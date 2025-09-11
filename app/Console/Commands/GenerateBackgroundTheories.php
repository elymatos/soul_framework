<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Laravel command to generate Background Theory classes from JSON axiom files
 * 
 * Processes JSON files containing FOL axioms and generates the complete
 * infrastructure including entities, predicates, and axiom executors.
 */
class GenerateBackgroundTheories extends Command
{
    /**
     * The name and signature of the console command
     */
    protected $signature = 'background:generate 
                           {file : Path to JSON file or directory containing JSON files}
                           {--force : Overwrite existing files}
                           {--dry-run : Show what would be generated without creating files}
                           {--entities-only : Generate only entity classes}
                           {--predicates-only : Generate only predicate classes}
                           {--axioms-only : Generate only axiom executor classes}';

    /**
     * The console command description
     */
    protected $description = 'Generate Background Theory classes from JSON axiom files';

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (!File::exists($filePath)) {
            $this->error("File or directory does not exist: {$filePath}");
            return self::FAILURE;
        }

        $jsonFiles = $this->collectJsonFiles($filePath);
        
        if (empty($jsonFiles)) {
            $this->error("No JSON files found in: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Found " . count($jsonFiles) . " JSON file(s) to process");

        $allData = $this->parseJsonFiles($jsonFiles);
        
        if (empty($allData)) {
            $this->error("No valid axiom data found in JSON files");
            return self::FAILURE;
        }

        $this->info("Processing " . count($allData) . " axiom definition(s)");

        if ($isDryRun) {
            return $this->showDryRun($allData);
        }

        return $this->generateClasses($allData, $force);
    }

    /**
     * Collect JSON files from path
     */
    private function collectJsonFiles(string $path): array
    {
        if (File::isFile($path) && str_ends_with($path, '.json')) {
            return [$path];
        }

        if (File::isDirectory($path)) {
            return File::glob($path . '/*.json');
        }

        return [];
    }

    /**
     * Parse JSON files and extract axiom data
     */
    private function parseJsonFiles(array $files): array
    {
        $allData = [];

        foreach ($files as $file) {
            try {
                $content = File::get($file);
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->warn("Invalid JSON in file: {$file} - " . json_last_error_msg());
                    continue;
                }

                if (isset($data['axioms']) && is_array($data['axioms'])) {
                    $chapterData = [
                        'file' => $file,
                        'metadata' => $data['metadata'] ?? [],
                        'axioms' => $data['axioms'],
                        'entities' => $this->extractEntitiesFromAxioms($data['axioms']),
                        'predicates' => $this->extractPredicatesFromAxioms($data['axioms']),
                    ];
                    
                    $allData[] = $chapterData;
                }

            } catch (\Exception $e) {
                $this->warn("Error processing file {$file}: " . $e->getMessage());
            }
        }

