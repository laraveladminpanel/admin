@extends('admin::master')

@section('page_title', __('admin.generic.media'))

@section('content')
    <div class="page-content container-fluid">
        @include('admin::alerts')
        <div class="row">
            <div class="col-md-12">

                <div class="admin-section-title">
                    <h3><i class="admin-images"></i> {{ __('admin.generic.media') }}</h3>
                </div>
                <div class="clear"></div>

                <div id="filemanager">

                    <div id="toolbar">
                        <div class="btn-group offset-right">
                            <button type="button" class="btn btn-primary" id="upload"><i class="admin-upload"></i>
                                {{ __('admin.generic.upload') }}
                            </button>
                            <button type="button" class="btn btn-primary" id="new_folder"
                                    onclick="jQuery('#new_folder_modal').modal('show');"><i class="admin-folder"></i>
                                {{ __('admin.generic.add_folder') }}
                            </button>
                        </div>
                        <button type="button" class="btn btn-default" id="refresh"><i class="admin-refresh"></i>
                        </button>
                        <div class="btn-group offset-right">
                            <button type="button" class="btn btn-default" id="move"><i class="admin-move"></i> {{ __('admin.generic.move') }}
                            </button>
                            <button type="button" class="btn btn-default" id="rename"><i class="admin-character"></i>
                                {{ __('admin.generic.rename') }}
                            </button>
                            <button type="button" class="btn btn-default" id="delete"><i class="admin-trash"></i>
                                {{ __('admin.generic.delete') }}
                            </button>
							<button v-show="selectedFileIs('image')" type="button" class="btn btn-default" id="crop"><i class="admin-crop"></i>
                                {{ __('admin.media.crop') }}
                            </button>
                        </div>
                    </div>

                    <div id="uploadPreview" style="display:none;"></div>

                    <div id="uploadProgress" class="progress active progress-striped">
                        <div class="progress-bar progress-bar-success" style="width: 0"></div>
                    </div>

                    <div id="content">


                        <div class="breadcrumb-container">
                            <ol class="breadcrumb filemanager">
                                <li class="media_breadcrumb" data-folder="/" data-index="0"><span class="arrow"></span><strong>{{ __('admin.media.library') }}</strong></li>
                                <template v-for="(folder, index) in folders">
                                    <li v-bind:data-folder="folder" v-bind:data-index="index+1" 
									v-bind:class="{media_breadcrumb: index !== folders.length - 1}"><span
                                                class="arrow"></span>@{{ folder }}</li>
												
                                </template>
                            </ol>

                            <div class="toggle"><span>{{ __('admin.generic.close') }}</span><i class="admin-double-right"></i></div>
                        </div>
                        <div class="flex">

                            <div id="left">

                                <ul id="files">

                                    <li v-for="(file,index) in files.items">
                                        <div class="file_link" :data-folder="file.name" :data-index="index">
                                            <div class="link_icon">
                                                <template v-if="file.type.includes('image')">
                                                    <div class="img_icon" :style="imgIcon(file.path)"></div>
                                                </template>
                                                <template v-if="file.type.includes('video')">
                                                    <i class="icon admin-video"></i>
                                                </template>
                                                <template v-if="file.type.includes('audio')">
                                                    <i class="icon admin-music"></i>
                                                </template>
												<template v-if="file.type.includes('zip')">
                                                    <i class="icon admin-archive"></i>
                                                </template>
                                                <template v-if="file.type == 'folder'">
                                                    <i class="icon admin-folder"></i>
                                                </template>
                                                <template
                                                        v-if="file.type != 'folder' && !file.type.includes('image') && !file.type.includes('video') && !file.type.includes('audio') && !file.type.includes('zip')">
                                                    <i class="icon admin-file-text"></i>
                                                </template>

                                            </div>
                                            <div class="details" :data-type="file.type">
                                                <div :class="file.type">
                                                    <h4>@{{ file.name }}</h4>
                                                    <small>
                                                        <template v-if="file.type == 'folder'">
                                                        <!--span class="num_items">@{{ file.items }} file(s)</span-->
                                                        </template>
                                                        <template v-else>
                                                            <span class="file_size">@{{ file.size }}</span>
                                                        </template>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                </ul>

                                <div id="file_loader">
                                    <?php $admin_loader_img = Admin::setting('admin.loader', ''); ?>
                                    @if($admin_loader_img == '')
                                        <img src="{{ admin_asset('images/admin_loader.png') }}" alt="Admin Loader">
                                    @else
                                        <img src="{{ Admin::image($admin_loader_img) }}" alt="Admin Loader">
                                    @endif
                                    <p>{{ __('admin.media.loading') }}</p>
                                </div>

                                <div id="no_files">
                                    <h3><i class="admin-meh"></i> {{ __('admin.media.no_files_in_folder') }}</h3>
                                </div>

                            </div>

                            <div id="right">
                                <div class="right_none_selected">
                                    <i class="admin-cursor"></i>
                                    <p>{{ __('admin.media.nothing_selected') }}</p>
                                </div>
                                <div class="right_details">
                                    <div class="detail_img">
                                        <div :class="selected_file.type">
                                            <template v-if="selectedFileIs('image')">
                                                <img :src="selected_file.path"/>
                                            </template>
                                            <template v-if="selectedFileIs('video')">
                                                <video width="100%" height="auto" controls>
                                                    <source :src="selected_file.path" type="video/mp4">
                                                    <source :src="selected_file.path" type="video/ogg">
                                                    <source :src="selected_file.path" type="video/webm">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </template>
                                            <template v-if="selectedFileIs('audio')">
												<i class="admin-music"></i>
                                                <audio controls style="width:100%; margin-top:5px;">
                                                    <source :src="selected_file.path" type="audio/ogg">
                                                    <source :src="selected_file.path" type="audio/mpeg">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </template>
											<template v-if="selectedFileIs('zip')">
                                                <i class="admin-archive"></i>
                                            </template>
                                            <template v-if="selected_file.type == 'folder'">
                                                <i class="admin-folder"></i>
                                            </template>
                                            <!--template
                                                    v-if="selected_file.type != 'folder' && !selectedFileIs('audio') && !selectedFileIs('video') && !selectedFileIs('image')">
                                                <i class="admin-file-text-o"></i>
                                            </template>-->
                                        </div>

                                    </div>
                                    <div class="detail_info">
                                        <div :class="selected_file.type">
                                            <span><h4>Title:</h4>
    							            <p>@{{selected_file.name}}</p></span>
                                            <span><h4>Type:</h4>
    							            <p>@{{selected_file.type}}</p></span>

                                            <template v-if="selected_file.type != 'folder'">
    								            <span><h4>Size:</h4>
    								            <p><span class="selected_file_count">@{{ selected_file.items }} item(s)</span><span
                                                    class="selected_file_size">@{{selected_file.size}}</span></p></span>
                                                <span><h4>Public URL:</h4>
    								            <p><a :href="selected_file.path" target="_blank">Click Here</a></p></span>
                                                <span><h4>Last Modified:</h4>
    								            <p>@{{ dateFilter(selected_file.last_modified) }}</p></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- #right -->

                        </div>

                        <div class="nothingfound">
                            <div class="nofiles"></div>
                            <span>{{ __('admin.media.no_files_here') }}</span>
                        </div>

                    </div>

                    <!-- Move File Modal -->
                    <div class="modal fade modal-warning" id="move_file_modal">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><i class="admin-move"></i> {{ __('admin.media.move_file_folder') }}</h4>
                                </div>

                                <div class="modal-body">
                                    <h4>{{ __('admin.media.destination_folder') }}</h4>
                                    <select id="move_folder_dropdown">
                                        <template v-if="folders.length">
                                            <option value="/../">../</option>
                                        </template>
                                        <template v-for="dir in directories">
                                            <option :value="dir">@{{ dir }}</option>
                                        </template>
                                    </select>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                                    <button type="button" class="btn btn-warning" id="move_btn">{{ __('admin.generic.move') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Move File Modal -->

                    <!-- Rename File Modal -->
                    <div class="modal fade modal-warning" id="rename_file_modal">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><i class="admin-character"></i> {{ __('admin.media.rename_file_folder') }}</h4>
                                </div>

                                <div class="modal-body">
                                    <h4>{{ __('admin.media.new_file_folder') }}</h4>
                                    <input id="new_filename" class="form-control" type="text"
                                           :value="selected_file.name">
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                                    <button type="button" class="btn btn-warning" id="rename_btn">{{ __('admin.generic.rename') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Move File Modal -->

					<!-- Image Modal -->
					<div class="modal fade" id="imagemodal">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                </div>
								<div class="modal-body">
									<img :src="selected_file.path" class="img img-responsive" style="margin: 0 auto;">
								</div>
							  
								<div class="modal-footer text-left">
									<small class="image-title">@{{ selected_file.name }}</small>
								</div>
								
							</div>
						</div>
					</div>
					<!-- End Image Modal -->
					
					<!-- Crop Image Modal -->
                    <div class="modal fade modal-warning" id="confirm_crop_modal">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><i class="admin-warning"></i> {{ __('admin.media.crop_image') }}</h4>
                                </div>

                                <div class="modal-body">
                                    <div class="crop-container">
                                        <img v-if="selectedFileIs('image')" id="cropping-image" class="img img-responsive" :src="selected_file.path + '?' + selected_file.last_modified"/>
                                    </div>
                                    <div class="new-image-info">
                                        {{ __('admin.media.width') }} <span id="new-image-width"></span>, {{ __('admin.media.height') }}<span id="new-image-height"></span>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                                    <button type="button" class="btn btn-warning" id="crop_btn" data-confirm="{{ __('admin.media.crop_override_confirm') }}">{{ __('admin.media.crop') }}</button>
                                    <button type="button" class="btn btn-warning" id="crop_and_create_btn">{{ __('admin.media.crop_and_create') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Crop Image Modal -->
					
					
                </div><!-- #filemanager -->

                <!-- New Folder Modal -->
                <div class="modal fade modal-info" id="new_folder_modal">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                        aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><i class="admin-folder"></i> {{ __('admin.media.add_new_folder') }}</h4>
                            </div>

                            <div class="modal-body">
                                <input name="new_folder_name" id="new_folder_name" placeholder="{{ __('admin.media.new_folder_name') }}"
                                       class="form-control" value=""/>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                                <button type="button" class="btn btn-info" id="new_folder_submit">{{ __('admin.media.create_new_folder') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End New Folder Modal -->

                <!-- Delete File Modal -->
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
                                <h5 class="folder_warning"><i class="admin-warning"></i> {{ __('admin.media.delete_folder_question') }}</h5>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.generic.cancel') }}</button>
                                <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('admin.generic.delete_confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Delete File Modal -->

                <div id="dropzone"></div>
                <!-- Delete File Modal -->
                <div class="modal fade" id="upload_files_modal">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"
                                        aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><i class="admin-warning"></i> {{ __('admin.media.drag_drop_info') }}</h4>
                            </div>

                            <div class="modal-body">

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" data-dismiss="modal">{{ __('admin.generic.all_done') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Delete File Modal -->


            </div><!-- .row -->
        </div><!-- .col-md-12 -->
    </div><!-- .page-content container-fluid -->

    <input type="hidden" id="storage_path" value="{{ storage_path() }}">
    <input type="hidden" id="base_url" value="{{ route('admin.dashboard') }}">

@stop

@section('javascript')

    <script>
        MediaManager();
    </script>

@endsection
