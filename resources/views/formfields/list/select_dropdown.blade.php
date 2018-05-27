@if(property_exists($options, 'options'))
    @if($dataTypeContent->{$dataType->field . '_page_slug'})
        <a href="{{ $dataTypeContent->{$dataType->field . '_page_slug'} }}">{!! $options->options->{$dataTypeContent->{$dataType->field}} !!}</a>
    @elseif(isset($options->options->{$dataTypeContent->{$dataType->field}}))
        {!! $options->options->{$dataTypeContent->{$dataType->field}} !!}
    @else
        {{ $dataTypeContent->{$dataType->field} }}
    @endif
@else
    {{ $dataTypeContent->{$dataType->field} }}
@endif