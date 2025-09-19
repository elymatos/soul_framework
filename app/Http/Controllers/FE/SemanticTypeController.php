<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use App\Repositories\FrameElement;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware('master')]
class SemanticTypeController extends Controller
{
    #[Get(path: '/fe/{id}/semanticTypes')]
    public function semanticTypes(string $id)
    {
        $fe = FrameElement::byId($id);

        return view('SemanticType.child', [
            'idEntity' => $fe->idEntity,
            'root' => '@ontological_type',
        ]);
    }
}
