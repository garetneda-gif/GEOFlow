<?php

namespace App\Services\GeoFlow;

use App\Models\Admin;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;

class LuckinMcpCredentialStore
{
    public function __construct(private readonly Encrypter $encrypter) {}

    public function tokenFor(int $adminId): string
    {
        if ($adminId <= 0) {
            return '';
        }

        $ciphertext = (string) Admin::query()->whereKey($adminId)->value('luckin_mcp_api_key');

        return $this->decrypt($ciphertext);
    }

    public function maskFor(int $adminId): string
    {
        if ($adminId <= 0) {
            return '';
        }

        $ciphertext = (string) Admin::query()->whereKey($adminId)->value('luckin_mcp_api_key');
        if ($ciphertext === '') {
            return '';
        }

        $plainApiKey = $this->decrypt($ciphertext);
        $length = strlen($plainApiKey);
        if ($length === 0) {
            return '';
        }
        if ($length <= 8) {
            return str_repeat('*', max($length, 4));
        }

        return substr($plainApiKey, 0, 4).str_repeat('*', max($length - 8, 8)).substr($plainApiKey, -4);
    }

    public function put(int $adminId, string $apiKey): void
    {
        Admin::query()->whereKey($adminId)->update([
            'luckin_mcp_api_key' => $this->encrypter->encryptString(trim($apiKey)),
        ]);
    }

    public function forget(int $adminId): void
    {
        if ($adminId <= 0) {
            return;
        }

        Admin::query()->whereKey($adminId)->update(['luckin_mcp_api_key' => null]);
    }

    public function configuredAdminIds(): array
    {
        return Admin::query()
            ->whereNotNull('luckin_mcp_api_key')
            ->where('luckin_mcp_api_key', '!=', '')
            ->pluck('id')
            ->map(static fn (mixed $adminId): int => (int) $adminId)
            ->values()
            ->all();
    }

    private function decrypt(string $ciphertext): string
    {
        if ($ciphertext === '') {
            return '';
        }

        try {
            return $this->encrypter->decryptString($ciphertext);
        } catch (DecryptException) {
            return '';
        }
    }
}
