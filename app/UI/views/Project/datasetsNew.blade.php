<x-form
    id="formNewDataset"
    title="New Dataset"
    :center="false"
    hx-post="/project/datasets/new"
>
    <x-slot:fields>
        <x-hidden-field
            id="idProject"
            :value="$idProject"
        ></x-hidden-field>
        <div class="field">
            <x-combobox.dataset
                id="idDataset"
                label="Use existing dataset"
                :value="0"
                ></x-combobox.dataset>
        </div>
        <div class="field">
            <label>OR create a new one</label>
        </div>
        <div class="field">
            <x-text-field
                label="Name"
                id="name"
                value=""
            ></x-text-field>
        </div>
        <div class="field">
            <x-multiline-field
                label="Description"
                id="description"
                value=""
            ></x-multiline-field>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
