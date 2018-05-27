@if(!empty($dataTypeContent->{$dataType->field}) )
    @if(json_decode($dataTypeContent->{$dataType->field}))
        @foreach(json_decode($dataTypeContent->{$dataType->field}) as $file)
            <a href="{{ Storage::disk(config('admin.storage.disk'))->url($file->download_link) ?: '' }}" target="_blank">
                {{ $file->original_name ?: '' }}
            </a>
            <br/>
        @endforeach
    @else
        <a href="{{ Storage::disk(config('admin.storage.disk'))->url($dataTypeContent->{$dataType->field}) }}" target="_blank">
            Download
        </a>
    @endif
@endif