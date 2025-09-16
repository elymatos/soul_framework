<x-form
    title="New RelationGroup"
    hx-post="/relations/relationgroup/new"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                id="nameEn"
                label="English Name"
                value="">

            </x-text-field>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Add RelationGroup"></x-submit>
    </x-slot:buttons>
</x-form>
