<x-form
    title="New Domain"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                label="English Name"
                id="nameEn"
                value=""
            ></x-text-field>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit
            label="Add Domain"
            hx-post="/domain/new"
        ></x-submit>
    </x-slot:buttons>
</x-form>
