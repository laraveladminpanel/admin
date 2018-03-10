@if(isset($view) && ($view == 'browse' || $view == 'read'))
    @php 
        $relationshipData = (isset($data)) ? $data : $dataTypeContent;
        $model = app($options->model);
        $query = $model::find($relationshipData->{$options->column});
    @endphp

    @if(isset($query))
        <p>{{ $query->{$options->label} }}</p>
    @else
        <p>No results</p>
    @endif
@else
    <select class="form-control select2" name="{{ $options->column }}">
        @php
            $model = app($options->model);
            $query = $model::all();
        @endphp
        @foreach($query as $relationshipData)
            <option value="{{ $relationshipData->{$options->key} }}" @if($dataTypeContent->{$options->column} == $relationshipData->{$options->key}){{ 'selected="selected"' }}@endif>{{ $relationshipData->{$options->label} }}</option>
        @endforeach
    </select>
@endif
