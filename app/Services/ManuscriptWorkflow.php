<?php

namespace App\Services;

use App\Enums\ManuscriptStatus;
use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\Manuscript;
use App\Models\ManuscriptFile;
use App\Models\PublicSubmission;
use App\Models\User;
use App\Notifications\ManuscriptStatusChanged;
use App\Notifications\ManuscriptSubmitted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManuscriptWorkflow
{
    /**
     * @param  array{title: string, abstract: string, keywords: string, authors: list<array{name: string, email: string, affiliation: string}>}  $data
     */
    public function submit(User $submitter, array $data, UploadedFile $blinded, ?UploadedFile $full): Manuscript
    {
        $manuscript = DB::transaction(function () use ($submitter, $data, $blinded, $full) {
            $manuscript = $this->createManuscript($submitter, $data);

            $this->storeVersion($manuscript, $submitter, $blinded, $full);
            $this->recordHistory($manuscript, $submitter, null, ManuscriptStatus::Submitted, null);

            return $manuscript;
        });

        AuditLog::record('manuscript.submitted', $submitter->id, ['manuscript_id' => $manuscript->id]);

        $this->notifyEditorsOfSubmission($manuscript);

        return $manuscript;
    }

    /**
     * Turns a confirmed public submission into a regular manuscript of this author.
     * Must run inside the caller's transaction.
     */
    public function adoptPublicSubmission(PublicSubmission $submission, User $author): Manuscript
    {
        // Claiming the row first keeps a double click from creating two manuscripts.
        $claimed = PublicSubmission::whereKey($submission->id)->whereNull('confirmed_at')->update(['confirmed_at' => now()]);

        abort_if($claimed === 0, 409, 'This submission is already confirmed.');

        $manuscript = $this->createManuscript($author, [
            'title' => $submission->title,
            'abstract' => $submission->abstract,
            'keywords' => $submission->keywords,
            'authors' => [[
                'name' => $submission->name,
                'email' => $submission->email,
                'affiliation' => mb_substr($submission->institution_address, 0, 255),
            ]],
        ]);

        $path = 'manuscripts/'.$manuscript->id.'/'.basename($submission->path);
        Storage::disk('local')->move($submission->path, $path);

        $manuscript->files()->create([
            'uploaded_by' => $author->id,
            'version' => 1,
            'kind' => ManuscriptFile::KIND_BLINDED,
            'path' => $path,
            'original_name' => $submission->original_name,
            'extension' => $submission->extension,
            'size' => $submission->size,
            'sha256' => $submission->sha256,
        ]);

        $this->recordHistory($manuscript, $author, null, ManuscriptStatus::Submitted, null);

        $submission->update(['manuscript_id' => $manuscript->id, 'path' => $path]);

        return $manuscript;
    }

    public function notifyEditorsOfSubmission(Manuscript $manuscript): void
    {
        $editors = User::whereHas('roles', fn ($roles) => $roles->where('role', Role::Editor))->get();

        foreach ($editors as $editor) {
            if (! $manuscript->isAuthoredBy($editor)) {
                rescue(fn () => $editor->notify(new ManuscriptSubmitted($manuscript)));
            }
        }
    }

    public function transition(Manuscript $manuscript, ManuscriptStatus $to, User $actor, ?string $note): void
    {
        if (! in_array($to, $manuscript->status->editorTransitions(), true)) {
            throw ValidationException::withMessages(['status' => 'This status change is not allowed.']);
        }

        if ($to === ManuscriptStatus::Accepted && ! $manuscript->hasCompletedReviewThisRound()) {
            throw ValidationException::withMessages(['status' => 'A manuscript needs a completed review in this round before it can be accepted.']);
        }

        $this->move($manuscript, $to, $actor, $note);
    }

    public function submitRevision(Manuscript $manuscript, User $author, UploadedFile $blinded, ?UploadedFile $full, ?string $note): void
    {
        DB::transaction(function () use ($manuscript, $author, $blinded, $full) {
            $manuscript->update(['current_version' => $manuscript->current_version + 1]);

            $this->storeVersion($manuscript, $author, $blinded, $full);
        });

        $this->move($manuscript, ManuscriptStatus::Screening, $author, $note, notifyAuthor: false);

        AuditLog::record('manuscript.revision_submitted', $author->id, ['manuscript_id' => $manuscript->id, 'version' => $manuscript->current_version]);
    }

    /**
     * @param  array{title: string, abstract: string, keywords: string, authors: list<array{name: string, email: string, affiliation: string}>}  $data
     */
    private function createManuscript(User $submitter, array $data): Manuscript
    {
        $manuscript = Manuscript::create([
            'submitter_id' => $submitter->id,
            'title' => $data['title'],
            'abstract' => $data['abstract'],
            'keywords' => $data['keywords'],
            'status' => ManuscriptStatus::Submitted,
            'current_version' => 1,
            'submitted_at' => now(),
        ]);

        $manuscript->update(['code' => sprintf('ICSMI-%d-%04d', now()->year, $manuscript->id)]);

        foreach (array_values($data['authors']) as $position => $author) {
            $manuscript->authors()->create($author + ['position' => $position, 'is_corresponding' => $position === 0]);
        }

        return $manuscript;
    }

    private function move(Manuscript $manuscript, ManuscriptStatus $to, User $actor, ?string $note, bool $notifyAuthor = true): void
    {
        $from = $manuscript->status;

        DB::transaction(function () use ($manuscript, $from, $to, $actor, $note) {
            $manuscript->update([
                'status' => $to,
                'decided_at' => in_array($to, [ManuscriptStatus::Accepted, ManuscriptStatus::Rejected], true) ? now() : $manuscript->decided_at,
            ]);

            $this->recordHistory($manuscript, $actor, $from, $to, $note);
        });

        AuditLog::record('manuscript.status_changed', $actor->id, [
            'manuscript_id' => $manuscript->id,
            'from' => $from->value,
            'to' => $to->value,
        ]);

        if ($notifyAuthor) {
            rescue(fn () => $manuscript->submitter->notify(new ManuscriptStatusChanged($manuscript, $to, $note)));
        }
    }

    private function recordHistory(Manuscript $manuscript, User $actor, ?ManuscriptStatus $from, ManuscriptStatus $to, ?string $note): void
    {
        $manuscript->histories()->create([
            'actor_id' => $actor->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    private function storeVersion(Manuscript $manuscript, User $uploader, UploadedFile $blinded, ?UploadedFile $full): void
    {
        $this->storeFile($manuscript, $uploader, $blinded, ManuscriptFile::KIND_BLINDED);

        if ($full) {
            $this->storeFile($manuscript, $uploader, $full, ManuscriptFile::KIND_FULL);
        }
    }

    private function storeFile(Manuscript $manuscript, User $uploader, UploadedFile $file, string $kind): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $path = Storage::disk('local')->putFileAs(
            'manuscripts/'.$manuscript->id,
            $file,
            Str::random(40).'.'.$extension,
        );

        $manuscript->files()->create([
            'uploaded_by' => $uploader->id,
            'version' => $manuscript->current_version,
            'kind' => $kind,
            'path' => $path,
            'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
            'extension' => $extension,
            'size' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
        ]);
    }
}
