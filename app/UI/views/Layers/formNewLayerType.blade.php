<x-form
    title="New LayerType"
    hx-post="/layers/layertype/new"
    hx-target="#editarea"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                label="English Name"
                id="nameEn"
                value=""
            ></x-text-field>
        </div>
        <div class="fields">
            <div class="field">
                <x-combobox.layer-group
                    label="LayerGroup"
                    id="idLayerGroup"
                    :value="0"
                ></x-combobox.layer-group>
            </div>
            <div class="field">
                <x-number-field
                    label="LayerOrder"
                    id="layerOrder"
                    :value="0"
                ></x-number-field>
            </div>
        </div>
        <div class="fields">
            <div class="field">
                <x-checkbox
                    label="Allows Apositional"
                    id="allowsApositional"
                    :active="false"
                ></x-checkbox>
            </div>
            <div class="field">
                <x-checkbox
                    label="Is Annotation"
                    id="isAnnotation"
                    :active="false"
                ></x-checkbox>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
