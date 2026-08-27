<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.google_sheets.webhook_url', '');
    }

    /**
     * Push a row to a tab on the shared spreadsheet. Fire-and-forget: a Sheets
     * outage never blocks a real submission (already safely in the database)
     * from succeeding for the visitor.
     */
    public function push(string $tab, array $data): bool
    {
        if ($this->webhookUrl === '') {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->asJson()
                ->post($this->webhookUrl, [
                    'tab'  => $tab,
                    'data' => $data,
                ]);

            $ok = $response->successful() && data_get($response->json(), 'success', false);

            if (!$ok) {
                Log::warning('Google Sheets sync failed', [
                    'tab'      => $tab,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('Google Sheets sync threw', [
                'tab'   => $tab,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
