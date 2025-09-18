<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractEntityHierarchyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:entity-hierarchy
                           {--language=2 : Language ID (default: 2)}
                           {--output=frames : Output folder in storage/app}
                           {--max-depth=10 : Maximum recursion depth}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract Entity frame and its complete recursive hierarchy from database';

    private int $languageId;
    private array $processedFrames = [];
    private int $totalFramesProcessed = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->languageId = (int) $this->option('language');
        $outputFolder = $this->option('output');
        $maxDepth = (int) $this->option('max-depth');

        $this->info("Extracting complete Entity frame hierarchy for language ID: {$this->languageId}");
        $this->info("Maximum recursion depth: {$maxDepth}");

        try {
            // First, find the Entity frame
            $entityFrame = Criteria::table('view_frame')
                ->where('idLanguage', $this->languageId)
                ->where('name', 'Entity')
                ->first();

            if (!$entityFrame) {
                $this->error("Entity frame not found for language ID {$this->languageId}");
                return 1;
            }

            $this->info("Found Entity frame: ID {$entityFrame->idFrame}, Name: {$entityFrame->name}");

            // Reset processing state
            $this->processedFrames = [];
            $this->totalFramesProcessed = 0;

            // Build recursive hierarchy structure
            $hierarchy = [
                'entity_frame' => [
                    'idFrame' => $entityFrame->idFrame,
                    'name' => $entityFrame->name,
                    'idEntity' => $entityFrame->idEntity,
                    'entry' => $entityFrame->entry ?? null,
                    'description' => $entityFrame->description ?? null
                ],
                'hierarchy' => $this->buildFrameHierarchy($entityFrame->idFrame, 0, $maxDepth),
                'metadata' => [
                    'language_id' => $this->languageId,
                    'extraction_date' => date('Y-m-d H:i:s'),
                    'max_depth' => $maxDepth,
                    'total_frames_processed' => 0,
                    'relation_types' => ['rel_inheritance', 'rel_perspective_on', 'rel_subframe']
                ]
            ];

            $hierarchy['metadata']['total_frames_processed'] = $this->totalFramesProcessed;

            // Create output directory if it doesn't exist
            if (!Storage::exists($outputFolder)) {
                Storage::makeDirectory($outputFolder);
            }

            // Save as JSON
            $filename = "{$outputFolder}/entity_complete_hierarchy_lang_{$this->languageId}.json";
            Storage::put($filename, json_encode($hierarchy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("Complete Entity hierarchy exported to: storage/app/{$filename}");
            $this->info("Total frames processed: {$this->totalFramesProcessed}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Recursively build frame hierarchy
     */
    private function buildFrameHierarchy(int $frameId, int $currentDepth, int $maxDepth): array
    {
        // Prevent infinite recursion and respect max depth
        if ($currentDepth >= $maxDepth || in_array($frameId, $this->processedFrames)) {
            return [];
        }

        // Mark this frame as processed
        $this->processedFrames[] = $frameId;
        $this->totalFramesProcessed++;

        // Get child frames for this frame
        $childFrames = Criteria::table('view_frame_relation')
            ->where('idLanguage', $this->languageId)
            ->where('f1IdFrame', $frameId)
            ->whereIn('relationType', ['rel_inheritance', 'rel_perspective_on', 'rel_subframe'])
            ->orderBy('relationType')
            ->orderBy('f2Name')
            ->all();

        if (empty($childFrames)) {
            return [];
        }

        $hierarchy = [];

        foreach ($childFrames as $child) {
            $childFrameData = [
                'idFrame' => $child->f2IdFrame,
                'name' => $child->f2Name,
                'idEntity' => $child->f2IdEntity,
                'depth' => $currentDepth + 1,
                'relation' => [
                    'idEntityRelation' => $child->idEntityRelation,
                    'relationType' => $child->relationType,
                    'nameCanonical' => $child->nameCanonical ?? null,
                    'nameDirect' => $child->nameDirect ?? null,
                    'nameInverse' => $child->nameInverse ?? null
                ],
                'children' => $this->buildFrameHierarchy($child->f2IdFrame, $currentDepth + 1, $maxDepth)
            ];

            $relationType = $child->relationType;
            if (!isset($hierarchy[$relationType])) {
                $hierarchy[$relationType] = [];
            }

            $hierarchy[$relationType][] = $childFrameData;
        }

        return $hierarchy;
    }
}
