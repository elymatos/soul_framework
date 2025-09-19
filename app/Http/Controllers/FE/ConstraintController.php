<?php

namespace App\Http\Controllers\FE;

use App\Http\Controllers\Controller;
use App\Repositories\Constraint;
use App\Repositories\FrameElement;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware(name: 'auth')]
class ConstraintController extends Controller
{
    #[Get(path: '/fe/{id}/constraints')]
    public function constraints(string $id)
    {
        return view('Constraint.feChild', [
            'idFrameElement' => $id,
        ]);
    }

    #[Get(path: '/fe/{id}/constraints/formNew')]
    public function constraintsFormNew(int $id)
    {
        $view = view('Constraint.feFormNew', [
            'idFrameElement' => $id,
            'frameElement' => FrameElement::byId($id),
        ]);

        return $view;
    }

    #[Get(path: '/fe/{id}/constraints/grid')]
    public function constraintsGrid(int $id)
    {
        $fe = FrameElement::byId($id);

        return view('Constraint.feGrid', [
            'idFrameElement' => $id,
            'constraints' => Constraint::listByIdConstrained($fe->idEntity),
        ]);
    }
}
