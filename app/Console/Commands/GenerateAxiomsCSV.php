<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateAxiomsCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:axioms-csv
                            {--output=docs/csp/axioms.csv : Output file path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CSV file with axioms from JSON files in docs/csp/json_01';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting axioms CSV generation...');

        // Define paths
        $jsonDirectory = base_path('docs/csp/json_01');
        $outputFile = base_path($this->option('output'));

        // Check if source directory exists
        if (! File::exists($jsonDirectory)) {
            $this->error("Source directory does not exist: {$jsonDirectory}");

            return 1;
        }

        // Ensure output directory exists
        $outputDir = dirname($outputFile);
        if (! File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
            $this->info("Created output directory: {$outputDir}");
        }

        // Get all JSON files
        $jsonFiles = File::glob($jsonDirectory.'/*.json');

        if (empty($jsonFiles)) {
            $this->error("No JSON files found in: {$jsonDirectory}");

            return 1;
        }

        $this->info('Found '.count($jsonFiles).' JSON files to process');

        // Prepare CSV data
        $csvData = [];
        $csvData[] = ['chapter', 'section', 'axiom_number', 'fol', 'english']; // Header

        $totalAxioms = 0;

        // Process each JSON file
        foreach ($jsonFiles as $jsonFile) {
            $filename = basename($jsonFile);
            $this->line("Processing: {$filename}");

            try {
                $content = File::get($jsonFile);
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("Invalid JSON in file: {$filename}");

                    continue;
                }

                // Extract axioms
                if (isset($data['axioms']) && is_array($data['axioms'])) {
                    foreach ($data['axioms'] as $axiom) {
                        $csvData[] = [
                            $axiom['chapter'] ?? '',
                            $axiom['section'] ?? '',
                            $axiom['axiom_number'] ?? '',
                            $axiom['fol'] ?? '',
                            $axiom['english'] ?? '',
                        ];
                        $totalAxioms++;
                    }

                    $axiomCount = count($data['axioms']);
                    $this->line("  → Extracted {$axiomCount} axioms");
                } else {
                    $this->warn("  → No axioms found in {$filename}");
                }

            } catch (\Exception $e) {
                $this->error("Error processing {$filename}: ".$e->getMessage());

                continue;
            }
        }

        // Write CSV file
        $this->info("Writing CSV file to: {$outputFile}");

        try {
            $csvContent = $this->arrayToCsv($csvData);
            File::put($outputFile, $csvContent);

            $this->info('✅ CSV file generated successfully!');
            $this->info("📊 Total axioms extracted: {$totalAxioms}");
            $this->info("📁 Output file: {$outputFile}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error writing CSV file: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Convert array data to CSV format
     */
    private function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
