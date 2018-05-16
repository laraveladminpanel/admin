@php
    $requestQuery = request()->getQueryString();

    if (isset($parentDataTypeContent)) {
        if (isset($parentDataTypeContent->id) && isset($parentDataType->slug)) {
            $requestQuery = ($requestQuery ? $requestQuery . '&' : '')
                . 'crud_slug=' . $parentDataType->slug
                . '&crud_action=' . request()->route()->getActionMethod()
                . '&crud_id=' . $parentDataTypeContent->id;
        }
    }
@endphp

<a href="{{ route('admin.'.$dataType->slug.'.create') }}?{{ $requestQuery }}" class="btn btn-success btn-add-new">
    <i class="admin-plus"></i> <span>{{ __('admin.generic.add_new') }}</span>
</a>
