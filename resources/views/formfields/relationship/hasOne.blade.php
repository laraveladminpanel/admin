@php 
    $relationshipData = (isset($data)) ? $data : $dataTypeContent;

    $model = app($options->model);
    $query = $model::where($options->column, '=', $relationshipData->id)->first();
@endphp

@if(isset($query))
    <p>{{ $query->{$options->label} }}</p>
@else
    <p>None results</p>
@endif
