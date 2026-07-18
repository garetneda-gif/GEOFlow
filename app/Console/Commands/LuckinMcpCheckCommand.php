<?php

namespace App\Console\Commands;

use App\Services\GeoFlow\LuckinMcpClient;
use Illuminate\Console\Command;
use JsonException;

class LuckinMcpCheckCommand extends Command
{
    protected $signature = 'geoflow:luckin-mcp:check {--json : Output a machine-readable status}';

    protected $description = 'Refresh the cached Luckin MCP store and product capability status';

    public function handle(LuckinMcpClient $client): int
    {
        $state = $client->refreshState();

        if ((bool) $this->option('json')) {
            try {
                $this->line(json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            } catch (JsonException) {
                return self::FAILURE;
            }
        } else {
            $this->components->info('Luckin MCP status: '.$state['status']);
        }

        return $state['status'] === 'error' ? self::FAILURE : self::SUCCESS;
    }
}
