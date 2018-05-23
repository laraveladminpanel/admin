@php
    $serviceButtons = [];
    $serviceButtons['delete']['title'] = '';
    $serviceButtons['edit']['title'] = '';
    $serviceButtons['read']['title'] = '';

    if (config('admin.views.browse.display_text_on_service_buttons')) {
        $serviceButtons['delete']['title'] = __('admin.generic.delete');
        $serviceButtons['edit']['title'] = __('admin.generic.edit');
        $serviceButtons['read']['title'] = __('admin.generic.view');
    }

    $dataTypeOptions = json_decode($dataType->details);
    $customServiceButtons = false;

    if(isset($dataTypeOptions->browse->service_buttons)) {
        $customServiceButtons = $dataTypeOptions->browse->service_buttons;
    }

    $jsonDataTable = "{}";
    if (isset($dataTypeOptions->datatable)) {
        $jsonDataTable = json_encode($dataTypeOptions->datatable);
    }

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

@can('delete', $data)
    @unless($customServiceButtons && is_object($customServiceButtons) && property_exists($customServiceButtons, 'delete'))
        <a href="javascript:;" title="{{ __('admin.generic.delete') }}" class="btn btn-sm pull-right btn-danger delete" data-id="{{ $data->{$data->getKeyName()} }}" id="delete-{{ $data->{$data->getKeyName()} }}">
            <i class="admin-trash"></i> <span class="hidden-xs hidden-sm">{{ $serviceButtons['delete']['title'] }}</span>
        </a>
    @endunless
@endcan
@can('edit', $data)
    @unless($customServiceButtons && is_object($customServiceButtons) && property_exists($customServiceButtons, 'delete'))
        <a href="{{ route('admin.'.$dataType->slug.'.edit', $data->{$data->getKeyName() }) }}?{{ $requestQuery }}" title="{{ __('admin.generic.edit') }}" class="btn btn-sm pull-right btn-primary edit">
            <i class="admin-edit"></i> <span class="hidden-xs hidden-sm">{{ $serviceButtons['edit']['title'] }}</span>
        </a>
    @endunless
@endcan
@can('read', $data)
    @unless($customServiceButtons && is_object($customServiceButtons) && property_exists($customServiceButtons, 'read'))
        <a href="{{ route('admin.'.$dataType->slug.'.show', $data->{$data->getKeyName()}) }}?{{ $requestQuery }}" title="{{ __('admin.generic.view') }}" class="btn btn-sm btn-warning pull-right">
            <i class="admin-eye"></i>
            <span class="hidden-xs hidden-sm">{{ $serviceButtons['read']['title'] }}</span>
        </a>
    @endunless
@endcan
@if(isset($dataTypeOptions->browse->service_buttons))
    @foreach($dataTypeOptions->browse->service_buttons as $serviceButton)
        @can(isset($serviceButton->apply_permission) ? $serviceButton->apply_permission : 'browse', $data)
        <a data-id="{{ $data->{$data->getKeyName()} }}"
           data-title="{{ isset($serviceButton->title) ? $serviceButton->title : '' }}"
           title="{{ isset($serviceButton->title) ? $serviceButton->title : '' }}"
           class="btn btn-sm pull-right {{ isset($serviceButton->class) ? $serviceButton->class : '' }}"
           href="{{ isset($serviceButton->attribute) && isset($data->{$serviceButton->attribute}) ? $data->{$serviceButton->attribute} : '#' }}"
           target="{{ isset($serviceButton->target) ? $serviceButton->target : '_self' }}">
            @if(isset($serviceButton->icon))
            <i class="{{ $serviceButton->icon }}"></i>
            @endif
            @if(config('admin.views.browse.display_text_on_service_buttons'))
                <span class="hidden-xs hidden-sm">{{ isset($serviceButton->title) ? $serviceButton->title : '' }}</span>
            @endif
        </a>
        @endcan
    @endforeach
@endif
