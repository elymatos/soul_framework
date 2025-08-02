<?php

namespace App\Http\Controllers;

use App\Data\Components\FrameFEData;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware(name: 'web')]
class ComponentsController extends Controller
{
    #[Get(path: '/components/fesByFrame')]
    public function feCombobox(FrameFEData $frame)
    {
        return view("components.fesByFrame", [
            'idFrame' => $frame->idFrame
        ]);
    }

}
