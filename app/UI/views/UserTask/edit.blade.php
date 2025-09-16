<x-layout.object>
    <x-slot:name>
        UserTask
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$usertask->idUserTask}}
        </div>
        <x-button
            label="Delete"
            color="danger"
            onclick="messenger.confirmDelete(`Removing UserTask '{{$usertask->idUserTask}}'.`, '/usertask/{{$usertask->idUserTask}}')"
        ></x-button>
    </x-slot:detail>
    <x-slot:description>
        <span>User:{{$usertask->userName}} / Task: {{$usertask->taskName}}</span>
    </x-slot:description>
    <x-slot:main>
        @include("UserTask.menu")
    </x-slot:main>
</x-layout.object>
