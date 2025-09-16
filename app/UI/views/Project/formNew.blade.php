<x-form id="formNewProject" title="New Project" :center="false" hx-post="/project/new">
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
                label="Description"
                id="description"
                value=""
            ></x-multiline-field>
        </div>
        <div class="field">
            <x-combobox.project-group
                label="Project Group"
                id="idProjectGroup"
                value="0"
            ></x-combobox.project-group>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
