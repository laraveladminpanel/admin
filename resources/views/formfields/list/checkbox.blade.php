@if($options && property_exists($options, 'on') && property_exists($options, 'off'))
    @if($dataTypeContent->{$dataType->field})
    <span class="label label-info">{{ $options->on }}</span>
    @else
    <span class="label label-primary">{{ $options->off }}</span>
    @endif
@else
{{ $dataTypeContent->{$dataType->field} }}
@endif