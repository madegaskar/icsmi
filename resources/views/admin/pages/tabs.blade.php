{{-- Admins see the full admin menu. Editors, who also manage pages, see the editor menu. --}}
@if (auth()->user()->hasRole(\App\Enums\Role::Admin))
    @include('admin.partials-tabs')
@else
    @include('editor.partials.tabs')
@endif
