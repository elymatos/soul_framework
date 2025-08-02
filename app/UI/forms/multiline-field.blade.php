<div class="form-field field">
    @if(isset($label))
        <label for="{{$id}}">{{$label}}</label>
    @endif
    <textarea
        id="{{$id}}"
        name="{{$id}}"
        placeholder="{{$placeholder ?? ''}}"
        {{$attributes->class(["w-full"])}}
        rows="{{$rows}}"
    >{{$value}}</textarea>
</div>
