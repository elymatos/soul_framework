@php use App\Database\Criteria;use App\Repositories\User;use App\Services\AppService; use App\Services\MessageService; @endphp
<x-layout.page>
    <x-slot:head>
        <x-breadcrumb :sections="[['','Home']]"></x-breadcrumb>
    </x-slot:head>
    <x-slot:main>
        @php
            $idUser = AppService::getCurrentIdUser();
            $user = User::byId($idUser);
            $isManager = User::isManager($user);
            $messages = MessageService::getMessagesToUser($idUser);
            $tasks = Criteria::table("view_usertask as ut")
                ->join("view_task_manager as tm","ut.idTask","=","tm.idTask")
                ->select("ut.projectName","ut.taskName","ut.taskGroupName")
                ->selectRaw("GROUP_CONCAT(DISTINCT tm.userName SEPARATOR ',') as manager")
                ->groupByRaw("ut.projectName,ut.taskName,ut.taskGroupName")
                ->where("ut.idUser",$idUser)
                ->where("ut.idProject","<>", 1)
                ->all();
//            $projects = collect($rows)->groupBy('projectName')->toArray();
        @endphp
        @include("App.messages")
<<<<<<< HEAD

        @php
            $tasksForManager =  Criteria::table("view_task_manager as tm")
                ->select("tm.projectName","tm.taskName","tm.taskGroupName")
                ->where("tm.idUser",$idUser)
                ->orderBy("tm.projectName")
                ->orderBy("tm.taskName")
                ->all();
        @endphp
        <div class="relative h-full overflow-auto">
            <div class="absolute top-0 left-0 bottom-0 right-0">
                @if(count($tasksForManager) > 0)
                <div class="ui container">
                    <div class="ui card w-full">
                        <div class="flex-grow-0 content h-4rem bg-gray-100">
                            <h2 class="ui header">Managed project/tasks</h2>
                        </div>
                        <div class="flex-grow-1 content bg-white">
                            <table class="ui striped small compact table">
                                <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Task</th>
                                    <th>Task group</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tasksForManager as $task)
                                    <tr>
                                        <td>
                                            {{$task->projectName}}
                                        </td>
                                        <td>
                                            {{$task->taskName}}
                                        </td>
                                        <td>
                                            {{$task->taskGroupName}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if(!$isManager)
=======
        @if(!$isManager)
            <div class="relative h-full overflow-auto">
                <div class="absolute top-0 left-0 bottom-0 right-0">
>>>>>>> claude
                    <div class="ui container">
                        <div class="ui card w-full">
                            <div class="flex-grow-0 content h-4rem bg-gray-100">
                                <h2 class="ui header">My tasks</h2>
                            </div>
                            <div class="flex-grow-1 content bg-white">
                                <table class="ui striped small compact table">
                                    <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Task</th>
                                        <th>Task group</th>
                                        <th>Manager(s)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>
                                                {{$task->projectName}}
                                            </td>
                                            <td>
                                                {{$task->taskName}}
                                            </td>
                                            <td>
                                                {{$task->taskGroupName}}
                                            </td>
                                            <td>
                                                {{$task->manager}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @php
                $projectsForManager =  Criteria::table("project_manager as pm")
                    ->join("project as p","p.idProject","=","pm.idProject")
                    ->join("user as u","u.idUser","=","pm.idUser")
                    ->select("p.name as projectName","p.idProject")
                    ->where("u.idUser",$idUser)
                    ->where("p.idProject","<>", 1)
                    ->all();
            @endphp
            <div class="relative h-full overflow-auto">
                <div class="absolute top-0 left-0 bottom-0 right-0">
                    <div class="ui container">
                        <div class="ui card w-full">
                            <div class="flex-grow-0 content h-4rem bg-gray-100">
                                <h2 class="ui header">Managed projects</h2>
                            </div>
                            <div class="flex-grow-1 content bg-white">
                                <table class="ui striped small compact table">
                                    <thead>
                                    <tr>
                                        <th>Project</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($projectsForManager as $project)
                                        <tr>
                                            <td>
                                                {{$project->projectName}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
{{--                        <div class="ui card w-full">--}}
{{--                            <div class="flex-grow-0 content h-4rem bg-gray-100">--}}
{{--                                <h2 class="ui header">Tasks attributed</h2>--}}
{{--                            </div>--}}
{{--                            <div class="flex-grow-1 content bg-white">--}}
{{--                                <table class="ui striped small compact table">--}}
{{--                                    <thead>--}}
{{--                                    <tr>--}}
{{--                                        <th>Document</th>--}}
{{--                                        <th>Annotator</th>--}}
{{--                                    </tr>--}}
{{--                                    </thead>--}}
{{--                                    <tbody>--}}
{{--                                    @foreach($projectsForManager as $project)--}}
{{--                                        <tr>--}}
{{--                                            <td colspan="2">--}}
{{--                                                {{$project->projectName}}--}}
{{--                                            </td>--}}
{{--                                        </tr>--}}
{{--                                        @php--}}
{{--                                            $tasksAttributed = Criteria::table("view_usertask_docs as utd")--}}
{{--                                                ->join("view_project_docs as pd","pd.idCorpus","=","utd.idCorpus")--}}
{{--                                                ->join("user as u","u.idUser","=","utd.idUser")--}}
{{--                                                ->select("utd.taskName","utd.documentName","u.email")--}}
{{--                                                ->where("pd.idProject","=", $project->idProject)--}}
{{--                                                ->where("pd.idLanguage",AppService::getCurrentIdLanguage())--}}
{{--                                                ->all();--}}
{{--                                        @endphp--}}
{{--                                        @foreach($tasksAttributed as $task)--}}
{{--                                            <tr>--}}
{{--                                                <td>--}}
{{--                                                    {{$task->documentName}}--}}
{{--                                                </td>--}}
{{--                                                <td>--}}
{{--                                                    {{$task->email}}--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        @endforeach--}}
{{--                                    @endforeach--}}
{{--                                    </tbody>--}}
{{--                                </table>--}}
{{--                            </div>--}}
{{--                        </div>--}}

                    </div>
                </div>
            </div>
        @endif
    </x-slot:main>
</x-layout.page>

