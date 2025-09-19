<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractEntityFramesSimplifiedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:entity-simplified
                           {--language=2 : Language ID (default: 2)}
                           {--output=frames : Output folder in storage/app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract simplified Entity hierarchy with core frame elements';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $languageId = (int) $this->option('language');
        $outputFolder = $this->option('output');

        $this->info("Extracting simplified Entity hierarchy for language ID: {$languageId}");

        try {
            // Get all frames from the Entity hierarchy
            $allFrameIds = $this->getAllEntityHierarchyFrames($languageId);

            if (empty($allFrameIds)) {
                $this->warn("No frames found in Entity hierarchy for language ID {$languageId}");

                return 0;
            }

            $this->info('Found '.count($allFrameIds).' frames in Entity hierarchy');

            $simplifiedFrames = [];
            $progressBar = $this->output->createProgressBar(count($allFrameIds));
            $progressBar->start();

            foreach ($allFrameIds as $frameId) {
                $frameData = $this->getSimplifiedFrameData($frameId, $languageId);
                if ($frameData) {
                    $simplifiedFrames[] = $frameData;
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            // Create output directory if it doesn't exist
            if (! Storage::exists($outputFolder)) {
                Storage::makeDirectory($outputFolder);
            }

            // Save as JSON
            $filename = "{$outputFolder}/entity_simplified_lang_{$languageId}.json";
            Storage::put($filename, json_encode($simplifiedFrames, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("Simplified Entity hierarchy exported to: storage/app/{$filename}");
            $this->info('Total frames: '.count($simplifiedFrames));

            // Calculate statistics
            $totalFEs = array_sum(array_map(fn ($frame) => count($frame['fes']), $simplifiedFrames));
            $this->info("Total core frame elements: {$totalFEs}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Export failed: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get all frame IDs in the Entity hierarchy
     */
    private function getAllEntityHierarchyFrames(int $languageId): array
    {
        // First, find the Entity frame
        $entityFrame = Criteria::table('view_frame')
            ->where('idLanguage', $languageId)
            ->where('name', 'Entity')
            ->first();

        if (! $entityFrame) {
            return [];
        }

        $allFrameIds = [$entityFrame->idFrame];
        $processedFrames = [];
        $framesToProcess = [$entityFrame->idFrame];

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
     * Get simplified frame data with core frame elements and their relations
     */
    private function getSimplifiedFrameData(int $frameId, int $languageId): ?array
    {
        // Get frame information
        $frame = Criteria::table('view_frame')
            ->where('idLanguage', $languageId)
            ->where('idFrame', $frameId)
            ->first();

        if (! $frame) {
            return null;
        }

        // Get core frame elements for this frame
        $frameElements = Criteria::table('view_frameelement')
            ->where('idLanguage', $languageId)
            ->where('idFrame', $frameId)
            ->whereIn('coreType', ['cty_core', 'cty_core-unexpressed'])
            ->orderBy('name')
            ->all();

        $fes = [];
        foreach ($frameElements as $fe) {
            // Get relations for this frame element
            $relations = $this->getFrameElementRelations($fe->idEntity, $languageId);

            $fes[] = [
                'name' => $fe->name,
                'idEntity' => $fe->idEntity,
                'definition' => $fe->description ?? null,
                'relations' => $relations,
            ];
        }

        return [
            'frame' => $frame->name,
            'idEntity' => $frame->idEntity,
            'definition' => $frame->description ?? null,
            'fes' => $fes,
        ];
    }

    /**
     * Get relations for a specific frame element
     */
    private function getFrameElementRelations(int $feIdEntity, int $languageId): array
    {
        // Get outgoing relations from this frame element
        $relations = Criteria::table('view_fe_relation')
            ->where('idLanguage', $languageId)
            ->where('fe1IdEntity', $feIdEntity)
            ->whereIn('relationType', ['rel_inheritance', 'rel_perspective_on', 'rel_subframe'])
            ->orderBy('relationType')
            ->orderBy('fe2Name')
            ->all();

        $relationData = [];
        foreach ($relations as $relation) {
            $relationData[] = [
                'relationType' => $relation->relationType,
                'idEntity' => $relation->fe2IdEntity,
            ];
        }

        return $relationData;
    }
}
