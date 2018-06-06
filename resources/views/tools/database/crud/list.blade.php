<div class="page-content container-fluid">
    @include('admin::alerts')
    <div class="row">
        <div class="col-md-12">

            <table class="table table-striped database-tables">
                <thead>
                    <tr>
                        <th>{{ __('admin.database.table_name') }}</th>
                        <th style="text-align:right">{{ __('admin.database.crud_actions') }}</th>
                    </tr>
                </thead>

                @foreach($additionalTables as $table)
                    <tr>
                        <td>
                            <p class="name">
                                <a href="{{ route('admin.database.show', $table->name) }}"
                                   data-name="{{ $table->name }}" class="desctable">
                                   {{ $table->slug }}
                                </a>
                                @if($table->dataTypeId)
                                    <i class="admin-check"></i>
                                @endif
                            </p>
                        </td>

                        <td class="actions">
                            <a class="btn btn-danger btn-sm pull-right delete"
                               data-id="{{ $table->id }}" data-name="{{ $table->name }}" style="display:inline; cursor:pointer;">
                               <i class="admin-trash"></i> {{ __('admin.generic.delete') }}
                            </a>
                            <a href="{{ route('admin.database.crud.edit', $table->slug) }}"
                               class="btn btn-sm btn-primary pull-right" style="display:inline; margin-right:10px;">
                               <i class="admin-edit"></i> {{ __('admin.generic.edit') }}
                            </a>
                            <a href="{{ route('admin.' . $table->slug . '.index') }}"
                               data-name="{{ $table->name }}"
                               class="btn btn-sm btn-warning pull-right desctable" style="display:inline; margin-right:10px;">
                               <i class="admin-eye"></i> {{ __('admin.generic.view') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>

<div class="modal modal-danger fade" tabindex="-1" id="delete_builder_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.generic.close') }}"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="admin-trash"></i>  {!! __('admin.database.delete_table_crud_quest', ['table' => '<span id="delete_builder_name"></span>']) !!}</h4>
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.database.crud.delete', ['id' => null]) }}" id="delete_builder_form" method="POST">
                    {{ method_field('DELETE') }}
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="submit" class="btn btn-danger" value="{{ __('admin.database.delete_table_crud_conf') }}">
                </form>
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@section('javascript')
    @parent
    <script>
        $(function () {
            $('.actions').on('click', '.delete', function (e) {
                id = $(this).data('id');
                name = $(this).data('name');

                $('#delete_builder_name').text(name);
                $('#delete_builder_form')[0].action += '/' + id;
                $('#delete_builder_modal').modal('show');
            });
        });
    </script>
@stop
