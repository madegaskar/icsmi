<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeBlockRequest;
use App\Models\AuditLog;
use App\Models\HomeBlock;
use App\Models\Media;
use App\Services\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeBlockController extends Controller
{
    public function __construct(private readonly MediaUploader $uploader) {}

    public function index(): View
    {
        return view('admin.home-blocks.index', [
            'blocks' => HomeBlock::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new HomeBlock(['type' => HomeBlock::TYPE_TEXT, 'sort_order' => 10, 'is_visible' => true]));
    }

    public function store(HomeBlockRequest $request): RedirectResponse
    {
        $block = HomeBlock::create($this->dataFrom($request));

        AuditLog::record('home_block.created', $request->user()->id, ['home_block_id' => $block->id, 'type' => $block->type]);

        return redirect()->route('admin.home-blocks.index')->with('status', 'Block saved.');
    }

    public function edit(HomeBlock $homeBlock): View
    {
        return $this->form($homeBlock);
    }

    public function update(HomeBlockRequest $request, HomeBlock $homeBlock): RedirectResponse
    {
        $homeBlock->update($this->dataFrom($request));

        AuditLog::record('home_block.updated', $request->user()->id, ['home_block_id' => $homeBlock->id, 'type' => $homeBlock->type]);

        return redirect()->route('admin.home-blocks.index')->with('status', 'Block saved.');
    }

    public function destroy(HomeBlock $homeBlock): RedirectResponse
    {
        $id = $homeBlock->id;
        $type = $homeBlock->type;
        $homeBlock->delete();

        AuditLog::record('home_block.deleted', request()->user()->id, ['home_block_id' => $id, 'type' => $type]);

        return redirect()->route('admin.home-blocks.index')->with('status', 'Block deleted.');
    }

    /**
     * An image uploaded on the form goes into the Media Library and is attached as the desktop image.
     *
     * @return array<string, mixed>
     */
    private function dataFrom(HomeBlockRequest $request): array
    {
        $data = $request->validated();

        if ($request->hasFile('desktop_image')) {
            $media = $this->uploader->store($request->file('desktop_image'), $request->input('image_alt'), null, $request->user());
            $data['desktop_media_id'] = $media->id;
        }

        if ($request->hasFile('cover_image')) {
            $cover = $this->uploader->store($request->file('cover_image'), $request->input('cover_alt'), null, $request->user());
            $data['cover_media_id'] = $cover->id;
        }

        return $data;
    }

    private function form(HomeBlock $block): View
    {
        return view('admin.home-blocks.form', [
            'block' => $block,
            'images' => Media::where('kind', Media::KIND_IMAGE)->orderBy('original_name')->get(['id', 'original_name']),
        ]);
    }
}
