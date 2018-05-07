@extends('admin::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('admin.generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->display_name_singular)

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('admin.generic.'.(isset($dataTypeContent->id) ? 'edit' : 'add')).' '.$dataType->display_name_singular }}
    </h1>
    @include('admin::multilingual.language-selector')
@stop

@section('content')
    @php
        $dataTypeRows = $dataType->{(isset($dataTypeContent->id) ? 'editRows' : 'addRows' )};
        $formDesigner = $dataType->getFormDesigner();
        $formDesignerFields = collect([]);
    @endphp

    @if($formDesigner)
        <!-- form start -->
        <form role="form"
            class="form-edit-add"
            action="@if(isset($dataTypeContent->id)){{ route('admin.'.$dataType->slug.'.update', $dataTypeContent->id) }}@else{{ route('admin.'.$dataType->slug.'.store') }}@endif?{{ request()->getQueryString() }}"
            method="POST" enctype="multipart/form-data">
        <!-- PUT Method if we are editing -->
        @if(isset($dataTypeContent->id))
            {{ method_field("PUT") }}
        @endif

        <!-- CSRF TOKEN -->
        {{ csrf_field() }}

        @foreach($formDesigner as $key => $htmlRow)
            <div class="{{ $htmlRow->class }}">
                @foreach($htmlRow->panels as $key_panel => $panel)
                    <div class="{{ isset($panel->class) ? $panel->class : '' }}">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="icon wb-image"></i> {!! isset($panel->title) ? __($panel->title) : '' !!}</h3>
                            <div class="panel-actions">
                                <a class="panel-action admin-angle-down" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            @php
                                $formDesignerFields->push($panel->fields);
                            @endphp
                            @foreach($dataTypeRows->whereIn('field', $panel->fields) as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $options = json_decode($row->details);
                                    $display_options = isset($options->display) ? $options->display : NULL;
                                @endphp

                                @if ($options && isset($options->formfields_custom))
                                    @include('admin::formfields.custom.' . $options->formfields_custom)
                                @else
                                    <div class="form-group @if($row->type == 'hidden') hidden @endif @if(isset($display_options->width)){{ 'col-md-' . $display_options->width }}@endif" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                        {{ $row->slugify }}
                                        @if($row->display_name)
                                        <label for="name">{{ $row->display_name }}</label>
                                        @endif
                                        @include('admin::multilingual.input-hidden-bread-edit-add')
                                        {!! app('admin')->formField($row, $dataType, $dataTypeContent) !!}

                                        @foreach (app('admin')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                            {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        </div><!-- panel-body -->

                        @if(!$key && count($htmlRow->panels) == $key_panel+1)
                            @php
                                $view = 'admin::crud.partials.edit-add-footer';

                                if (view()->exists("admin::{$dataType->slug}.partials.edit-add-footer")) {
                                    $view = "admin::{$dataType->slug}.partials.edit-add-footer";
                                }
                            @endphp
                            @include($view)
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach

        @php
            $formDesignerFields = $formDesignerFields->collapse();

            $notformDesignerFields = $dataTypeRows->filter(function ($value, $key) use ($formDesignerFields) {
                return !$formDesignerFields->contains($value->field);
            });
        @endphp

        @if(count($notformDesignerFields))
            <!-- this fields  -->
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        @php
                            $dataTypeRows = $dataType->{(isset($dataTypeContent->id) ? 'editRows' : 'addRows' )};
                        @endphp

                        @foreach($notformDesignerFields as $row)
                            <!-- GET THE DISPLAY OPTIONS -->
                            @php
                                $options = json_decode($row->details);
                                $display_options = isset($options->display) ? $options->display : NULL;
                            @endphp

                            <!-- online code -->
                            @if ($options && isset($options->formfields_custom))
                                @include('admin::formfields.custom.' . $options->formfields_custom)
                            @else
                                <div class="form-group @if($row->type == 'hidden') hidden @endif @if(isset($display_options->width)){{ 'col-md-' . $display_options->width }}@endif" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label for="name">{{ $row->display_name }}</label>
                                    @include('admin::multilingual.input-hidden-bread-edit-add')
                                    {!! app('admin')->formField($row, $dataType, $dataTypeContent) !!}

                                    @foreach (app('admin')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div><!-- panel-body -->
                </div>
            </div>
        @endif
        </form>
    @else
        <div class="page-content edit-add container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-bordered">
                        <!-- form start -->
                        <form role="form"
                                class="form-edit-add"
                                action="@if(isset($dataTypeContent->id)){{ route('admin.'.$dataType->slug.'.update', $dataTypeContent->id) }}@else{{ route('admin.'.$dataType->slug.'.store') }}@endif"
                                method="POST" enctype="multipart/form-data">
                            <!-- PUT Method if we are editing -->
                            @if(isset($dataTypeContent->id))
                                {{ method_field("PUT") }}
                            @endif

                            <!-- CSRF TOKEN -->
                            {{ csrf_field() }}

                            <div class="panel-body">

                                @if (count($errors) > 0)
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Adding / Editing -->
                                @foreach($dataTypeRows as $row)
                                    <!-- GET THE DISPLAY OPTIONS -->
                                    @php
                                        $options = json_decode($row->details);
                                        $display_options = isset($options->display) ? $options->display : NULL;
                                    @endphp
                                    @if ($options && isset($options->formfields_custom))
                                        @include('admin::formfields.custom.' . $options->formfields_custom)
                                    @else
                                        <div class="form-group @if($row->type == 'hidden') hidden @endif @if(isset($display_options->width)){{ 'col-md-' . $display_options->width }}@endif" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                            {{ $row->slugify }}
                                            <label for="name">{{ $row->display_name }}</label>
                                            @include('admin::multilingual.input-hidden-bread-edit-add')
                                            @if($row->type == 'relationship')
                                                @include('admin::formfields.relationship')
                                            @else
                                                {!! app('admin')->formField($row, $dataType, $dataTypeContent) !!}
                                            @endif

                                            @foreach (app('admin')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                                {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach

                            </div><!-- panel-body -->

                            <div class="panel-footer">
                                <button type="submit" class="btn btn-primary save">{{ __('admin.generic.save') }}</button>
                            </div>
                        </form>

                        <iframe id="form_target" name="form_target" style="display:none"></iframe>
                        <form id="my_form" action="{{ route('admin.upload') }}" target="form_target" method="post"
                                enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                            <input name="image" id="upload_file" type="file"
                                     onchange="$('#my_form').submit();this.value='';">
                            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                            {{ csrf_field() }}
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="admin-warning"></i> {{ __('admin.generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('admin.generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.delete') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('admin.generic.delete_confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
    <script>
        var params = {}
        var $image

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.type != 'date' || elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', function (e) {
                $image = $(this).siblings('img');

                params = {
                    slug:   '{{ $dataType->slug }}',
                    image:  $image.data('image'),
                    id:     $image.data('id'),
                    field:  $image.parent().data('field-name'),
                    _token: '{{ csrf_token() }}'
                }

                $('.confirm_delete_name').text($image.data('image'));
                $('#confirm_delete_modal').modal('show');
            });

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('admin.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $image.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing image.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
