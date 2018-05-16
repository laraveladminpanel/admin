@extends('admin::master')

@section('page_title', __('admin.generic.viewing').' '.$dataType->display_name_plural)

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->display_name_plural }}
        </h1>
        @can('add',app($dataType->model_name))
            @include('admin::crud.browse.buttons.add-new')
        @endcan

        @can('delete',app($dataType->model_name))
            @include('admin::partials.bulk-delete')
        @endcan

        @include('admin::multilingual.language-selector')
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('admin::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    @include('admin::list.datatable')
                </div>
            </div>
        </div>
    </div>
@stop
