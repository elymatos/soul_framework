<?php

namespace App\Http\Controllers\Project;

use App\Data\Project\ManagerData;
use App\Database\Criteria;
use App\Http\Controllers\Controller;
use Collective\Annotations\Routing\Attributes\Attributes\Delete;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware("master")]
class DatasetController extends Controller
{
    #[Get(path: '/project/{id}/datasets')]
    public function datasets(int $id)
    {
        return view("Project.datasets", [
            'idProject' => $id
        ]);
    }

    #[Get(path: '/project/{id}/datasets/formNew')]
    public function datasetsFormNew(int $id)
    {
        return view("Project.datasetsNew", [
            'idProject' => $id
        ]);
    }

    #[Get(path: '/project/{id}/datasets/grid')]
    public function datasetsGrid(int $id)
    {
        $datasets = Criteria::table("dataset")
            ->select("*")
            ->where("idProject", $id)
            ->all();
        return view("Project.datasetsGrid", [
            'idProject' => $id,
            'datasets' => $datasets
        ]);
    }

    #[Post(path: '/project/{id}/datasets/new')]
    public function datasetsNew(ManagerData $data)
    {
        Criteria::table("project_manager")->insert($data->toArray());
        $this->trigger('reload-gridManagers');
        return $this->renderNotify("success", "Manager added to project.");
    }

}
