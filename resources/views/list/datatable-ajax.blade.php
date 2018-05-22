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
                    <th></th>
                    @foreach($dataType->browseRows as $row)
                    <th>
                        {{ $row->display_name }}
                    </th>
                    @endforeach
                    <th class="actions">{{ __('admin.generic.actions') }}</th>
                </tr>
            </thead>
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
    <link rel="stylesheet" href="{{ admin_asset('lib/css/responsive.dataTables.min.css') }}">

    @if(isset($dataTypeOptions->datatable->buttons) && is_array($dataTypeOptions->datatable->buttons))
        <link rel="stylesheet" href="{{ admin_asset('plugins/dataTables/extensions/buttons/buttons.min.css') }}">
    @endif

    @if(isset($dataTypeOptions->datatable->rowReorder))
        <link href="{{ admin_asset('plugins/dataTables/extensions/reorder/rowReorder.min.css') }}" rel="stylesheet">
    @endif
@stop

@section('javascript')
    <!-- DataTables -->
    <script src="{{ admin_asset('lib/js/dataTables.responsive.min.js') }}"></script>

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

                table.DataTable({
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin.get-ajax-list') }}",
                        type: "POST",
                        data: {
                            "slug": "{{ $slug }}"
                        },
                    },
                    columns: [
                        {data: "delete_checkbox", orderable: false, searchable: false},
                    @foreach($dataType->browseRows as $row)
                        {data: "{{ $row->field }}"},
                    @endforeach
                        {data: "actions", orderable: false, searchable: false},
                    ],
                });

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
