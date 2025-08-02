<?php

namespace App\Http\Controllers\UI;


use App\Http\Controllers\Controller;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;

#[Middleware("master")]
class DesignController extends Controller
{
    #[Get(path: '/design/appNoTools')]
    public function appNoTools()
    {
        return view("UI.Design.appNoTools");
    }

    #[Get(path: '/design/appNoSidebar')]
    public function appNoSidebar()
    {
        return view("UI.Design.appNoSidebar");
    }

    #[Get(path: '/design/appMinimal')]
    public function appMinimal()
    {
        return view("UI.Design.appMinimal");
    }

    #[Get(path: '/design/appFullscreen')]
    public function appFullscreen()
    {
        return view("UI.Design.appFullscreen");
    }

    #[Get(path: '/design/appSplit')]
    public function appSplit()
    {
        return view("UI.Design.appSplit");
    }

    #[Get(path: '/design/appSidebar')]
    public function appSidebar()
    {
        return view("UI.Design.appSidebar");
    }

    #[Get(path: '/design/container')]
    public function container()
    {
        return view("UI.Design.container");
    }

    #[Get(path: '/design/containerWide')]
    public function containerWide()
    {
        return view("UI.Design.containerWide");
    }

    #[Get(path: '/design/containerNarrow')]
    public function containerNarrow()
    {
        return view("UI.Design.containerNarrow");
    }

    #[Get(path: '/design/containerReading')]
    public function containerReading()
    {
        return view("UI.Design.containerReading");
    }

    #[Get(path: '/design/containerFluid')]
    public function containerFluid()
    {
        return view("UI.Design.containerFluid");
    }

    #[Get(path: '/design/containerCompact')]
    public function containerCompact()
    {
        return view("UI.Design.containerCompact");
    }

    #[Get(path: '/design/gridCrudSidebar')]
    public function gridCrudSidebar()
    {
        return view("UI.Design.gridCrudSidebar");
    }

    #[Get(path: '/design/gridCrudSingleColumn')]
    public function gridCrudSingleColumn()
    {
        return view("UI.Design.gridCrudSingleColumn");
    }

    #[Get(path: '/design/gridCrudSplitView')]
    public function gridCrudSplitView()
    {
        return view("UI.Design.gridCrudSplitView");
    }

    #[Get(path: '/design/gridCrudMasterDetail')]
    public function gridCrudMasterDetail()
    {
        return view("UI.Design.gridCrudMasterDetail");
    }

    #[Get(path: '/design/gridCrudThreePanel')]
    public function gridCrudThreePanel()
    {
        return view("UI.Design.gridCrudThreePanel");
    }

    #[Get(path: '/design/gridCard')]
    public function gridCard()
    {
        return view("UI.Design.gridCard");
    }

    #[Get(path: '/design/gridForm')]
    public function gridForm()
    {
        return view("UI.Design.gridForm");
    }

    #[Get(path: '/design/cardType')]
    public function cardType()
    {
        return view("UI.Design.cardType");
    }
}
