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
            $relationshipOptions = [];

            if (isset($options->details)) {
                $details = json_decode($options->details);
                $relationshipOptions = isset($details->options) ? $details->options : [];
            }
        @endphp

        @foreach($relationshipOptions as $key => $value)
            <option value="{{ $key === 'null' ? null : $key }}">{{ $value }}</option>
        @endforeach

        @foreach($query as $relationshipData)
            <option value="{{ $relationshipData->{$options->key} }}" @if($dataTypeContent->{$options->column} == $relationshipData->{$options->key}){{ 'selected="selected"' }}@endif>{{ $relationshipData->{$options->label} }}</option>
        @endforeach
    </select>
@endif
