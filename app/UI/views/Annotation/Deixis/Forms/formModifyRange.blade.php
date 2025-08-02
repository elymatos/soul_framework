<form
    class="ui form p-4 border"
    hx-post="/annotation/deixis/updateObjectRange"
>
    <div class="w-2/3">
        <x-form::hidden-field id="idDocument" value="{{$object->idDocument}}"></x-form::hidden-field>
        <x-form::hidden-field id="idDynamicObject" value="{{$object->idDynamicObject}}"></x-form::hidden-field>
        <div class="two fields">
            <div class="field">
                <label>New start frame <span class="text-primary cursor-pointer" @click="copyFrameFor('startFrame')">[Copy from video]</span></label>
                <div class="ui medium input">
                    <input type="text" name="startFrame" placeholder="0" value="{{$object->startFrame}}">
                </div>
            </div>
            <div class="field">
                <label>New end frame <span class="text-primary cursor-pointer" @click="copyFrameFor('endFrame')">[Copy from video]</span></label>
                <div class="ui medium input">
                    <input type="text" name="endFrame" placeholder="0" value="{{$object->endFrame}}">
                </div>
            </div>
        </div>
        <button
            type="submit"
            class="ui button medium"
        >Submit
        </button>
    </div>
</form>

