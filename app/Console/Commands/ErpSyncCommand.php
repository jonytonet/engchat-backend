<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ErpIntegrationService;
use Illuminate\Console\Command;

class ErpSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:sync 
                            {type : Type of sync (users|contacts|all)}
                            {--file= : JSON file path with data to sync}
                            {--dry-run : Show what would be synced without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize users and contacts with ERP system';

    public function __construct(
        private readonly ErpIntegrationService $erpService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $file = $this->option('file');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        try {
            match ($type) {
                'users' => $this->syncUsers($file, $dryRun),
                'contacts' => $this->syncContacts($file, $dryRun),
                'all' => $this->syncAll($file, $dryRun),
                default => $this->error("❌ Invalid type: {$type}. Use: users, contacts, or all")
            };

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error during sync: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function syncUsers(?string $file, bool $dryRun): void
    {
        $this->info('👥 Syncing users with ERP...');

        if ($file) {
            $users = $this->loadDataFromFile($file);

            if ($dryRun) {
                $this->showDryRunResults('users', $users);
                return;
            }

            $results = $this->erpService->batchSyncUsers($users);
            $this->showResults('Users', $results);
        } else {
            // Show users that need syncing
            $users = $this->erpService->getUsersForErpSync();
            $this->info("Found " . count($users) . " users that need ERP sync");

            if ($dryRun) {
                $this->table(['ID', 'Name', 'Email'], array_map(fn($u) => [
                    $u['id'],
                    $u['name'],
                    $u['email']
                ], $users));
            }
        }
    }

    private function syncContacts(?string $file, bool $dryRun): void
    {
        $this->info('📞 Syncing contacts with ERP...');

        if ($file) {
            $contacts = $this->loadDataFromFile($file);

            if ($dryRun) {
                $this->showDryRunResults('contacts', $contacts);
                return;
            }

            $results = $this->erpService->batchSyncContacts($contacts);
            $this->showResults('Contacts', $results);
        } else {
            // Show contacts that need syncing
            $contacts = $this->erpService->getContactsForErpSync();
            $this->info("Found " . count($contacts) . " contacts that need ERP sync");

            if ($dryRun) {
                $this->table(['ID', 'Name', 'Email', 'Phone'], array_map(fn($c) => [
                    $c['id'],
                    $c['name'],
                    $c['email'],
                    $c['phone']
                ], $contacts));
            }
        }
    }

    private function syncAll(?string $file, bool $dryRun): void
    {
        $this->info('🔄 Syncing all data with ERP...');

        $this->syncUsers($file, $dryRun);
        $this->newLine();
        $this->syncContacts($file, $dryRun);
    }

    private function loadDataFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON in file: " . json_last_error_msg());
        }

        return $data;
    }

    private function showDryRunResults(string $type, array $data): void
    {
        $this->info("Would sync " . count($data) . " {$type}:");

        foreach (array_slice($data, 0, 5) as $item) {
            $identifier = $type === 'users'
                ? ($item['erp_user_id'] ?? 'N/A')
                : ($item['businesspartner_id'] ?? 'N/A');

            $this->line("  - {$identifier}: {$item['name']} ({$item['email']})");
        }

        if (count($data) > 5) {
            $this->line("  ... and " . (count($data) - 5) . " more");
        }
    }

    private function showResults(string $type, array $results): void
    {
        $this->info("✅ {$type} sync completed:");
        $this->line("  Success: {$results['success']}");
        $this->line("  Errors: {$results['errors']}");

        if ($results['errors'] > 0) {
            $this->warn("⚠️  Errors occurred during sync:");
            foreach ($results['details'] as $detail) {
                if ($detail['status'] === 'error') {
                    $this->error("  - {$detail['message']}");
                }
            }
        }
    }
}
