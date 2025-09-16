<div
    class="grid"
    hx-trigger="reload-gridManagers from:body"
    hx-target="this"
    hx-swap="outerHTML"
    hx-get="/project/{{$idProject}}/users/grid"
>
    @foreach($managers as $manager)
        <div class="col-4">
            <div class="ui card w-full">
                <div class="content">
                    <span class="right floated">
                        <x-delete
                            title="delete Project"
                            onclick="messenger.confirmDelete(`Removing manager '{{$manager->name}}' from project.`, '/project/{{$idProject}}/users/{{$manager->idUser}}')"
                        ></x-delete>
                    </span>
                    <div
                        class="header"
                    >
                        #{{$manager->idUser}}
                    </div>
                    <div class="description">
                        {{$manager->name}}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
