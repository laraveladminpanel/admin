@if(property_exists($options, 'relationship'))
    @foreach($dataTypeContent->{$dataType->field} as $item)
        @if($item->{$dataType->field . '_page_slug'})
        <a href="{{ $item->{$dataType->field . '_page_slug'} }}">{{ $item->{$dataType->field} }}</a>@if(!$loop->last), @endif
        @else
        {{ $item->{$dataType->field} }}
        @endif
    @endforeach

    {{-- $dataTypeContent->{$dataType->field}->implode($options->relationship->label, ', ') --}}
@elseif(property_exists($options, 'options'))
    @if(!empty($dataTypeContent->{$dataType->field}))
        @foreach(json_decode($dataTypeContent->{$dataType->field}) as $item)
         {{ $options->options->{$item} . (!$loop->last ? ', ' : '') }}
        @endforeach
    @endif
@endif
