@php 
    $input = 'input_' . $row->field;

    if( strpos($dataTypeContent->{$row->field}, 'http://') === false && strpos($dataTypeContent->{$row->field}, 'https://') === false) {
        $image = Admin::image( $dataTypeContent->{$row->field} );
    } else {
        $image = $dataTypeContent->{$row->field};
    }
@endphp
<div id="cropper-{{ $row->field }}">
    <input id="{{ $input }}" type="hidden" name="{{ $row->field }}"
        value="@if(isset($dataTypeContent->{$row->field})){{ old($row->field, $dataTypeContent->{$row->field}) }}@elseif(isset($options->default)){{ old($row->field, $options->default) }}@else{{ old($row->field) }}@endif">

    @foreach($options->cropper as $photoParams)
        <input type="hidden" name="{{ $row->field . '_' . $photoParams->name }}"
           value="{{ old($row->field . '_' . $photoParams->name ) }}">
    @endforeach

    <button type="button" class="btn btn-success" id="upload-{{ $input }}">
        <i class="admin-upload"></i> {{ __('admin.generic.upload') }}
    </button>

    @if($image)
        <button id="edit-{{ $input }}" type="button" class="btn btn-primary cropper-edit">
            <i class="admin-edit"></i> {{ __('admin.generic.edit') }}
        </button>

        <a type="button" class="btn btn-warning" id="download-{{ $input }}" href="{{ $image }}" download="{{ $image }}" target="_blank">
            <i class="admin-download"></i> {{ __('admin.generic.download') }}
        </a>
    @endif
    <div id="uploadPreview" style="display:none;"></div>
    @foreach($options->cropper as $photoParams)
        <div class="{{ $photoParams->name }} photo-block disabled">
            <div class="cropMain"></div>
            <div class="cropSlider"></div>
        </div>
    @endforeach
</div>

<style>
    @foreach($options->cropper as $photoParams)
        @php
            $width = ($photoParams->size->width > 300) ? $photoParams->size->width / 2 : $photoParams->size->width;
            $height = ($photoParams->size->width > 300) ? $photoParams->size->height / 2 : $photoParams->size->height;
        @endphp

        #cropper-{{ $row->field }} .{{ $photoParams->name }} .cropMain {
            width: {{ $width }}px;
            height: {{ $height }}px;
        }
        #cropper-{{ $row->field }} .{{ $photoParams->name }} .cropSlider {
            width: {{ $width }}px;
        }
        @isset($photoParams->watermark)
        #cropper-{{ $row->field }} .{{ $photoParams->name }} .cropMain .crop-container:after {
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
        @foreach($options->cropper as $photoParams)
            @php
                $cropper = $row->field . $photoParams->name;
            @endphp

            var {{ $cropper }} = new Image.cropper();
            {{ $cropper }}.init("#cropper-{{ $row->field }} .{{ $photoParams->name }}");

            @if ( old($row->field) )
                {{ $cropper }}.loadImg("{{ storage_url(old($row->field)) }}?{{ time() }}");
            @elseif ( isset($dataTypeContent->{$row->field}) )
                {{ $cropper }}.loadImg("{{ $dataTypeContent->getCroppedPhoto($row->field, $photoParams->name, $photoParams->size->name) }}?{{ time() }}");
            @endif
        @endforeach

        CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        $("#upload-{{ $input }}").dropzone({
            url: "{{ route('admin.media.upload') }}",
            previewsContainer: "#uploadPreview",

            sending: function(file, xhr, formData) {
                formData.append("_token", CSRF_TOKEN);
                formData.append("upload_path", "{{ $dataType->slug.'/'.date('F').date('Y') }}");
            },
            success: function(e, res){
                if (res.success){
                    uploadImage(res.path, $("#upload-{{ $input }}").parent());
                    toastr.success(res.message, "Sweet Success!");
                } else {
                    toastr.error(res.message, "Whoopsie!");
                }
            },
            error: function(e, res, xhr){
                toastr.error(res, "Whoopsie");
            }
        });

        $('#edit-{{ $input }}').click(function(){
            var cropperBlock = $(this).parent();
            var imagePath = "{{ $dataTypeContent->{$row->field} }}";

            uploadImage(imagePath, cropperBlock);
        });

        function uploadImage(imagePath, cropperBlock){
            var image;
            cropperBlock.find(".crop-container").remove();
            cropperBlock.find(".noUi-base").remove();

            $('#{{ $input }}').val(imagePath);
            cropperBlock.children().removeClass('disabled');

            @foreach($options->cropper as $photoParams)
                @php
                    $cropper = $row->field . $photoParams->name;
                @endphp

                var {{ $cropper }} = new Image.cropper();
                {{ $cropper }}.init("#cropper-{{ $row->field }} .{{ $photoParams->name }}");


                function protocolExists(url) {
                   if (/^(f|ht)tps?:\/\//i.test(url)) {
                      return true;
                   }
                   return false;
                }

                image = imagePath;
                if (!protocolExists(imagePath)) {
                    image = "/{{ basename(storage_url('')) }}/" + imagePath;
                }

                {{ $cropper }}.loadImg(image);

                $("button:submit").click(function() {
                    $('input[name={{ $row->field.'_'.$photoParams->name }}]').val(
                        JSON.stringify(coordinates({{ $cropper }}))
                    );
                });
            @endforeach
        }
    });
    </script>
@endsection
