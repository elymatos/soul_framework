<?php

namespace App\Console\Commands\Soul;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Soul\Services\YamlLoaderService;
use Symfony\Component\Console\Helper\Table;

class LoadYamlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'soul:load-yaml 
                            {files?* : Specific YAML files to load}
                            {--directory=* : Load files from specific directories}
                            {--pattern=* : File patterns to match (e.g., "primitive*", "*.yml")}
                            {--dry-run : Preview files without loading}
                            {--force : Force reload of already loaded files}
                            {--strict : Enable strict validation mode}
                            {--permissive : Enable permissive validation mode}
                            {--interactive : Interactive file selection}
                            {--clear-cache : Clear loaded files cache before loading}';

    /**
     * The console command description.
     */
    protected $description = 'Load YAML files into the SOUL Framework database with selective options';

    protected YamlLoaderService $yamlLoader;
    protected string $baseDirectory;
    protected array $foundFiles = [];

    public function __construct(YamlLoaderService $yamlLoader)
    {
        parent::__construct();
        $this->yamlLoader = $yamlLoader;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧠 SOUL Framework - YAML Data Loader');
        $this->info('=====================================');

        // Get base directory
        $this->baseDirectory = config('soul.yaml.base_directory', storage_path('soul/yaml'));
        
        if (!File::exists($this->baseDirectory)) {
            $this->error("❌ YAML directory does not exist: {$this->baseDirectory}");
            $this->info("💡 Create it with: mkdir -p {$this->baseDirectory}");
            return Command::FAILURE;
        }

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            $this->yamlLoader->clearLoadedFilesCache();
            $this->info('🗑️  Cleared loaded files cache');
        }

        try {
            // Determine which files to load
            $filesToLoad = $this->determineFilesToLoad();

            if (empty($filesToLoad)) {
                $this->warn('⚠️  No YAML files found to load');
                return Command::SUCCESS;
            }

            // Show preview in dry-run mode
            if ($this->option('dry-run')) {
                return $this->showDryRun($filesToLoad);
            }

            // Load the files
            return $this->loadFiles($filesToLoad);

        } catch (\Exception $e) {
            $this->error("❌ Command failed: " . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return Command::FAILURE;
        }
    }

    /**
     * Determine which files to load based on arguments and options
     */
    protected function determineFilesToLoad(): array
    {
        $files = [];

        // Check for specific file arguments
        $fileArguments = $this->argument('files') ?? [];
        
        // Check for directory option
        $directories = $this->option('directory') ?? [];
        
        // Check for pattern option
        $patterns = $this->option('pattern') ?? [];

        // Interactive mode (but not in dry-run)
        if ($this->option('interactive') || 
            (!$this->option('dry-run') && empty($fileArguments) && empty($directories) && empty($patterns))) {
            return $this->interactiveFileSelection();
        }

        // Process specific files
        foreach ($fileArguments as $fileArg) {
            $resolvedFiles = $this->resolveFileArgument($fileArg);
            $files = array_merge($files, $resolvedFiles);
        }

        // Process directories
        foreach ($directories as $directory) {
            $dirFiles = $this->getFilesFromDirectory($directory);
            $files = array_merge($files, $dirFiles);
        }

        // Process patterns
        foreach ($patterns as $pattern) {
            $patternFiles = $this->getFilesByPattern($pattern);
            $files = array_merge($files, $patternFiles);
        }

        // If no specific arguments, load all files
        if (empty($files) && empty($fileArguments) && empty($directories) && empty($patterns)) {
            $files = $this->getAllYamlFiles();
        }

        return array_unique($files);
    }

    /**
     * Interactive file selection menu
     */
    protected function interactiveFileSelection(): array
    {
        $allFiles = $this->getAllYamlFiles();
        
        if (empty($allFiles)) {
            $this->warn('⚠️  No YAML files found in directory');
            return [];
        }

        $this->info("\n📁 Found " . count($allFiles) . " YAML files:");
        
        // Display files with indices
        foreach ($allFiles as $index => $file) {
            $relativePath = str_replace($this->baseDirectory . '/', '', $file);
            $fileSize = File::size($file);
            $this->line(sprintf("  [%d] %s (%s)", $index + 1, $relativePath, $this->formatFileSize($fileSize)));
        }

        $this->newLine();
        $this->info('Selection options:');
        $this->info('  • Enter numbers separated by commas (e.g., 1,3,5)');
        $this->info('  • Enter ranges with hyphens (e.g., 1-5)');
        $this->info('  • Enter "all" to load all files');
        $this->info('  • Press Enter to cancel');

        $selection = $this->ask('Which files would you like to load?');

        if (empty($selection)) {
            $this->info('Operation cancelled');
            return [];
        }

        if (strtolower($selection) === 'all') {
            return $allFiles;
        }

        return $this->parseSelection($selection, $allFiles);
    }

    /**
     * Parse user selection and return corresponding files
     */
    protected function parseSelection(string $selection, array $allFiles): array
    {
        $selectedFiles = [];
        $parts = array_map('trim', explode(',', $selection));

        foreach ($parts as $part) {
            if (strpos($part, '-') !== false) {
                // Handle range (e.g., 1-5)
                [$start, $end] = array_map('trim', explode('-', $part, 2));
                $start = (int)$start - 1; // Convert to 0-based index
                $end = (int)$end - 1;

                for ($i = max(0, $start); $i <= min(count($allFiles) - 1, $end); $i++) {
                    $selectedFiles[] = $allFiles[$i];
                }
            } else {
                // Handle single number
                $index = (int)$part - 1; // Convert to 0-based index
                if (isset($allFiles[$index])) {
                    $selectedFiles[] = $allFiles[$index];
                }
            }
        }

        return array_unique($selectedFiles);
    }

    /**
     * Show dry run preview
     */
    protected function showDryRun(array $files): int
    {
        $this->info("🔍 Dry Run - Preview Mode");
        $this->info("Files that would be processed:\n");

        $table = new Table($this->output);
        $table->setHeaders(['File', 'Size', 'Status', 'Validation']);

        foreach ($files as $file) {
            $relativePath = str_replace($this->baseDirectory . '/', '', $file);
            $size = $this->formatFileSize(File::size($file));
            $status = $this->yamlLoader->isFileLoaded($file) ? 
                ($this->option('force') ? 'Will Reload' : 'Already Loaded') : 
                'Will Load';
            
            $validation = $this->getValidationMode();
            
            $table->addRow([$relativePath, $size, $status, $validation]);
        }

        $table->render();

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  • Files to process: " . count($files));
        $this->info("  • Validation mode: " . $this->getValidationMode());
        $this->info("  • Force reload: " . ($this->option('force') ? 'Yes' : 'No'));

        return Command::SUCCESS;
    }

    /**
     * Load the specified files
     */
    protected function loadFiles(array $files): int
    {
        $this->info("🔄 Loading " . count($files) . " YAML files...\n");

        // Set validation mode if specified
        if ($this->option('strict')) {
            config(['soul.yaml.validation_strict' => true]);
        } elseif ($this->option('permissive')) {
            config(['soul.yaml.validation_strict' => false]);
        }

        $results = [
            'loaded_files' => [],
            'errors' => [],
            'concepts_created' => 0,
            'agents_created' => 0,
            'relationships_created' => 0,
            'processing_time' => 0
        ];

        $startTime = microtime(true);
        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->setFormat('verbose');

        foreach ($files as $file) {
            $relativePath = str_replace($this->baseDirectory . '/', '', $file);
            
            if (!$this->option('quiet')) {
                $this->line("\n📄 Loading: {$relativePath}");
            }

            try {
                // Check if already loaded
                if (!$this->option('force') && $this->yamlLoader->isFileLoaded($file)) {
                    $this->comment("  ⏭️  Already loaded, skipping (use --force to reload)");
                    $progressBar->advance();
                    continue;
                }

                $result = $this->yamlLoader->loadYamlFile($file);
                
                // Accumulate results
                $results['loaded_files'][] = $result;
                $results['concepts_created'] += $result['concepts_created'] ?? 0;
                $results['agents_created'] += $result['agents_created'] ?? 0;
                $results['relationships_created'] += $result['relationships_created'] ?? 0;

                if (!$this->option('quiet')) {
                    $this->info("  ✅ Success: {$result['concepts_created']} concepts, {$result['agents_created']} agents");
                }

            } catch (\Exception $e) {
                $error = [
                    'file' => $file,
                    'error' => $e->getMessage()
                ];
                $results['errors'][] = $error;

                if (!$this->option('quiet')) {
                    $this->error("  ❌ Failed: " . $e->getMessage());
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $results['processing_time'] = round((microtime(true) - $startTime) * 1000, 2);

        $this->newLine(2);
        $this->displayResults($results);

        return empty($results['errors']) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Display loading results
     */
    protected function displayResults(array $results): void
    {
        $this->info("📊 Loading Results");
        $this->info("=================");

        // Summary statistics
        $table = new Table($this->output);
        $table->setStyle('box');
        $table->setHeaders(['Metric', 'Count']);
        
        $table->addRows([
            ['Files Loaded', count($results['loaded_files'])],
            ['Concepts Created', $results['concepts_created']],
            ['Agents Created', $results['agents_created']],
            ['Relationships Created', $results['relationships_created']],
            ['Errors', count($results['errors'])],
            ['Processing Time', $results['processing_time'] . ' ms']
        ]);

        $table->render();

        // Show detailed file results if verbose
        if ($this->output->isVerbose() && !empty($results['loaded_files'])) {
            $this->newLine();
            $this->info("📋 Detailed Results:");
            
            $detailTable = new Table($this->output);
            $detailTable->setHeaders(['File', 'Concepts', 'Agents', 'Relationships']);
            
            foreach ($results['loaded_files'] as $result) {
                $filename = basename($result['file']);
                $detailTable->addRow([
                    $filename,
                    $result['concepts_created'] ?? 0,
                    $result['agents_created'] ?? 0,
                    $result['relationships_created'] ?? 0
                ]);
            }
            
            $detailTable->render();
        }

        // Show errors if any
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error("❌ Errors encountered:");
            
            foreach ($results['errors'] as $error) {
                $filename = basename($error['file']);
                $this->error("  • {$filename}: {$error['error']}");
            }
        }

        // Show success message
        if (empty($results['errors'])) {
            $this->newLine();
            $this->info("🎉 All files loaded successfully!");
        } else {
            $this->newLine();
            $this->warn("⚠️  Loading completed with " . count($results['errors']) . " errors");
        }

        // Show cache status
        $cacheStats = $this->yamlLoader->getLoadingStatistics();
        $this->info("\n📈 Cache Status: " . count($cacheStats['loaded_files']) . " files in cache");
    }

    /**
     * Resolve file argument (can be relative or absolute path)
     */
    protected function resolveFileArgument(string $fileArg): array
    {
        // If it's an absolute path, use it directly
        if ($this->isAbsolutePath($fileArg)) {
            return File::exists($fileArg) ? [$fileArg] : [];
        }

        // Try relative to base directory
        $fullPath = $this->baseDirectory . '/' . $fileArg;
        if (File::exists($fullPath)) {
            return [$fullPath];
        }

        // Try as a pattern within base directory
        $matches = glob($this->baseDirectory . '/' . $fileArg);
        return array_filter($matches, fn($file) => is_file($file) && $this->isYamlFile($file));
    }

    /**
     * Get files from a specific directory
     */
    protected function getFilesFromDirectory(string $directory): array
    {
        $fullPath = $this->baseDirectory . '/' . ltrim($directory, '/');
        
        if (!File::isDirectory($fullPath)) {
            $this->warn("Directory not found: {$directory}");
            return [];
        }

        return $this->getYamlFilesFromPath($fullPath);
    }

    /**
     * Get files by pattern
     */
    protected function getFilesByPattern(string $pattern): array
    {
        // Try direct pattern first
        $matches = glob($this->baseDirectory . '/' . $pattern);
        $yamlFiles = array_filter($matches, fn($file) => is_file($file) && $this->isYamlFile($file));
        
        // If no direct matches, try recursive pattern
        if (empty($yamlFiles)) {
            $recursiveMatches = glob($this->baseDirectory . '/**/' . $pattern, GLOB_BRACE);
            $yamlFiles = array_filter($recursiveMatches, fn($file) => is_file($file) && $this->isYamlFile($file));
        }
        
        return $yamlFiles;
    }

    /**
     * Get all YAML files from base directory recursively
     */
    protected function getAllYamlFiles(): array
    {
        return $this->getYamlFilesFromPath($this->baseDirectory);
    }

    /**
     * Get YAML files from a specific path
     */
    protected function getYamlFilesFromPath(string $path): array
    {
        $files = File::allFiles($path);
        $yamlFiles = [];

        foreach ($files as $file) {
            if ($this->isYamlFile($file->getPathname())) {
                $yamlFiles[] = $file->getPathname();
            }
        }

        return $yamlFiles;
    }

    /**
     * Check if file is a YAML file
     */
    protected function isYamlFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, ['yml', 'yaml']);
    }

    /**
     * Get current validation mode
     */
    protected function getValidationMode(): string
    {
        if ($this->option('strict')) {
            return 'Strict';
        } elseif ($this->option('permissive')) {
            return 'Permissive';
        }
        
        return config('soul.yaml.validation_strict', true) ? 'Strict' : 'Permissive';
    }

    /**
     * Check if path is absolute
     */
    protected function isAbsolutePath(string $path): bool
    {
        // Unix/Linux absolute paths start with /
        if (str_starts_with($path, '/')) {
            return true;
        }
        
        // Windows absolute paths (C:\, D:\, etc.)
        if (preg_match('/^[A-Z]:\\\\/', $path)) {
            return true;
        }
        
        return false;
    }

    /**
     * Format file size in human-readable format
     */
    protected function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 1) . ' ' . $units[$unitIndex];
    }
}