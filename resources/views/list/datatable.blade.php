@php
    $buttons = [];
    $buttons['text_on_delete'] = '';
    $buttons['text_on_edit'] = '';
    $buttons['text_on_view'] = '';

    if (config('admin.views.browse.display_text_on_service_buttons')) {
        $buttons['text_on_delete'] = __('admin.generic.delete');
        $buttons['text_on_edit'] = __('admin.generic.edit');
        $buttons['text_on_view'] = __('admin.generic.view');
    }

    $dataTypeOptions = json_decode($dataType->details);
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

        <table id="dataTable" class="table table-hover">
            <thead>
                <tr>
                    <th></th>
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
                <tr>
                    <td>
                        <input type="checkbox" name="row_id" id="checkbox_{{ $data->id }}" value="{{ $data->id }}">
                    </td>
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
                                @elseif(property_exists($options, 'options'))
                                    @foreach($data->{$row->field} as $item)
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
                                <div class="readmore">{{ strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                            @elseif($row->type == 'text_area')
                                @include('admin::multilingual.input-hidden-bread-browse')
                                <div class="readmore">{{ strlen( $data->{$row->field} ) > 200 ? substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
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
                                <div class="readmore">{{ strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>
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
                            <a href="javascript:;" title="{{ __('admin.generic.delete') }}" class="btn btn-sm btn-danger pull-right delete" data-id="{{ $data->{$data->getKeyName()} }}" id="delete-{{ $data->{$data->getKeyName()} }}">
                                <i class="admin-trash"></i> <span class="hidden-xs hidden-sm">{{ $buttons['text_on_delete'] }}</span>
                            </a>
                        @endcan
                        @can('edit', $data)
                            <a href="{{ route('admin.'.$dataType->slug.'.edit', $data->{$data->getKeyName()}) }}" title="{{ __('admin.generic.edit') }}" class="btn btn-sm btn-primary pull-right edit">
                                <i class="admin-edit"></i> <span class="hidden-xs hidden-sm">{{ $buttons['text_on_edit'] }}</span>
                            </a>
                        @endcan
                        @can('read', $data)
                            <a href="{{ route('admin.'.$dataType->slug.'.show', $data->{$data->getKeyName()}) }}" title="{{ __('admin.generic.view') }}" class="btn btn-sm btn-warning pull-right">
                                <i class="admin-eye"></i>
                                @if(config('admin.views.browse.display_text_on_service_buttons'))
                                    <span class="hidden-xs hidden-sm">{{ isset($button->title) ? $button->title : '' }}</span>
                                @endif
                                <span class="hidden-xs hidden-sm">{{ $buttons['text_on_view'] }}</span>
                            </a>
                        @endcan
                        @can('read', $data)
                            @if(isset($dataTypeOptions->browse->buttons))
                                @foreach($dataTypeOptions->browse->buttons as $button)
                                    <a data-id="{{ $data->id }}"
                                       data-title="{{ isset($button->title) ? $button->title : '' }}"
                                       title="{{ isset($button->title) ? $button->title : '' }}"
                                       class="btn btn-sm pull-right {{ isset($button->class) ? $button->class : '' }}"
                                       href="{{ isset($button->attribute) && isset($data->{$button->attribute}) ? $data->{$button->attribute} : '#' }}"
                                       target="{{ isset($button->target) ? $button->target : '_self' }}">
                                        @if(isset($button->icon))
                                        <i class="{{ $button->icon }}"></i>
                                        @endif
                                        @if(config('admin.views.browse.display_text_on_service_buttons'))
                                            <span class="hidden-xs hidden-sm">{{ isset($button->title) ? $button->title : '' }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            @endif
                        @endcan
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

@section('css')
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <link rel="stylesheet" href="{{ admin_asset('lib/css/responsive.dataTables.min.css') }}">
    @endif
@stop

@section('javascript')
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ admin_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            @if (!$dataType->server_side)
                var table = $('#dataTable').DataTable({!! json_encode(
                    array_merge([
                        "order" => [],
                        "language" => __('admin.datatable'),
                    ],
                    config('admin.dashboard.data_tables', []))
                , true) !!});
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
            console.log(form.action);

            $('#delete_modal').modal('show');
        });
    </script>
@stop