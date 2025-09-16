<x-form id="formNewDatasetCorpus" title="Add Corpus to Dataset" :center="false"  hx-post="/dataset/{{$idDataset}}/corpus/new">
    <x-slot:fields>
        <x-hidden-field id="idDataset" value="{{$idDataset}}"></x-hidden-field>
        <x-combobox.corpus
            id="idCorpus"
            label="Associated Corpus"
            :value="null"
        >
        </x-combobox.corpus>
    </x-slot:fields>
    <x-slot:buttons>
        <x-submit label="Add"></x-submit>
    </x-slot:buttons>
</x-form>
