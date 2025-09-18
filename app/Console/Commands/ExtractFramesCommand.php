<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExtractFramesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:frames
                           {--language=1 : Language ID (default: 1)}
                           {--output=frames : Output folder in storage/app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract frames from database and save as JSON';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $languageId = (int) $this->option('language');
        $outputFolder = $this->option('output');

        $this->info("Extracting frames for language ID: {$languageId}");

        try {
            // Query frames from view_frame
            $frames = Criteria::table('view_frame')
                ->where('idLanguage', $languageId)
                ->orderBy('entry')
                ->all();

            $this->info("Found " . count($frames) . " frames");

            if (empty($frames)) {
                $this->warn("No frames found for language ID {$languageId}");
                return 0;
            }

            // Create output directory if it doesn't exist
            if (!Storage::exists($outputFolder)) {
                Storage::makeDirectory($outputFolder);
            }

            // Convert to array for JSON serialization
            $framesArray = array_map(function($frame) {
                return (array) $frame;
            }, $frames);

            // Save as JSON
            $filename = "{$outputFolder}/frames_lang_{$languageId}.json";
            Storage::put($filename, json_encode($framesArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("Frames exported to: storage/app/{$filename}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            return 1;
        }
    }
}
