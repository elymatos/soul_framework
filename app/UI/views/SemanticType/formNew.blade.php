<x-form
    title="New SemanticType"
>
    <x-slot:fields>
        <div class="field">
            <x-text-field
                id="semanticTypeName"
                label="English name"
                value=""
            ></x-text-field>
        </div>
        <div class="field">
            <x-combobox.domain
                id="idDomain"
                label="Domain"
            ></x-combobox.domain>
        </div>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit
            label="Add SemanticType"
            hx-post="/semanticType/new"
        ></x-submit>
    </x-slot:buttons>
</x-form>
