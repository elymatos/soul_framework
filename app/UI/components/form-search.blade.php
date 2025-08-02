<form class="ui form"
      method="post"
    {{$attributes}}
>
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="field">
        <div class="ui left icon input w-full">
            <i class="search icon"></i>
            <input
                type="search"
                id="{{$id}}"
                name="{{$id}}"
                placeholder="{{$placeholder}}"

            >
        </div>
    </div>
</form>
