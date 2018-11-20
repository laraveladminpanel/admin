@if(isset($options->model) && isset($options->type))
    @if(class_exists($options->model))
        @php $relationshipField = $row->field; @endphp
        @include('admin::formfields.relationship.' . $options->type)
    @else
        cannot make relationship because {{ $options->model }} does not exist.
    @endif
@else
    @php $row =(object) json_decode($row->details,true); @endphp
    @if(isset($row->model) && isset($row->type))
        @if(class_exists($row->model))
            @php $options = $row @endphp
            @include('admin::formfields.relationship.' . $row->type)
        @else
            cannot make relationship because {{ $row->model }} does not exist.
        @endif
    @endif
@endif
