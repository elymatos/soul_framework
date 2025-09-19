<?php

namespace App\Http\Controllers\Frame;

use App\Http\Controllers\Controller;
use App\Repositories\Frame;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware('master')]
class SemanticTypeController extends Controller
{
    #[Get(path: '/frame/{id}/semanticTypes')]
    public function semanticTypes(string $id)
    {
        $frame = Frame::byId($id);

        return view('SemanticType.child', [
            'idEntity' => $frame->idEntity,
            'root' => '@framal_type',
        ]);
    }
}
