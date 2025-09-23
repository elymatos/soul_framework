<?php

namespace App\Console\Commands;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use App\Repositories\CorticalColumnRepository;
use App\Services\CorticalNetwork\DatabaseService;
use App\Services\CorticalNetwork\NetworkService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*

 Usage Examples

  # List all networks
  php artisan cortical:manage list:networks

  # Clean test data
  php artisan cortical:manage clean:test

  # Export network to JSON
  php artisan cortical:manage export --network=2 --file=network.json

  # Create snapshot
  php artisan cortical:manage snapshot:create --network=2 --name="baseline"

  # Sync metadata
  php artisan cortical:manage sync:metadata

*/
class CorticalNetworkManageCommand extends Command
{
    protected $signature = 'cortical:manage
                            {operation : Operation to perform}
                            {--network= : Network ID}
                            {--name= : Snapshot or network name}
                            {--file= : File path for import/export}
                            {--format=json : Export format (json, cypher, gexf)}
                            {--status= : Filter networks by status}
                            {--force : Force operation without confirmation}
                            {--json : Output as JSON}';

    protected $description = 'Manage cortical networks: snapshots, exports, imports, sync, and cleanup';

    private DatabaseService $databaseService;

    private NetworkService $networkService;

    private CorticalColumnRepository $columnRepo;

    public function __construct(
        DatabaseService $databaseService,
        NetworkService $networkService,
        CorticalColumnRepository $columnRepo
    ) {
        parent::__construct();
        $this->databaseService = $databaseService;
        $this->networkService = $networkService;
        $this->columnRepo = $columnRepo;
    }

    public function handle(): int
    {
        $operation = $this->argument('operation');

        try {
            return match ($operation) {
                'snapshot:create' => $this->createSnapshot(),
                'snapshot:load' => $this->loadSnapshot(),
                'snapshot:list' => $this->listSnapshots(),
                'sync:metadata' => $this->syncMetadata(),
                'export' => $this->exportNetwork(),
                'import' => $this->importNetwork(),
                'clean:all' => $this->cleanAll(),
                'clean:test' => $this->cleanTest(),
                'validate' => $this->validate(),
                'list:networks' => $this->listNetworks(),
                default => $this->showHelp(),
            };
        } catch (Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");

            return 1;
        }
    }

    private function createSnapshot(): int
    {
        $networkId = $this->option('network');
        $name = $this->option('name');

        if (! $networkId || ! $name) {
            $this->error('Missing required options: --network and --name');

            return 1;
        }

        $this->info("Creating snapshot '{$name}' for network {$networkId}...");

        $snapshotId = $this->databaseService->createNetworkSnapshot($name, (int) $networkId, 'manual');

        $snapshot = Criteria::table('network_snapshots')->where('id', $snapshotId)->first();

        $this->newLine();
        $this->info('✅ Snapshot created successfully!');
        $this->table(
            ['ID', 'Name', 'Network ID', 'Size (bytes)', 'Checksum'],
            [[$snapshot->id, $snapshot->name, $snapshot->cortical_network_id, $snapshot->file_size, substr($snapshot->checksum, 0, 12).'...']]
        );

        return 0;
    }

