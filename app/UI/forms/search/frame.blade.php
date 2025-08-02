@if(isset($label))
<label for="{{$id}}">{{$label}}</label>
@endif
<x-ui::search
    {{ $attributes }}
    name="{{$name}}"
    placeholder="{{$placeholder ?? 'Select a frame'}}"
    search-url="/frame/list/forSelect"
    display-name="frame"
    display-field="name"
    value="{{$value}}"
    display-value="{{ $displayValue }}"
    value-field="idFrame"
    modal-title="{{$modalTitle}}"
/>
