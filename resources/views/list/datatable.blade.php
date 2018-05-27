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

<div class="panel-body">
    @if ($isServerSide)
        <form method="get">
            <div id="search-input">
                <select id="search_key" name="key">
                    @foreach($searchable as $key)
                        <option value="{{ $key }}" @if($search->key == $key){{ 'selected' }}@endif>{{ ucwords(str_replace('_', ' ', $key)) }}</option>
                    @endforeach
                </select>
                <select id="filter" name="filter">
                    <option value="contains" @if($search->filter == "contains"){{ 'selected' }}@endif>contains</option>
                    <option value="equals" @if($search->filter == "equals"){{ 'selected' }}@endif>=</option>
                </select>
                <div class="input-group col-md-12">
                    <input type="text" class="form-control" placeholder="Search" name="s" value="{{ $search->value }}">
                    <span class="input-group-btn">
                        <button class="btn btn-info btn-lg" type="submit">
                            <i class="admin-search"></i>
                        </button>
                    </span>
                </div>
            </div>
        </form>
    @endif
    <div class="table-responsive">

        @yield('datatable_header')

        <table id="dataTable" class="table table-hover" data-json-datatable="{{ $jsonDataTable }}">
            <thead>
                <tr>
                    @if(!isset($dataTypeOptions->datatable->rowReorder))
                    <th></th>
                    @endif
                    @foreach($dataType->browseRows as $row)
                    <th>
                        @if ($isServerSide)
                            <a href="{{ $row->sortByUrl() }}">
                        @endif
                        {{ $row->display_name }}
                        @if ($isServerSide)
                            @if ($row->isCurrentSortField())
                                @if (!isset($_GET['sort_order']) || $_GET['sort_order'] == 'asc')
                                    <i class="admin-angle-up pull-right"></i>
                                @else
                                    <i class="admin-angle-down pull-right"></i>
                                @endif
                            @endif
                            </a>
                        @endif
                    </th>
                    @endforeach
                    <th class="actions">{{ __('admin.generic.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataTypeContent as $data)
                <tr @isset($data->id) data-id="{{ $data->id }}" @endisset>
                    @if(!isset($dataTypeOptions->datatable->rowReorder))
                    <td>
                        <input type="checkbox" name="row_id" id="checkbox_{{ $data->id }}" value="{{ $data->id }}">
                    </td>
                    @endif
                    @foreach($dataType->browseRows as $row)
                        <td>
                            <?php $options = json_decode($row->details); ?>
                            @if($row->type == 'image')
                                <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Admin::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                            @elseif($row->type == 'relationship')
                                @include('admin::formfields.relationship', ['view' => 'browse'])
                            @elseif($row->type == 'select_multiple')
                                @if(property_exists($options, 'relationship'))

                                    @foreach($data->{$row->field} as $item)
                                        @if($item->{$row->field . '_page_slug'})
                                        <a href="{{ $item->{$row->field . '_page_slug'} }}">{{ $item->{$row->field} }}</a>@if(!$loop->last), @endif
                                        @else
                                        {{ $item->{$row->field} }}
                                        @endif
                                    @endforeach

                                    {{-- $data->{$row->field}->implode($options->relationship->label, ', ') --}}
                                @elseif(property_exists($options, 'options') && !empty($data->{$row->field}))
                                    @foreach(json_decode($data->{$row->field}) as $item)
                                     {{ $options->options->{$item} . (!$loop->last ? ', ' : '') }}
                                    @endforeach
                                @endif

                            @elseif($row->type == 'select_dropdown' && property_exists($options, 'options'))

                                @if($data->{$row->field . '_page_slug'})
                                    <a href="{{ $data->{$row->field . '_page_slug'} }}">{!! $options->options->{$data->{$row->field}} !!}</a>
                                @else
                                    {!! $options->options->{$data->{$row->field}} !!}
                                @endif


                            @elseif($row->type == 'select_dropdown' && $data->{$row->field . '_page_slug'})
                                <a href="{{ $data->{$row->field . '_page_slug'} }}">{{ $data->{$row->field} }}</a>
                            @elseif($row->type == 'date')
                            {{ $options && property_exists($options, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($options->format) : $data->{$row->field} }}
                            @elseif($row->type == 'checkbox')
                                @if($options && property_exists($options, 'on') && property_exists($options, 'off'))
                                    @if($data->{$row->field})
                                    <span class="label label-info">{{ $options->on }}</span>
                                    @else
                                    <span class="label label-primary">{{ $options->off }}</span>
                                    @endif
                                @else
                                {{ $data->{$row->field} }}
                                @endif
                            @elseif($row->type == 'color')
                                <span class="badge badge-lg" style="background-color: {{ $data->{$row->field} }}">{{ $data->{$row->field} }}</span>
                            @elseif($row->type == 'text')
                                @include('admin::multilingual.input-hidden-bread-browse')
                                <div class="readmore">{{ mb_strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                            @elseif($row->type == 'text_area')
                                @include('admin::multilingual.input-hidden-bread-browse')
                                <div class="readmore">{{ mb_strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                            @elseif($row->type == 'file' && !empty($data->{$row->field}) )
                                @include('admin::multilingual.input-hidden-bread-browse')
                                @if(json_decode($data->{$row->field}))
                                    @foreach(json_decode($data->{$row->field}) as $file)
                                        <a href="{{ Storage::disk(config('admin.storage.disk'))->url($file->download_link) ?: '' }}" target="_blank">
                                            {{ $file->original_name ?: '' }}
                                        </a>
                                        <br/>
                                    @endforeach
                                @else
                                    <a href="{{ Storage::disk(config('admin.storage.disk'))->url($data->{$row->field}) }}" target="_blank">
                                        Download
                                    </a>
                                @endif
                            @elseif($row->type == 'rich_text_box')
                                @include('admin::multilingual.input-hidden-bread-browse')
                                <div class="readmore">{{ mb_strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>
                            @elseif($row->type == 'coordinates')
                                @include('admin::partials.coordinates-static-image')
                            @else
                                @include('admin::multilingual.input-hidden-bread-browse')
                                <span>{{ $data->{$row->field} }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="no-sort no-click" id="crud-actions">
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
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($isServerSide)
        <div class="pull-left">
            <div role="status" class="show-res" aria-live="polite">{{ trans_choice(
                'admin.generic.showing_entries', $dataTypeContent->total(), [
                    'from' => $dataTypeContent->firstItem(),
                    'to' => $dataTypeContent->lastItem(),
                    'all' => $dataTypeContent->total()
                ]) }}</div>
        </div>
        <div class="pull-right">
            {{ $dataTypeContent->appends([
                's' => $search,
                'order_by' => $orderBy,
                'sort_order' => $sortOrder
            ])->links() }}
        </div>
    @endif
</div>

@section('popup')
{{-- Single delete modal --}}
<div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.generic.close') }}"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="admin-trash"></i> {{ __('admin.generic.delete_question') }} {{ strtolower($dataType->display_name_singular) }}?</h4>
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.'.$dataType->slug.'.index') }}" id="delete_form" method="POST">
                    {{ method_field("DELETE") }}
                    {{ csrf_field() }}
                    <input type="submit" class="btn btn-danger pull-right delete-confirm"
                             value="{{ __('admin.generic.delete_confirm') }} {{ strtolower($dataType->display_name_singular) }}">
                </form>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@stop

@section('css')
    @if($dataType->pagination !== 'php' && config('dashboard.data_tables.responsive'))
        <link rel="stylesheet" href="{{ admin_asset('lib/css/responsive.dataTables.min.css') }}">
    @endif

    @if(isset($dataTypeOptions->datatable->buttons) && is_array($dataTypeOptions->datatable->buttons))
        <link rel="stylesheet" href="{{ admin_asset('plugins/dataTables/extensions/buttons/buttons.min.css') }}">
    @endif

    @if(isset($dataTypeOptions->datatable->rowReorder))
        <link href="{{ admin_asset('plugins/dataTables/extensions/reorder/rowReorder.min.css') }}" rel="stylesheet">
    @endif
@stop

@section('javascript')
    <!-- DataTables -->
    @if($dataType->pagination !== 'php' && config('dashboard.data_tables.responsive'))
        <script src="{{ admin_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif

    @if(isset($dataTypeOptions->datatable->buttons) && is_array($dataTypeOptions->datatable->buttons))
        <script src="{{ admin_asset('plugins/dataTables/extensions/buttons/dataTables.buttons.min.js') }}"></script>
        <script src="{{ admin_asset('plugins/dataTables/extensions/buttons/jszip.min.js') }}"></script>
        <script src="{{ admin_asset('plugins/dataTables/extensions/buttons/buttons.html5.min.js') }}"></script>
        <script src="{{ admin_asset('plugins/dataTables/extensions/buttons/buttons.print.min.js') }}"></script>
    @endif

    @if(isset($dataTypeOptions->datatable->rowReorder))
        <script src="{{ admin_asset('plugins/dataTables/extensions/reorder/rowReorder.min.js') }}"></script>
    @endif

    <script>
        $(document).ready(function () {
            @if ($dataType->pagination !== 'php')
                var table = $('#dataTable');

                var baseDatatableConfig = {!! json_encode(
                    array_merge([
                        "order" => [],
                        "language" => __('admin.datatable'),
                    ])
                , true) !!};

                var crudDatatableConfig = table.data('json-datatable');
                var datatableConfig = $.extend(baseDatatableConfig, crudDatatableConfig);
                var dataTable = table.DataTable(datatableConfig);

                @if (isset($dataTypeOptions->datatable->rowReorder))
                    @php
                        $datatableOrderColumn = '';
                        if (isset($dataTypeOptions->datatable->rowReorder->order_column)) {
                            $datatableOrderColumn = $dataTypeOptions->datatable->rowReorder->order_column;
                        }
                    @endphp

                    dataTable.on('row-reorder', function (e, diff, edit) {
                        var data = $("#dataTable tr")
                           // get all sibling tr
                           .siblings()
                           // iterate over elements using map and generate array elelements
                           .map(function(){
                           // get data attribute value
                           return $(this).data();
                           // get the result object as an array using get method
                        }).get();

                        $.post('{{ route('admin.api.order') }}', {
                            data: JSON.stringify(data),
                            table_name: "{{ $dataType->name }}",
                            order_by: "{{ $datatableOrderColumn }}",
                            dataType: 'json',
                            _token: '{{ csrf_token() }}'
                        }).done(function() {
                            toastr.success("Порядок успешно обновлен ({{ $dataType->display_name_singular }})");
                        }).fail(function(data, type, error) {
                            toastr.error(type, error);
                        });
                    });
                @endif

            @else
                $('#search-input select').select2({
                    minimumResultsForSearch: Infinity
                });
            @endif

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
            @endif
        });


        var deleteFormAction;
        $('td').on('click', '.delete', function (e) {
            var form = $('#delete_form')[0];

            if (!deleteFormAction) { // Save form action initial value
                deleteFormAction = form.action;
            }

            form.action = deleteFormAction.match(/\/[0-9]+$/)
                ? deleteFormAction.replace(/([0-9]+$)/, $(this).data('id'))
                : deleteFormAction + '/' + $(this).data('id');

            form.action = form.action + "?{{ $requestQuery }}".replace(/&amp;/g, '&');

            $('#delete_modal').modal('show');
        });
    </script>
@stop
