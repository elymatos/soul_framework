<div
    id="gridDatasetCorpus"
    class="grid"
    hx-trigger="reload-gridDatasetCorpus from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/dataset/{{$idDataset}}/corpus/grid"
>
    @foreach($corpus as $c)
        <div class="col-4">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Corpus from Dataset"
                            onclick="messenger.confirmDelete(`Removing association to Corpus '{{$c->name}}'.`, '/dataset/{{$idDataset}}/corpus/{{$c->idCorpus}}')"
                        ></x-delete>
                    </span>
                    <div
                        class="header"
                    >
                        {{$c->name}}
                    </div>
                    <div class="description">
                        {{$c->description}}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
