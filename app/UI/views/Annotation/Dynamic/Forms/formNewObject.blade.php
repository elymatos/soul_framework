<h3 class="ui header">Create new object</h3>
<form
    class="ui form p-4 border"
    hx-post="/annotation/dynamic/createNewObjectAtLayer"
>
    <div class="w-1/2">
        <x-form::hidden-field id="idDocument" value="{{$idDocument}}"></x-form::hidden-field>
        <x-form::hidden-field id="idDynamicObject" value="0"></x-form::hidden-field>
{{--        <div class="field">--}}
{{--            <x-form::combobox.layer-deixis--}}
{{--                label="Layer"--}}
{{--                name="idLayerType"--}}
{{--                :value="0"--}}
{{--            ></x-form::combobox.layer-deixis>--}}
{{--        </div>--}}
        <div class="two fields">
            <div class="field">
                <label>Start frame <span class="text-primary cursor-pointer" @click="copyFrameFor('startFrame')">[Copy from video]</span></label>
                <div class="ui medium input">
                    <input type="text" name="startFrame" placeholder="1" value="1">
                </div>
            </div>
            <div class="field">
                <label>End frame <span class="text-primary cursor-pointer" @click="copyFrameFor('endFrame')">[Copy from video]</span></label>
                <div class="ui medium input">
                    <input type="text" name="endFrame" placeholder="1" value="1">
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

