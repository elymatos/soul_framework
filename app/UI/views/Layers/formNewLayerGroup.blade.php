<x-form
    title="New LayerGroup"
    hx-post="/layers/layergroup/new"
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
            <x-combobox.options
                label="Type"
                id="type"
                :options="['Deixis' => 'Deixis','Text' => 'Text']"
                value=""
            ></x-combobox.options>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