        return $allData;
    }

    /**
     * Extract entity types from axioms
     */
    private function extractEntitiesFromAxioms(array $axioms): array
    {
        $entities = [];
        
        foreach ($axioms as $axiom) {
            $predicates = $axiom['predicates'] ?? [];
            
            // Identify entity types from common patterns
            foreach ($predicates as $predicate) {
                if (in_array($predicate, ['eventuality', 'set', 'composite', 'function', 'sequence', 'pair'])) {
                    if (!in_array($predicate, $entities)) {
                        $entities[] = $predicate;
                    }
                }
            }
        }

        return $entities;
    }

    /**
     * Extract predicate types from axioms
     */
    private function extractPredicatesFromAxioms(array $axioms): array
    {
        $predicates = [];
        
        foreach ($axioms as $axiom) {
            $axiomPredicates = $axiom['predicates'] ?? [];
            
            foreach ($axiomPredicates as $predicate) {
                if (!isset($predicates[$predicate])) {
                    $predicates[$predicate] = [
                        'name' => $predicate,
                        'usage_count' => 0,
                        'axioms' => []
                    ];
                }
                
                $predicates[$predicate]['usage_count']++;
                $predicates[$predicate]['axioms'][] = $axiom['id'];
            }
        }

        return array_values($predicates);
    }

    /**
     * Show dry run results
     */
    private function showDryRun(array $allData): int
    {
        $this->info("\n=== DRY RUN - What would be generated ===\n");

        $totalEntities = 0;
        $totalPredicates = 0;
        $totalAxioms = 0;

        foreach ($allData as $chapterData) {
            $chapter = $chapterData['metadata']['chapter'] ?? 'Unknown';
            $title = $chapterData['metadata']['chapter_title'] ?? 'Unknown Chapter';
            
            $this->info("Chapter {$chapter}: {$title}");
            
            $entities = $chapterData['entities'];
            $predicates = $chapterData['predicates'];
            $axioms = $chapterData['axioms'];
            
            $this->line("  Entities: " . count($entities) . " (" . implode(', ', $entities) . ")");
            $this->line("  Predicates: " . count($predicates));
            $this->line("  Axioms: " . count($axioms));
            
            foreach ($predicates as $predicate) {
                $this->line("    - {$predicate['name']} (used {$predicate['usage_count']} times)");
            }
            
            $this->line("");
            
            $totalEntities += count($entities);
            $totalPredicates += count($predicates);
            $totalAxioms += count($axioms);
        }

        $this->info("=== Summary ===");
        $this->info("Total Entity Classes: {$totalEntities}");
        $this->info("Total Predicate Classes: {$totalPredicates}");
        $this->info("Total Axiom Executor Classes: {$totalAxioms}");

        $this->info("\nFiles that would be created:");
        $this->line("- app/Domain/BackgroundTheories/Entities/ ({$totalEntities} files)");
        $this->line("- app/Domain/BackgroundTheories/Predicates/ ({$totalPredicates} files)");
        $this->line("- app/Domain/BackgroundTheories/AxiomExecutors/ ({$totalAxioms} files)");

        return self::SUCCESS;
    }

    /**
     * Generate all classes
     */
    private function generateClasses(array $allData, bool $force): int
    {
        $entitiesOnly = $this->option('entities-only');
        $predicatesOnly = $this->option('predicates-only');
        $axiomsOnly = $this->option('axioms-only');

        $generateAll = !$entitiesOnly && !$predicatesOnly && !$axiomsOnly;

        $progress = $this->output->createProgressBar(
            array_sum(array_map(function($chapter) use ($generateAll, $entitiesOnly, $predicatesOnly, $axiomsOnly) {
                $count = 0;
                if ($generateAll || $entitiesOnly) $count += count($chapter['entities']);
                if ($generateAll || $predicatesOnly) $count += count($chapter['predicates']);
                if ($generateAll || $axiomsOnly) $count += count($chapter['axioms']);
                return $count;
            }, $allData))
        );

        $progress->start();

        foreach ($allData as $chapterData) {
            if ($generateAll || $entitiesOnly) {
                $this->generateEntityClasses($chapterData['entities'], $force, $progress);
            }
            
            if ($generateAll || $predicatesOnly) {
                $this->generatePredicateClasses($chapterData['predicates'], $force, $progress);
            }
            
            if ($generateAll || $axiomsOnly) {
                $this->generateAxiomExecutorClasses($chapterData['axioms'], $force, $progress);
            }
        }

        $progress->finish();
        
        $this->info("\n\nGeneration completed successfully!");
        $this->info("Remember to run: composer dump-autoload");
        
        return self::SUCCESS;
    }

    /**
     * Generate entity classes
     */
    private function generateEntityClasses(array $entities, bool $force, $progress): void
    {
        $entitiesDir = app_path('Domain/BackgroundTheories/Entities');
        
        if (!File::exists($entitiesDir)) {
            File::makeDirectory($entitiesDir, 0755, true);
        }

        foreach ($entities as $entityType) {
            $className = Str::studly($entityType) . 'Entity';
            $filePath = $entitiesDir . '/' . $className . '.php';
            
            if (File::exists($filePath) && !$force) {
                $progress->advance();
                continue;
            }
            
            $content = $this->generateEntityClassContent($className, $entityType);
            File::put($filePath, $content);
            
            $progress->advance();
        }
    }

    /**
     * Generate predicate classes
     */
    private function generatePredicateClasses(array $predicates, bool $force, $progress): void
    {
        $predicatesDir = app_path('Domain/BackgroundTheories/Predicates');
        
        if (!File::exists($predicatesDir)) {
            File::makeDirectory($predicatesDir, 0755, true);
        }

        foreach ($predicates as $predicateData) {
            $predicateName = $predicateData['name'];
            $className = Str::studly($predicateName) . 'Predicate';
            $filePath = $predicatesDir . '/' . $className . '.php';
            
            if (File::exists($filePath) && !$force) {
                $progress->advance();
                continue;
            }
            
            $content = $this->generatePredicateClassContent($className, $predicateData);
            File::put($filePath, $content);
            
            $progress->advance();
        }
    }

    /**
     * Generate axiom executor classes
     */
    private function generateAxiomExecutorClasses(array $axioms, bool $force, $progress): void
    {
        $axiomsDir = app_path('Domain/BackgroundTheories/AxiomExecutors');
        
        if (!File::exists($axiomsDir)) {
            File::makeDirectory($axiomsDir, 0755, true);
        }

        foreach ($axioms as $axiom) {
            $axiomId = str_replace('.', '_', $axiom['id']);
            $className = 'Axiom' . $axiomId . 'Executor';
            $filePath = $axiomsDir . '/' . $className . '.php';
            
            if (File::exists($filePath) && !$force) {
                $progress->advance();
                continue;
            }
            
            $content = $this->generateAxiomExecutorClassContent($className, $axiom);
            File::put($filePath, $content);
            
            $progress->advance();
        }
    }

    /**
     * Generate entity class content
     */
    private function generateEntityClassContent(string $className, string $entityType): string
    {
        return <<<PHP
<?php

namespace App\Domain\BackgroundTheories\Entities;

use App\Domain\BackgroundTheories\BackgroundEntity;

/**
 * {$className} - {$entityType} entity from Background Theories
 * 
 * Generated automatically from JSON axiom definitions.
 */
class {$className} extends BackgroundEntity
{
    public function __construct(array \$attributes = [])
    {
        parent::__construct('{$entityType}', \$attributes);
    }

    public function validate(): bool
    {
        // Add validation logic specific to {$entityType}
        return true;
    }

    public function describe(): string
    {
        return "{$entityType} entity: " . \$this->getId();
    }
}

PHP;
    }

    /**
     * Generate predicate class content
     */
    private function generatePredicateClassContent(string $className, array $predicateData): string
    {
        $predicateName = $predicateData['name'];
        $usageCount = $predicateData['usage_count'];
        $axiomsList = implode(', ', $predicateData['axioms']);

        return <<<PHP
<?php

namespace App\Domain\BackgroundTheories\Predicates;

use App\Domain\BackgroundTheories\BackgroundPredicate;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;

/**
 * {$className} - {$predicateName} predicate from Background Theories
 * 
 * Used in axioms: {$axiomsList}
 * Total usage: {$usageCount} times
 * 
 * Generated automatically from JSON axiom definitions.
 */
class {$className} extends BackgroundPredicate
{
    public function __construct(array \$arguments = [])
    {
        parent::__construct('{$predicateName}', \$arguments);
    }

    public function evaluate(BackgroundReasoningContext \$context): bool
    {
        // Add evaluation logic specific to {$predicateName}
        // This is where the FOL semantics get implemented
        return true;
    }

    public function toFOL(): string
    {
        \$args = implode(' ', array_map(function(\$arg) {
            return is_string(\$arg) ? \$arg : 'entity_' . \$arg;
        }, \$this->arguments));
        
        return "({$predicateName} {\$args})";
    }
}

PHP;
    }

    /**
     * Generate axiom executor class content
     */
    private function generateAxiomExecutorClassContent(string $className, array $axiom): string
    {
        $axiomId = $axiom['id'];
        $description = addslashes($axiom['english'] ?? $axiom['title'] ?? 'No description');
        $folFormula = addslashes($axiom['fol'] ?? '');
        $complexity = $axiom['complexity'] ?? 'unknown';
        $predicatesUsed = json_encode($axiom['predicates'] ?? []);

        return <<<PHP
<?php

namespace App\Domain\BackgroundTheories\AxiomExecutors;

use App\Domain\BackgroundTheories\BackgroundAxiomExecutor;
use App\Domain\BackgroundTheories\BackgroundReasoningContext;
use Illuminate\Support\Collection;

/**
 * {$className} - Axiom {$axiomId} executor
 * 
 * Description: {$description}
 * FOL Formula: {$folFormula}
 * Complexity: {$complexity}
 * 
 * Generated automatically from JSON axiom definitions.
 */
class {$className} extends BackgroundAxiomExecutor
{
    public function __construct()
    {
        parent::__construct(
            '{$axiomId}',
            '{$description}',
            {$predicatesUsed},
            '{$folFormula}',
            '{$complexity}'
        );
    }

    public function execute(BackgroundReasoningContext \$context): Collection
    {
        \$results = collect();
        \$trace = [];

        \$trace[] = \$this->trace('axiom_execution_start', [
            'axiom_id' => '{$axiomId}',
            'description' => '{$description}'
        ]);

        // TODO: Implement the actual axiom logic here
        // This is where the FOL axiom gets converted to imperative code
        
        // Example structure:
        // 1. Get relevant entities from context
        // 2. Apply the axiom rules
        // 3. Create new predicates/entities as needed
        // 4. Add them to results

        \$trace[] = \$this->trace('axiom_execution_complete', [
            'results_count' => \$results->count()
        ]);

        return \$results;
    }
}

PHP;
    }
}