<?php

namespace App\Http\Controllers\SOUL;

use App\Data\SOUL\BrowseConceptData;
use App\Http\Controllers\Controller;
use App\Services\SOUL\BrowseService;
use App\Services\SOUL\ConceptService;
use App\Services\SOUL\GraphService;
use Collective\Annotations\Routing\Attributes\Attributes\Get;
use Collective\Annotations\Routing\Attributes\Attributes\Middleware;
use Collective\Annotations\Routing\Attributes\Attributes\Post;

#[Middleware(name: 'web')]
class BrowseController extends Controller
{
    public function __construct(
        private BrowseService $browseService,
        private ConceptService $conceptService,
        private GraphService $graphService
    ) {}

    #[Get(path: '/soul/browse/script/{file}')]
    public function scripts(string $file)
    {
        return response()
            ->view("SOUL.Browse.{$file}")
            ->header('Content-type', 'text/javascript');
    }

    #[Get(path: '/soul/browse')]
    public function main()
    {
        return view('SOUL.Browse.main', [
            'concepts' => [],
        ]);
    }

    #[Post(path: '/soul/browse/grid')]
    public function grid(BrowseConceptData $search)
    {
        $concepts = $this->browseService->browseConcepts($search);

        return view('SOUL.Browse.main', [
            'search' => $search,
            'concepts' => $concepts,
        ])->fragment('search');
    }

    #[Get(path: '/soul/browse/{conceptName}')]
    public function concept(string $conceptName)
    {
        try {
            $data = $this->browseService->getConceptDetails($conceptName);

            return view('SOUL.Browse.concept', $data);
        } catch (\Exception $e) {
            return redirect('/soul/browse')
                ->with('error', "Concept '{$conceptName}' not found: ".$e->getMessage());
        }
    }

    #[Get(path: '/soul/browse/{conceptName}/graph')]
    public function conceptGraph(string $conceptName)
    {
        try {
            $graphData = $this->graphService->getConceptGraphVisualization($conceptName, 2);

            return response()->json($graphData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Get(path: '/soul/browse/{conceptName}/activation')]
    public function spreadingActivation(string $conceptName)
    {
        try {
            $activationData = $this->graphService->performSpreadingActivation($conceptName, 0.5, 3, 20);

            return view('SOUL.Browse.activation', [
                'conceptName' => $conceptName,
                'activationData' => $activationData,
            ]);
        } catch (\Exception $e) {
            return view('SOUL.Browse.activation', [
                'conceptName' => $conceptName,
                'activationData' => ['activatedConcepts' => []],
                'error' => $e->getMessage(),
            ]);
        }
    }

    #[Get(path: '/soul/browse/initialize')]
    public function initialize()
    {
        try {
            $initialized = $this->conceptService->initializeSoulPrimitives();

            return redirect('/soul/browse')
                ->with('success', 'SOUL primitives initialized: '.implode(', ', $initialized));
        } catch (\Exception $e) {
            return redirect('/soul/browse')
                ->with('error', 'Failed to initialize SOUL primitives: '.$e->getMessage());
        }
    }
}
