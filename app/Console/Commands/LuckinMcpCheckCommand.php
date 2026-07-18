<?php

namespace App\Console\Commands;

use App\Services\GeoFlow\LuckinMcpClient;
use App\Services\GeoFlow\LuckinMcpCredentialStore;
use Illuminate\Console\Command;
use JsonException;

class LuckinMcpCheckCommand extends Command
{
    protected $signature = 'geoflow:luckin-mcp:check {--admin=* : Admin IDs to check} {--json : Output a machine-readable status}';

    protected $description = 'Refresh the cached Luckin MCP store and product capability status';

    public function handle(LuckinMcpClient $client, LuckinMcpCredentialStore $credentials): int
    {
        $adminIds = array_values(array_filter(
            array_map('intval', (array) $this->option('admin')),
            static fn (int $adminId): bool => $adminId > 0,
        ));
        if ($adminIds === []) {
            $adminIds = $credentials->configuredAdminIds();
        }
        $states = [];
        foreach ($adminIds as $adminId) {
            $states[(string) $adminId] = $client->refreshState($adminId);
        }

        if ((bool) $this->option('json')) {
            try {
                $this->line(json_encode(['admins' => $states], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            } catch (JsonException) {
                return self::FAILURE;
            }
        } else {
            if ($states === []) {
                $this->components->info('No Luckin MCP API keys are configured.');
            }
            foreach ($states as $adminId => $state) {
                $this->components->info('Luckin MCP status for admin '.$adminId.': '.$state['status']);
            }
        }

        return collect($states)->contains(static fn (array $state): bool => in_array(
            $state['status'],
            ['error', 'authorization_required'],
            true,
        ))
            ? self::FAILURE
            : self::SUCCESS;
    }
}
