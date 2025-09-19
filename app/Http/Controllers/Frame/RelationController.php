<?php

namespace App\Http\Controllers\Frame;

use App\Http\Controllers\Controller;
use App\Repositories\Frame;
use App\Services\RelationService;
use App\Services\ReportFrameService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware('master')]
class RelationController extends Controller
{
    #[Get(path: '/frame/{id}/relations')]
    public function relations(string $id)
    {
        return view('Relation.frameChild', [
            'idFrame' => $id,
            'frame' => Frame::byId($id),
        ]);
    }

    #[Get(path: '/frame/{id}/relations/formNew')]
    public function formNewRelation(string $id)
    {
        return view('Relation.frameFormNew', [
            'idFrame' => $id,
        ]);
    }

    #[Get(path: '/frame/{id}/relations/grid')]
    public function gridRelation(string $id)
    {
        $frame = Frame::byId($id);
        $relations = ReportFrameService::getRelations($frame);

        return view('Relation.frameGrid', [
            'idFrame' => $id,
            'relations' => $relations, // RelationService::listRelationsFrame($id)
        ]);
    }
}
