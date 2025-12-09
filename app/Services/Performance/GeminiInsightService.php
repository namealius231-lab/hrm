<?php

namespace App\Services\Performance;

use App\Models\Employee;
use App\Models\PerformanceAiInsight;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiInsightService
{
    public function generateForEmployee(Employee $employee, array $metrics, ?User $actor = null): ?PerformanceAiInsight
    {
        $config = config('services.gemini');
        $apiKey = $config['api_key'] ?? null;
        $model = $config['model'] ?? 'gemini-2.0-flash';
        $base = rtrim($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/models', '/');

        if (empty($apiKey)) {
            return null;
        }

        $prompt = $this->buildPrompt($employee, $metrics);

        $endpoint = sprintf('%s/%s:generateContent?key=%s', $base, urlencode($model), urlencode($apiKey));

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini insight request failed', [
                    'employee_id' => $employee->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text');
            if (empty($text)) {
                return null;
            }

            return PerformanceAiInsight::create([
                'employee_id' => $employee->id,
                'org_position_id' => $employee->org_position_id,
                'generated_by' => $actor?->id,
                'context_type' => 'employee',
                'context_id' => $employee->id,
                'model' => $model,
                'prompt' => $prompt,
                'response' => $text,
                'metrics' => $metrics,
                'payload' => $response->json(),
                'created_by' => $actor?->creatorId() ?? $employee->created_by,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Gemini insight exception', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function buildPrompt(Employee $employee, array $metrics): string
    {
        $lines = [
            "Act as an HR performance analyst and summarise insights for {$employee->name}.",
            'Use the provided metrics to highlight strengths, risks, and actionable advice in under 120 words.',
            '',
            'Metrics:',
        ];

        foreach ($metrics as $key => $value) {
            $formattedValue = is_array($value) ? json_encode($value) : $value;
            $lines[] = "- {$key}: {$formattedValue}";
        }

        return implode("\n", $lines);
    }
}

