@if(isset($options->model) && isset($options->type))
    @if(class_exists($options->model))
        @php $relationshipField = $row->field; @endphp
        @include('admin::formfields.relationship.' . $options->type)
    @else
        cannot make relationship because {{ $options->model }} does not exist.
    @endif
@endif
