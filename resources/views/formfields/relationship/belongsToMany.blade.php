@if(isset($view) && ($view == 'browse' || $view == 'read'))
    @php
        $relationshipData = (isset($data)) ? $data : $dataTypeContent;
        $selected_values = isset($relationshipData) ? $relationshipData->belongsToMany($options->model, $options->pivot_table)->pluck($options->label)->all() : array();
    @endphp

    @if($view == 'browse')
        @php
            $string_values = implode(", ", $selected_values); 
            if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; } 
        @endphp
        @if(empty($selected_values))
            <p>No results</p>
        @else
            <p>{{ $string_values }}</p>
        @endif
    @else
        @if(empty($selected_values))
            <p>No results</p>
        @else
            <ul>
                @foreach($selected_values as $selected_value)
                    <li>{{ $selected_value }}</li>
                @endforeach
            </ul>
        @endif
    @endif
@else
    <select class="form-control select2" name="{{ $relationshipField }}[]" multiple>
        @php 
            $selected_values = isset($dataTypeContent) ? $dataTypeContent->belongsToMany($options->model, $options->pivot_table)->pluck($options->key)->all() : array();
            $relationshipOptions = app($options->model)->all();
        @endphp

        @foreach($relationshipOptions as $relationshipOption)
            <option value="{{ $relationshipOption->{$options->key} }}" @if(in_array($relationshipOption->{$options->key}, $selected_values)){{ 'selected="selected"' }}@endif>{{ $relationshipOption->{$options->label} }}</option>
        @endforeach
    </select>
@endif