    private function loadSnapshot(): int
    {
        $networkId = $this->option('network');
        $name = $this->option('name');

        if (! $networkId || ! $name) {
            $this->error('Missing required options: --network and --name');

            return 1;
        }

        if (! $this->option('force')) {
            if (! $this->confirm("⚠️  This will replace all data in network {$networkId}. Continue?")) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info("Loading snapshot '{$name}' into network {$networkId}...");

        $result = $this->databaseService->loadNetworkSnapshot($name, (int) $networkId);

        $this->newLine();
        $this->info('✅ Snapshot loaded successfully!');
        $this->line("Neurons restored: {$result['network_data']['neurons_imported']}");
        $this->line("Connections restored: {$result['network_data']['relationships_imported']}");
        $this->line("Restored at: {$result['restored_at']}");

        return 0;
    }

    private function listSnapshots(): int
    {
        $networkId = $this->option('network');

        $query = Criteria::table('network_snapshots')
            ->orderBy('created_at', 'desc');

        if ($networkId) {
            $query->where('cortical_network_id', $networkId);
        }

        $snapshots = $query->get();

        if ($snapshots->isEmpty()) {
            $this->info('No snapshots found.');

            return 0;
        }

        $rows = $snapshots->map(fn ($s) => [
            $s->id,
            $s->name,
            $s->cortical_network_id,
            number_format($s->file_size / 1024, 2).' KB',
            $s->type,
            $s->created_at ?? 'N/A',
        ])->toArray();

        $this->table(['ID', 'Name', 'Network', 'Size', 'Type', 'Created'], $rows);

        return 0;
    }

    private function syncMetadata(): int
    {
        $this->info('🔄 Synchronizing network metadata between Neo4j and MariaDB...');
        $this->newLine();

        $results = $this->databaseService->syncNetworkMetadata();

        $successful = collect($results)->where('status', 'success')->count();
        $failed = collect($results)->where('status', 'error')->count();

        $rows = collect($results)->map(fn ($r) => [
            $r['network_id'],
            $r['name'],
            $r['status'],
            $r['neuron_count'] ?? $r['error'] ?? 'N/A',
            $r['connection_count'] ?? '-',
        ])->toArray();

        $this->table(['Network ID', 'Name', 'Status', 'Neurons', 'Connections'], $rows);

        $this->newLine();
        $this->info("✅ Sync completed: {$successful} successful, {$failed} failed");

        return $failed > 0 ? 1 : 0;
    }

    private function exportNetwork(): int
    {
        $networkId = $this->option('network');
        $file = $this->option('file');
        $format = $this->option('format');

        if (! $networkId || ! $file) {
            $this->error('Missing required options: --network and --file');

            return 1;
        }

        $this->info("Exporting network {$networkId} to {$file} ({$format} format)...");

        $data = $this->databaseService->exportNetwork((int) $networkId, $format);

        file_put_contents($file, $data);

        $fileSize = filesize($file);

        $this->newLine();
        $this->info('✅ Export completed successfully!');
        $this->line("File: {$file}");
        $this->line('Size: '.number_format($fileSize / 1024, 2).' KB');
        $this->line("Format: {$format}");

        return 0;
    }

    private function importNetwork(): int
    {
        $networkId = $this->option('network');
        $file = $this->option('file');

        if (! $networkId || ! $file) {
            $this->error('Missing required options: --network and --file');

            return 1;
        }

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return 1;
        }

        if (! $this->option('force')) {
            if (! $this->confirm("⚠️  This will replace all data in network {$networkId}. Continue?")) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info("Importing network from {$file} into network {$networkId}...");

        $data = json_decode(file_get_contents($file), true);

        if (! $data) {
            $this->error('Invalid JSON file');

            return 1;
        }

        $result = $this->databaseService->importNetwork($data, (int) $networkId);

        $this->newLine();
        $this->info('✅ Import completed successfully!');
        $this->line("Neurons imported: {$result['neurons_imported']}");
        $this->line("Connections imported: {$result['relationships_imported']}");

        return 0;
    }

    private function cleanAll(): int
    {
        if (! $this->option('force')) {
            $this->warn('⚠️  WARNING: This will delete ALL cortical network data!');
            $this->line('This includes:');
            $this->line('  • All neurons in Neo4j');
            $this->line('  • All cortical networks in MariaDB');
            $this->line('  • All snapshots');
            $this->line('  • All activation sessions');
            $this->newLine();

            if (! $this->confirm('Are you absolutely sure you want to continue?')) {
                $this->info('Operation cancelled.');

                return 0;
            }

            if (! $this->confirm('Type "DELETE ALL" to confirm', false)) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info('🗑️  Cleaning all cortical network data...');

        $neuronCount = GraphCriteria::node('Neuron')->count();
        GraphCriteria::node('Neuron')
            ->getClient()
            ->run('MATCH (n:Neuron) DETACH DELETE n');

        $networkCount = DB::connection('ccf')->table('cortical_networks')->count();
        DB::connection('ccf')->table('cortical_networks')->delete();

        $snapshotCount = DB::connection('ccf')->table('network_snapshots')->count();
        DB::connection('ccf')->table('network_snapshots')->delete();

        $sessionCount = DB::connection('ccf')->table('activation_sessions')->count();
        DB::connection('ccf')->table('activation_sessions')->delete();

        DB::connection('ccf')->table('cortical_metadata')->delete();

        $this->newLine();
        $this->info('✅ Cleanup completed!');
        $this->line("Deleted {$neuronCount} neurons from Neo4j");
        $this->line("Deleted {$networkCount} cortical networks");
        $this->line("Deleted {$snapshotCount} snapshots");
        $this->line("Deleted {$sessionCount} activation sessions");

        return 0;
    }

    private function cleanTest(): int
    {
        $this->info('🗑️  Cleaning test data...');

        $testPatterns = ['%Findable%', '%Updatable%', '%Archivable%', '%Test%'];

        $totalNetworks = 0;
        $totalNeurons = 0;

        foreach ($testPatterns as $pattern) {
            $networks = DB::connection('ccf')
                ->table('cortical_networks')
                ->where('name', 'like', $pattern)
                ->get();

            foreach ($networks as $network) {
                $neurons = GraphCriteria::node('Neuron')
                    ->where('n.column_id', '=', $network->id)
                    ->count();

                GraphCriteria::node('Neuron')
                    ->getClient()
                    ->run('MATCH (n:Neuron) WHERE n.column_id = $id DETACH DELETE n', ['id' => $network->id]);

                $totalNeurons += $neurons;
                $totalNetworks++;
            }

            DB::connection('ccf')
                ->table('cortical_networks')
                ->where('name', 'like', $pattern)
                ->delete();
        }

        $this->newLine();
        $this->info('✅ Test data cleanup completed!');
        $this->line("Deleted {$totalNetworks} test networks");
        $this->line("Deleted {$totalNeurons} test neurons");

        return 0;
    }

    private function validate(): int
    {
        $this->info('🔍 Validating data consistency...');
        $this->newLine();

        $result = $this->databaseService->validateDataConsistency();

        if ($result['valid']) {
            $this->info('✅ All data is consistent!');

            return 0;
        }

        $this->warn("⚠️  Found {$result['issues_found']} consistency issues:");
        $this->newLine();

        foreach ($result['issues'] as $issue) {
            $this->line("• {$issue['type']} (Network {$issue['network_id']})");
            if (isset($issue['metadata_count']) && isset($issue['actual_count'])) {
                $this->line("  Expected: {$issue['metadata_count']}, Actual: {$issue['actual_count']}");
            }
            if (isset($issue['count'])) {
                $this->line("  Count: {$issue['count']}");
            }
        }

        $this->newLine();
        $this->info('💡 Run "php artisan cortical:manage sync:metadata" to fix metadata mismatches');

        return 1;
    }

    private function listNetworks(): int
    {
        $status = $this->option('status');

        $query = Criteria::table('cortical_networks')
            ->orderBy('id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $networks = $query->get();

        if ($networks->isEmpty()) {
            $this->info('No networks found.');

            return 0;
        }

        $rows = $networks->map(fn ($n) => [
            $n->id,
            $n->name,
            $n->status,
            $n->neuron_count ?? 0,
            $n->connection_count ?? 0,
            $n->created_at ?? 'N/A',
        ])->toArray();

        $this->table(['ID', 'Name', 'Status', 'Neurons', 'Connections', 'Created'], $rows);

        return 0;
    }

    private function showHelp(): int
    {
        $this->info('Cortical Network Management Command');
        $this->newLine();
        $this->line('Available operations:');
        $this->newLine();

        $operations = [
            ['snapshot:create', 'Create network snapshot', '--network=ID --name=NAME'],
            ['snapshot:load', 'Load network from snapshot', '--network=ID --name=NAME'],
            ['snapshot:list', 'List all snapshots', '[--network=ID]'],
            ['sync:metadata', 'Sync Neo4j ↔ MariaDB metadata', ''],
            ['export', 'Export network to file', '--network=ID --file=PATH [--format=json|cypher|gexf]'],
            ['import', 'Import network from file', '--network=ID --file=PATH'],
            ['clean:all', 'Clean all database data', '[--force]'],
            ['clean:test', 'Clean test data only', ''],
            ['validate', 'Validate data consistency', ''],
            ['list:networks', 'List all cortical networks', '[--status=active|inactive|archived]'],
        ];

        $this->table(['Operation', 'Description', 'Options'], $operations);

        $this->newLine();
        $this->line('Examples:');
        $this->line('  php artisan cortical:manage snapshot:create --network=2 --name="baseline"');
        $this->line('  php artisan cortical:manage export --network=2 --file=network.json');
        $this->line('  php artisan cortical:manage sync:metadata');
        $this->line('  php artisan cortical:manage clean:test');

        return 0;
    }
}
