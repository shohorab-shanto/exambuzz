@php
    $currentPath = array_merge($path ?? [], [$folder->id]);
    $inputName = "permission[Material][$permissionKey]";
    foreach ($currentPath as $segment) {
        $inputName .= '[' . $segment . ']';
    }
    $permissionPath = 'Material.' . $permissionKey . '.' . implode('.', $currentPath);
    $isChecked = isset($package) ? data_get($package->permission, $permissionPath) : false;
@endphp

<div class="form-check" style="margin-left: {{ count($currentPath) > 1 ? (count($currentPath) - 1) * 20 : 0 }}px;">
    <input type="checkbox"
           name="{{ $inputName }}"
           value="true"
           class="form-check-input"
           id="Material_{{ $permissionKey }}_Folder{{ $folder->id }}"
        {{ $isChecked ? 'checked' : '' }}>
    <label class="form-label form-check-label"
           for="Material_{{ $permissionKey }}_Folder{{ $folder->id }}">{{ $folder->name }}</label>
</div>

@if ($folder->children->isNotEmpty())
    @foreach ($folder->children as $child)
        @include('backend.package.partials.material-folder-tree', [
            'folder' => $child,
            'permissionKey' => $permissionKey,
            'path' => $currentPath,
            'package' => $package ?? null,
        ])
    @endforeach
@endif

