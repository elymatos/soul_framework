<x-form id="formNewDataset" title="New Dataset" :center="false" hx-post="/dataset/new">
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
{{--            <div class="field">--}}
{{--                <x-combobox.project--}}
{{--                    id="idProject"--}}
{{--                    label="Project"--}}
{{--                    value="0"--}}
{{--                >--}}
{{--                </x-combobox.project>--}}
{{--            </div>--}}
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
