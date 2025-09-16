<x-layout.object>
    <x-slot:name>
        <span class="color_user">{{$project->name}}</span>
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$project->idProject}}
        </div>
        <x-button
            label="Delete"
            color="danger"
            onclick="messenger.confirmDelete(`Removing Project '{{$project?->name}}'.`, '/project/{{$project->idProject}}')"
        ></x-button>
    </x-slot:detail>
    <x-slot:description>
        {{$project->description}}
    </x-slot:description>
    <x-slot:main>
        @include("Project.menu")
    </x-slot:main>
</x-layout.object>
