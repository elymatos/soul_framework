<form
    class="ui form p-4 border"
    hx-post="/annotation/dynamicMode/updateObjectComment"
>
    <x-form::hidden-field id="idDocument" value="{{$object->idDocument}}"></x-form::hidden-field>
    <x-form::hidden-field id="idDynamicObject" value="{{$object?->idDynamicObject}}"></x-form::hidden-field>
    <x-form::hidden-field id="createdAt" value="{{$object?->comment->createdAt}}"></x-form::hidden-field>
    <div class="field mr-1">
        <x-form::multiline-field
            label="Comment"
            id="comment"
            rows="4"
            :value="$object->comment->comment ?? ''"
        ></x-form::multiline-field>
    </div>
    <button type="submit" class="ui medium button">
        Save
    </button>
    <button
        class="ui medium button danger"
        type="button"
        hx-delete="/annotation/deixis/comment/{{$object->idDocument}}/{{$object?->idDynamicObject}}"
    >Delete
    </button>
</form>
