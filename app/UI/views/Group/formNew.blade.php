<x-form
    title="New Group"
    hx-post="/group/new"
>
    <x-slot:fields>
        <div class="two fields">
            <div class="field">
                <x-text-field
                    label="Name"
                    id="name"
                    value=""
                ></x-text-field>
            </div>
            <div class="field">
                <x-text-field
                    label="Description"
                    id="description"
                    value=""
                ></x-text-field>
            </div>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Save"></x-submit>
    </x-slot:buttons>
</x-form>
