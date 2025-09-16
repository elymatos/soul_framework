<x-form
    title="New GenericLabel"
    hx-post="/layers/genericlabel/new"
    hx-target="#editarea"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                label="Name"
                id="name"
                value=""
            ></x-text-field>
        </div>
        <div class="field">
            <x-multiline-field
                label="Definition"
                id="definition"
                value=""
            ></x-multiline-field>
        </div>
        <div class="three fields">
            <div class="field">
                <x-combobox.language
                    id="idLanguage"
                    label="Language"
                    value=""
                ></x-combobox.language>
            </div>
            <div class="field">
                <x-combobox.color
                    id="idColor"
                    label="Color"
                    value=""
                    placeholder="Color"
                ></x-combobox.color>
            </div>
            <div class="field">
                <x-combobox.layer-type
                    label="LayerType"
                    id="idLayerType"
                    :value="0"
                ></x-combobox.layer-type>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
