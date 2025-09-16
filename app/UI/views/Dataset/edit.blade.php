<x-layout.object>
    <x-slot:name>
        <span class="color_user">{{$dataset->name}} [{{$dataset->project->name}}]</span>
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$dataset->idDataset}}
        </div>
        <x-button
            label="Delete"
            color="danger"
            onclick="messenger.confirmDelete(`Removing Dataset '{{$dataset->name}}'.`, '/dataset/{{$dataset->idDataset}}')"
        ></x-button>
    </x-slot:detail>
    <x-slot:description>
        {{$dataset->description}}
    </x-slot:description>
    <x-slot:main>
        @include("Dataset.menu")
    </x-slot:main>
</x-layout.object>
