<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExtractFrameElementsToGraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:frame-elements-to-graph
                           {frame : Name of the top frame to extract hierarchy from}
                           {--language=2 : Language ID (default: 2)}
                           {--output=graphs : Output folder in storage/app}
                           {--filename= : Custom filename (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract core frame elements from frame hierarchy to graph editor JSON format';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $frameName = $this->argument('frame');
        $languageId = (int) $this->option('language');
        $outputFolder = $this->option('output');
        $customFilename = $this->option('filename');

        $this->info("Extracting core frame elements for '{$frameName}' frame hierarchy (language ID: {$languageId})");

        try {
            // Get all frames from the specified frame hierarchy
            $allFrameIds = $this->getAllFrameHierarchyFrames($frameName, $languageId);

            if (empty($allFrameIds)) {
                $this->warn("No frames found in '{$frameName}' hierarchy for language ID {$languageId}");

                return 0;
            }

            $this->info('Found '.count($allFrameIds)." frames in '{$frameName}' hierarchy");

            $graphNodes = [];
            $graphEdges = [];
            $progressBar = $this->output->createProgressBar(count($allFrameIds));
            $progressBar->start();

            foreach ($allFrameIds as $frameId) {
                $frameElementData = $this->getFrameElementGraphData($frameId, $languageId);
                if (! empty($frameElementData['nodes'])) {
                    $graphNodes = array_merge($graphNodes, $frameElementData['nodes']);
                    $graphEdges = array_merge($graphEdges, $frameElementData['edges']);
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Create output directory if it doesn't exist
            if (! Storage::exists($outputFolder)) {
                Storage::makeDirectory($outputFolder);
            }

            // Generate filename
            $filename = $customFilename
                ? "{$outputFolder}/{$customFilename}.json"
                : "{$outputFolder}/".strtolower(str_replace(' ', '_', $frameName))."_frame_elements_graph_lang_{$languageId}.json";

            // Create graph data structure
            $graphData = [
                'nodes' => $graphNodes,
                'edges' => $graphEdges,
                'metadata' => [
                    'created' => now()->toISOString(),
                    'modified' => now()->toISOString(),
                    'version' => '1.0',
                    'source' => 'frame_elements_hierarchy_extraction',
                    'language_id' => $languageId,
                    'root_frame' => $frameName,
                    'core_types' => ['cty_core', 'cty_core-unexpressed'],
                    'includes_edges' => true,
                    'edge_type' => 'f-slot',
                ],
            ];

            // Save as JSON
            Storage::put($filename, json_encode($graphData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("'{$frameName}' frame elements exported to: storage/app/{$filename}");
            $this->info('Total frame element nodes: '.count($graphNodes));
            $this->info('Total f-slot edges: '.count($graphEdges));

            return 0;

        } catch (\Exception $e) {
            $this->error('Export failed: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get all frame IDs in the specified frame hierarchy
     */
    private function getAllFrameHierarchyFrames(string $frameName, int $languageId): array
    {
        // First, find the top frame
        $topFrame = Criteria::table('view_frame')
            ->where('idLanguage', $languageId)
            ->where('name', $frameName)
            ->first();

        if (! $topFrame) {
            $this->error("Frame '{$frameName}' not found for language ID {$languageId}");

            return [];
        }

        $this->info("Found top frame: ID {$topFrame->idFrame}, Name: {$topFrame->name}");

        $allFrameIds = [$topFrame->idFrame];
        $processedFrames = [];
        $framesToProcess = [$topFrame->idFrame];

        // Recursively get all descendant frames
        while (! empty($framesToProcess)) {
            $currentFrameId = array_shift($framesToProcess);

            if (in_array($currentFrameId, $processedFrames)) {
                continue;
            }

            $processedFrames[] = $currentFrameId;

            // Get child frames
            $childFrames = Criteria::table('view_frame_relation')
                ->where('idLanguage', $languageId)
                ->where('f1IdFrame', $currentFrameId)
                ->whereIn('relationType', ['rel_inheritance', 'rel_perspective_on', 'rel_subframe'])
                ->get(['f2IdFrame'])
                ->all();

            foreach ($childFrames as $child) {
                if (! in_array($child->f2IdFrame, $allFrameIds)) {
                    $allFrameIds[] = $child->f2IdFrame;
                    $framesToProcess[] = $child->f2IdFrame;
                }
            }
        }

        return $allFrameIds;
    }

    /**
     * Get frame element data in graph editor format (nodes and edges) for a specific frame
     */
    private function getFrameElementGraphData(int $frameId, int $languageId): array
    {
        // Get core frame elements for this frame
        $frameElements = Criteria::table('view_frameelement')
            ->where('idLanguage', $languageId)
            ->where('idFrame', $frameId)
            ->whereIn('coreType', ['cty_core', 'cty_core-unexpressed'])
            ->orderBy('name')
            ->all();

        $nodes = [];
        $edges = [];

        foreach ($frameElements as $fe) {
            // Create frame element node
            $nodes[] = [
                'id' => $fe->idEntity,
                'label' => $fe->name,
                'type' => 'slot',
            ];

            // Create edge from frame element to frame
            $edges[] = [
                'id' => Str::uuid()->toString(),
                'from' => $fe->idEntity,
                'to' => $fe->frameIdEntity,
                'label' => 'f-slot',
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }
}
