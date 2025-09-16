<div
    id="gridUserTask"
    class="grid"
    hx-trigger="reload-gridTaskUsers from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/task/{{$idTask}}/users/grid"
>
    @foreach($usertasks as $usertask)
        <div class="col-4">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Project"
                            onclick="messenger.confirmDelete(`Removing user '{{$usertask->name}}' from task.`, '/task/{{$idTask}}/users/{{$usertask->idUserTask}}')"
                        ></x-delete>
                    </span>
                    <div
                        class="header"
                    >
                        #{{$usertask->idUser}}
                    </div>
                    <div class="description">
                        {{$usertask->name}}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
