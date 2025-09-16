<div
    class="grid"
    hx-trigger="reload-gridDatasets from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/project/{{$idProject}}/datasets/grid"
>
    @foreach($datasets as $dataset)
        <div class="col-4">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="remove Dataset"
                            onclick="messenger.confirmDelete(`Removing datase '{{$dataset->name}}' from project.`, '/project/{{$idProject}}/datasets/{{$dataset->idDataset}}')"
                        ></x-delete>
                    </span>

                    <div
                        class="header"
                    >
                        #{{$dataset->idDataset}}
                    </div>
                    <div class="description">
                        {{$dataset->name}}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
