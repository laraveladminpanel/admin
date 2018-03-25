@php 
    $id = 'input_' . $row->field;

    if( strpos($dataTypeContent->{$row->field}, 'http://') === false && strpos($dataTypeContent->{$row->field}, 'https://') === false) {
        $image = Admin::image( $dataTypeContent->{$row->field} );
    } else {
        $image = $dataTypeContent->{$row->field};
    }
@endphp

<input id="{{ $id }}" type="hidden" name="{{ $row->field }}"
    value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@elseif(isset($options->default)){{ old($row->field, $options->default) }}@else{{ old($row->field) }}@endif">

@foreach($options->crop as $photoParams)
    <input type="hidden" name="{{ $row->field . '_' . $photoParams->name }}"
       value="{{ old($row->field . '_' . $photoParams->name ) }}">
@endforeach

<button type="button" class="btn btn-success" id="upload-{{ $id }}">
    <i class="admin-upload"></i> {{ __('admin.generic.upload') }}
</button>

@if($image)
    <button type="button" class="btn btn-primary" id="edit-{{ $id }}" >
        <i class="admin-edit"></i> {{ __('admin.generic.edit') }}
    </button>

    <a type="button" class="btn btn-warning" id="download-{{ $id }}" href="{{ $image }}" download="{{ $image }}" target="_blank">
        <i class="admin-download"></i> {{ __('admin.generic.download') }}
    </a>
@endif
<div id="uploadPreview" style="display:none;"></div>

<div id="dropzone-{{ $id }}" class="dropzone-block disabled">
    @foreach($options->crop as $photoParams)
        <div class="foto-send">
            <div class="photo-block {{ $photoParams->name }}">
                <div class="cropMain"></div>
                <div class="cropSlider"></div>
            </div>
        </div>
    @endforeach
</div>

<style>
    @foreach($options->crop as $photoParams)
        @php
            $width = ($photoParams->size->width > 300) ? $photoParams->size->width / 2 : $photoParams->size->width;
            $height = ($photoParams->size->width > 300) ? $photoParams->size->height / 2 : $photoParams->size->height;
        @endphp

        .{{ $photoParams->name }} > .cropMain {
            width: {{ $width }}px;
            height: {{ $height }}px;
        }
        .{{ $photoParams->name }} > .cropSlider {
            width: {{ $width }}px;
        }
        @isset($photoParams->watermark)
        .{{ $photoParams->name }} > .cropMain > .crop-container:after {
            background: url({{ asset_with_time('/' . $photoParams->watermark) }}) no-repeat;
            background-size: {{ $width }}px;
        }
        @endisset
    @endforeach
</style>

@section('javascript')
    @parent
    <script>
    $(document).ready(function() {
        @foreach($options->crop as $photoParams)
            var {{ $photoParams->name }} = new Image.crop();
            {{ $photoParams->name }}.init(".{{ $photoParams->name }}");

            @if ( old($row->field) )
                {{ $photoParams->name }}.loadImg("{{ storage_url(old($row->field)) }}?{{ time() }}");
            @elseif ( isset($dataTypeContent->{$row->field}) )
                {{ $photoParams->name }}.loadImg("{{ $dataTypeContent->getCroppedPhoto($photoParams->name, $photoParams->size->name) }}?{{ time() }}");
            @endif
        @endforeach

        CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $("#upload-{{ $id }}, #dropzone-{{ $id }}").dropzone({
            url: "{{ route('admin.media.upload') }}",
            previewsContainer: "#uploadPreview",

            sending: function(file, xhr, formData) {
                formData.append("_token", CSRF_TOKEN);
                formData.append("upload_path", "{{ $dataType->slug.'/'.date('F').date('Y') }}");
            },
            success: function(e, res){
                if (res.success){
                    uploadImage(res.path)
                    toastr.success(res.message, "Sweet Success!");
                } else {
                    toastr.error(res.message, "Whoopsie!");
                }
            },
            error: function(e, res, xhr){
                toastr.error(res, "Whoopsie");
            }
        });

        $('#edit-{{ $id }}').click(function(){
            uploadImage("{{ $dataTypeContent->{$row->field} }}")
        });

        function uploadImage(imagePath){
            var image;
            $("#dropzone-{{ $id }}").find(".crop-container").remove();
            $("#dropzone-{{ $id }}").find(".noUi-base").remove();

            $('#{{ $id }}').val(imagePath);
            $('#dropzone-{{ $id }}').removeClass('disabled');

            @foreach($options->crop as $photoParams)
                image = imagePath;
                var {{ $photoParams->name }} = new Image.crop();
                {{ $photoParams->name }}.init(".{{ $photoParams->name }}");


                function protocolExists(url) {
                   if (/^(f|ht)tps?:\/\//i.test(url)) {
                      return true;
                   }
                   return false;
                }

                if (!protocolExists(imagePath)) {
                    image = "/{{ basename(storage_url('')) }}/" + imagePath;
                }

                {{ $photoParams->name }}.loadImg(image);

                $("button:submit").click(function() {
                    $('input[name={{ $row->field.'_'.$photoParams->name }}]').val(
                        JSON.stringify(coordinates({{ $photoParams->name }}))
                    );
                })
            @endforeach
        }
    });
    </script>
@endsection
