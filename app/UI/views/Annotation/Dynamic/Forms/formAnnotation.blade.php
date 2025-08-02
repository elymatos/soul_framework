<form
    class="ui form p-4 border"
>
    <x-form::hidden-field id="idDocument" value="{{$object->idDocument}}"></x-form::hidden-field>
    <x-form::hidden-field id="idDynamicObject" value="{{$object?->idDynamicObject}}"></x-form::hidden-field>
{{--    @if(!is_null($object->idGenericLabel) || ($object->layerGroup == 'Deixis'))--}}
{{--        <div class="ui two column stackable grid relative">--}}
{{--            <div class="column pr-8">--}}
{{--                <div class="field w-full">--}}
{{--                    <x-form::combobox.gl--}}
{{--                        id="idGenericLabel"--}}
{{--                        name="idGenericLabel"--}}
{{--                        label="Label"--}}
{{--                        :value="$object?->idGenericLabel ?? 0"--}}
{{--                        :idLayerType="$object?->idLayerType ?? 0"--}}
{{--                        :hasNull="false"--}}
{{--                    ></x-form::combobox.gl>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}
    <div class="ui two column stackable grid relative">
        <div class="column pr-8">
            <x-form::frame-fe
                :object="$object"
            ></x-form::frame-fe>
        </div>
        <div class="column pl-8">
            <div class="field w-full">
                <x-form::search.lu
                    id="idLU"
                    label="CV Name"
                    placeholder="Select a CV name"
                    search-url="/lu/list/forSelect"
                    value="{{ old('idFrame', $object?->idLU ?? '') }}"
                    display-value="{{ old('frame', $object->lu ?? '') }}"
                    modal-title="Search CV Name"
                ></x-form::search.lu>
            </div>
        </div>
        <div class="ui vertical divider">
            and
        </div>
    </div>
    <button
        type="submit"
        class="ui medium button"
        hx-post="/annotation/dynamic/updateObjectAnnotation"
        hx-target="#o{{$object?->idDynamicObject}}"
        hx-swap="innerHTML"
    >
        Save
    </button>
</form>
