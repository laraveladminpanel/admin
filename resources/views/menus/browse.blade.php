@extends('admin::master')

@section('page_title', __('admin.generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <h1 class="page-title">
        <i class="admin-list-add"></i> {{ $dataType->display_name_plural }}
        @can('add',app($dataType->model_name))
            <a href="{{ route('admin.'.$dataType->slug.'.create') }}" class="btn btn-success">
                <i class="admin-plus"></i> {{ __('admin.generic.add_new') }}
            </a>
        @endcan
    </h1>
@stop

@section('content')
    @include('admin::menus.partial.notice')

    <div class="page-content container-fluid">
        @include('admin::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <table id="dataTable" class="table table-hover">
                            <thead>
                            <tr>
                                @foreach($dataType->browseRows as $rows)
                                <th>{{ $rows->display_name }}</th>
                                @endforeach
                                <th class="actions">{{ __('admin.generic.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($dataTypeContent as $data)
                                <tr>
                                    @foreach($dataType->browseRows as $row)
                                    <td>
                                        @if($row->type == 'image')
                                            <img src="@if( strpos($data->{$row->field}, 'http://') === false && strpos($data->{$row->field}, 'https://') === false){{ Admin::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                                        @else
                                            {{ $data->{$row->field} }}
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="no-sort no-click crud-actions">
                                        @can('delete', $data)
                                            <a class="btn btn-sm btn-danger pull-right delete" data-id="{{ $data->{$data->getKeyName()} }}">
                                                <i class="admin-trash"></i> {{ __('admin.generic.delete') }}
                                            </a>
                                        @endcan
                                        @can('edit', $data)
                                            <a href="{{ route('admin.'.$dataType->slug.'.edit', $data->{$data->getKeyName()}) }}" class="btn btn-sm btn-primary pull-right edit">
                                                <i class="admin-edit"></i> {{ __('admin.generic.edit') }}
                                            </a>
                                        @endcan
                                        @can('edit', $data)
                                            <a href="{{ route('admin.'.$dataType->slug.'.builder', $data->{$data->getKeyName()}) }}" class="btn btn-sm btn-success pull-right">
                                                <i class="admin-list"></i> {{ __('admin.generic.builder') }}
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.generic.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        <i class="admin-trash"></i> {{ __('admin.generic.delete_question') }} {{ $dataType->display_name_singular }}?
                    </h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('admin.'.$dataType->slug.'.index') }}" id="delete_form" method="POST">
                        {{ method_field("DELETE") }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('admin.generic.delete_this_confirm') }} {{ $dataType->display_name_singular }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <!-- DataTables -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable({
                "order": [],
                "language": {!! json_encode(__('admin.datatable'), true) !!}
                @if(config('dashboard.data_tables.responsive')), responsive: true @endif
            });
        });

        $('td').on('click', '.delete', function (e) {
            id = $(e.target).data('id');

            $('#delete_form')[0].action += '/' + id;

            $('#delete_modal').modal('show');
        });
    </script>
@stop
