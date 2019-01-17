@if(isset($view) && ($view == 'browse' || $view == 'read'))
    @php
        $relationshipData = (isset($data)) ? $data : $dataTypeContent;
        $model = app($options->model);
        $selected_values = $model::where($options->column, '=', $relationshipData->id)->pluck($options->label)->all();
    @endphp

    @if($view == 'browse')
        @php
            $string_values = implode(", ", $selected_values);
            if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; } 
        @endphp
        @if(!$selected_values)
            <p>No results</p>
        @else
            <p>{{ $string_values }}</p>
        @endif
    @else
        @php
            $model = app($options->model);
            $query = $model::where($options->column, '=', $dataTypeContent->id)->get();
            $details = new stdClass;
            if (property_exists($options, 'details')) {
                $details = json_decode($options->details);
            }
        @endphp

        @if(isset($details->list) && $details->list === "datatable")
            @php
                $relationDataType = Admin::model('DataType')->where('name', '=', $model->getTable())->first()
            @endphp

            @section('datatable_header')
                @can('add',app($relationDataType->model_name))
                    @include('admin::crud.browse.buttons.add-new', [
                        'dataType' => $relationDataType,
                        'parentDataTypeContent' => $dataTypeContent,
                        'parentDataType' => $dataType
                    ])
                @endcan

                @can('delete',app($relationDataType->model_name))
                    @include('admin::partials.bulk-delete')
                @endcan
            @stop

            @include('admin::list.datatable', [
                'isServerSide' => $dataType->isServerSide(),
                'dataTypeContent' => $query,
                'dataType' => $relationDataType,
                'parentDataTypeContent' => $dataTypeContent,
                'parentDataType' => $dataType
            ])
        @elseif(isset($query))
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
    @endif
@else
    @php
        $model = app($options->model);
        $query = $model::where($options->column, '=', $dataTypeContent->id)->get();
        $details = new stdClass;
        if (property_exists($options, 'details')) {
            $details = json_decode($options->details);
        }
    @endphp

    @if(isset($details->list) && $details->list === "datatable")
        @php
            $relationDataType = Admin::model('DataType')->where('name', '=', $model->getTable())->first()

        @endphp

            @can('add',app($relationDataType->model_name))
                @include('admin::crud.browse.buttons.add-new', [
                    'dataType' => $relationDataType,
                    'parentDataTypeContent' => $dataTypeContent,
                    'parentDataType' => $dataType
                ])
            @endcan

            @can('delete',app($relationDataType->model_name))
                @include('admin::partials.bulk-delete')
            @endcan


        @include('admin::list.datatable', [
            'isServerSide' => $dataType->isServerSide(),
            'dataTypeContent' => $query,
            'dataType' => $relationDataType,
            'parentDataTypeContent' => $dataTypeContent,
            'parentDataType' => $dataType
        ])
    @elseif(isset($query))
        <ul>
            @foreach($query as $query_res)
                <li>{{ $query_res->{$options->label} }}</li>
            @endforeach
        </ul>
    @else
        <p>No results</p>
    @endif
@endif
