<form
    class="ui form p-4 border"
    hx-post="/annotation/dynamic/updateObjectComment"
>
{{--    <div class="flex gap-2">--}}
{{--        <div class="title">Comment for Object: #{{$order}}</div>--}}
{{--        <div class="flex h-2rem gap-2">--}}
{{--            <div class="ui label">--}}
{{--                Range--}}
{{--                <div class="detail">{{$object->startFrame}}/{{$object->endFrame}}</div>--}}
{{--            </div>--}}
{{--            <div class="ui label wt-tag-id">--}}
{{--                #{{$object->idDynamicObject}}--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        @if($object->comment->email)--}}
{{--            <div class="text-sm">Created by [{{$object->comment->email}}] at [{{$object->comment->createdAt}}]</div>--}}
{{--        @endif--}}
{{--    </div>--}}
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
        hx-delete="/annotation/dynamic/comment/{{$object->idDocument}}/{{$object?->idDynamicObject}}"
{{--        onclick="annotation.objects.deleteObjectComment({{$object?->idDynamicObject}})"--}}
    >Delete
    </button>
</form>
