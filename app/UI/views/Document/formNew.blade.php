<x-layout.page>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['/','Home'],['/corpus','Corpus/Document'],['','New Document']]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <div class="ui container h-full">
            <x-form
                title="New Document"
                hx-post="/document/new"
                class="p-2"
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
                        <x-combobox.corpus
                            id="idCorpus"
                            label="Corpus [min 3 chars]"
                        >
                        </x-combobox.corpus>
                    </div>
                </x-slot:fields>
                <x-slot:buttons>
                    <x-submit label="Save"></x-submit>
                </x-slot:buttons>
            </x-form>
        </div>
    </x-slot:main>
</x-layout.page>
