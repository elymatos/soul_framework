<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractFramesToGraphCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:frames-to-graph
                           {frame : Name of the top frame to extract hierarchy from}
                           {--language=2 : Language ID (default: 2)}
                           {--output=graphs : Output folder in storage/app}
                           {--filename= : Custom filename (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract frame hierarchy to graph editor JSON format with nodes only';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $frameName = $this->argument('frame');
        $languageId = (int) $this->option('language');
        $outputFolder = $this->option('output');
        $customFilename = $this->option('filename');

        $this->info("Extracting '{$frameName}' frame hierarchy for language ID: {$languageId}");

        try {
            // Get all frames from the specified frame hierarchy
            $allFrameIds = $this->getAllFrameHierarchyFrames($frameName, $languageId);

            if (empty($allFrameIds)) {
                $this->warn("No frames found in '{$frameName}' hierarchy for language ID {$languageId}");

                return 0;
            }

            $this->info('Found '.count($allFrameIds)." frames in '{$frameName}' hierarchy");

            $graphNodes = [];
            $progressBar = $this->output->createProgressBar(count($allFrameIds));
            $progressBar->start();

            foreach ($allFrameIds as $frameId) {
                $frameData = $this->getFrameGraphNode($frameId, $languageId);
                if ($frameData) {
                    $graphNodes[] = $frameData;
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
                : "{$outputFolder}/".strtolower(str_replace(' ', '_', $frameName))."_frames_graph_lang_{$languageId}.json";

            // Create graph data structure
            $graphData = [
                'nodes' => $graphNodes,
                'edges' => [],
                'metadata' => [
                    'created' => now()->toISOString(),
                    'modified' => now()->toISOString(),
                    'version' => '1.0',
                    'source' => 'frame_hierarchy_extraction',
                    'language_id' => $languageId,
                    'root_frame' => $frameName,
                ],
            ];

            // Save as JSON
            Storage::put($filename, json_encode($graphData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("'{$frameName}' frame hierarchy exported to: storage/app/{$filename}");
            $this->info('Total frame nodes: '.count($graphNodes));

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
     * Get frame data in graph editor node format
     */
    private function getFrameGraphNode(int $frameId, int $languageId): ?array
    {
        // Get frame information
        $frame = Criteria::table('view_frame')
            ->where('idLanguage', $languageId)
            ->where('idFrame', $frameId)
            ->first();

        if (! $frame) {
            return null;
        }

        return [
            'id' => $frame->idEntity,
            'label' => $frame->name,
            'type' => 'frame',
        ];
    }
}
