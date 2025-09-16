<x-layout.object>
    <x-slot:name>
        <span class="color_user">{{$task->name}}</span>
    </x-slot:name>
    <x-slot:detail>
        <div class="ui label tag wt-tag-id">
            #{{$task->idTask}}
        </div>
        <x-button
            label="Delete"
            color="danger"
            onclick="messenger.confirmDelete(`Removing Task '{{$task->name}}'.`, '/task/{{$task->idTask}}')"
        ></x-button>
    </x-slot:detail>
    <x-slot:description>
    </x-slot:description>
    <x-slot:main>
        @include("Task.menu")
    </x-slot:main>
</x-layout.object>
