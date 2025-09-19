<?php

namespace App\Http\Controllers\LU;

use App\Http\Controllers\Controller;
use App\Repositories\LU;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware('master')]
class SemanticTypeController extends Controller
{
    #[Get(path: '/lu/{id}/semanticTypes')]
    public function semanticTypes(string $id)
    {
        $lu = LU::byId($id);

        return view('SemanticType.child', [
            'idEntity' => $lu->idEntity,
            'root' => '@lexical_type',
        ]);
    }
}
