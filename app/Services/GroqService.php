<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
    }

    public function interpretOperationalPrompt(string $prompt): array
    {
        if ($this->apiKey) {
            try {
                $systemPrompt = "You are an AI Operational Assistant for a SaaS Salon Management System.
                Parse the user's natural language command into a JSON array of operational actions.

                ACTIONS:
                1. 'create_services':
                   details: {
                      services: [ { name: 'Prerje', price: 1000, duration_minutes: 30 } ]
                   }
                2. 'create_bookings':
                   details: {
                      count: 5,
                      start_hour: 13,
                      end_hour: 17,
                      is_walkin: true,
                      send_sms: false,
                      service_name: 'Qethje'
                   }
                3. 'create_staff':
                   details: {
                      staff: [ { name: 'Ardit' }, { name: 'Klara' } ]
                   }
                4. 'create_customers':
                   details: {
                      customers: [ { name: 'Klient 1' } ]
                   }
                5. 'query_stats':
                   details: { type: 'today' }

                RETURN ONLY A VALID JSON ARRAY OF ACTIONS:
                [
                  {
                    \"action\": \"create_services|create_bookings|create_staff|create_customers|query_stats\",
                    \"details\": { ... }
                  }
                ]";

                $response = Http::withToken($this->apiKey)
                    ->timeout(10)
                    ->post($this->baseUrl, [
                        'model' => 'llama-3.3-70b-versatile',
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.1,
                    ]);

                $content = (string) $response->json('choices.0.message.content');
                if (preg_match('/(\[.*\]|\{.*\})/s', $content, $matches)) {
                    $content = $matches[0];
                }

                $data = json_decode($content, true);
                if (is_array($data)) {
                    return isset($data['action']) ? [$data] : $data;
                }
            } catch (\Throwable $e) {}
        }

        // Smart Regex / Keyword Fallback Parser for Albanian & English commands
        return $this->fallbackOperationalParser($prompt);
    }

    protected function fallbackOperationalParser(string $prompt): array
    {
        $lower = mb_strtolower($prompt);
        $actions = [];

        // Check for Services creation
        if (str_contains($lower, 'sherbim') || str_contains($lower, 'shërbim') || str_contains($lower, 'service')) {
            preg_match('/(\d+)\s*(?:sherbim|shërbim|service)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 3;

            $parsedServices = [];
            // Check if specific names/prices are mentioned e.g. after ":"
            if (str_contains($prompt, ':')) {
                $parts = explode(':', $prompt, 2);
                $listStr = $parts[1] ?? '';
                $items = explode(',', $listStr);

                foreach ($items as $item) {
                    $itemTrim = trim($item);
                    if (empty($itemTrim)) continue;

                    // Extract price
                    preg_match('/(\d+)\s*(?:lekë|leke|lek|eur|euro|€|\$)?/ui', $itemTrim, $priceMatch);
                    $price = isset($priceMatch[1]) ? (float) $priceMatch[1] : 1000;

                    // Extract duration
                    preg_match('/(\d+)\s*(?:min|minuta)/ui', $itemTrim, $durMatch);
                    $dur = isset($durMatch[1]) ? (int) $durMatch[1] : 30;

                    // Clean service name: remove digits, currencies, min, and extra symbols
                    $cleanName = preg_replace('/\d+/u', '', $itemTrim);
                    $cleanName = preg_replace('/(lekë|leke|lek|eur|euro|€|\$|minuta|min)/ui', '', $cleanName);
                    $cleanName = trim(str_replace([',', ';', '-', ':', 'ë', 'e'], [' ', ' ', ' ', ' ', 'ë', 'e'], $cleanName));
                    $cleanName = preg_replace('/\s+/', ' ', $cleanName);

                    if (!empty($cleanName)) {
                        $parsedServices[] = [
                            'name' => mb_convert_case(trim($cleanName), MB_CASE_TITLE, 'UTF-8'),
                            'price' => $price,
                            'duration_minutes' => $dur,
                        ];
                    }
                }
            }

            if (empty($parsedServices)) {
                $defaults = ['Prerje & Stilim', 'Mjekërr & Rroje', 'Larje & Masazh Fytyre', 'Modelim i Plotë', 'Trajtim Flokësh'];
                for ($i = 0; $i < min($count, 10); $i++) {
                    $parsedServices[] = [
                        'name' => $defaults[$i % count($defaults)] . ($i > 4 ? ' ' . ($i + 1) : ''),
                        'price' => (rand(5, 20) * 100),
                        'duration_minutes' => [15, 30, 45, 60][rand(0, 3)],
                    ];
                }
            }

            $actions[] = [
                'action' => 'create_services',
                'details' => ['services' => $parsedServices]
            ];
        }

        // Check for Bookings creation
        if (str_contains($lower, 'booking') || str_contains($lower, 'rezervim') || str_contains($lower, 'walkin') || str_contains($lower, 'walk-in') || str_contains($lower, 'takim')) {
            preg_match('/(\d+)\s*(?:booking|rezervim|walkin|walk-in|takim)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 5;

            // Extract hours e.g. "ora 13 deri 17" or "nga 1 ne 5"
            preg_match('/(?:ora|nga)?\s*(\d{1,2})\s*(?:ne|deri|-)\s*(\d{1,2})/u', $lower, $hMatch);
            $startHour = isset($hMatch[1]) ? (int) $hMatch[1] : 13;
            $endHour = isset($hMatch[2]) ? (int) $hMatch[2] : 17;

            if ($startHour < 7 && $startHour > 0) $startHour += 12; // e.g. "1" -> 13
            if ($endHour < 7 && $endHour > 0) $endHour += 12;     // e.g. "5" -> 17

            $actions[] = [
                'action' => 'create_bookings',
                'details' => [
                    'count' => $count,
                    'start_hour' => $startHour,
                    'end_hour' => $endHour,
                    'is_walkin' => true,
                    'send_sms' => false,
                ]
            ];
        }

        // Check for Staff creation
        if (str_contains($lower, 'staf') || str_contains($lower, 'staff') || str_contains($lower, 'berber') || str_contains($lower, 'parukier')) {
            preg_match('/(\d+)\s*(?:staf|staff|berber|parukier)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 3;

            $parsedStaff = [];
            if (str_contains($prompt, ':')) {
                $parts = explode(':', $prompt, 2);
                $names = explode(',', $parts[1] ?? '');
                foreach ($names as $n) {
                    $clean = trim($n);
                    if (!empty($clean)) {
                        $parsedStaff[] = ['name' => mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8')];
                    }
                }
            }

            if (empty($parsedStaff)) {
                $defaults = ['Ardit Berber', 'Klara Stylist', 'Egli Hoxha', 'Arben Stylist', 'Sonia Nails'];
                for ($i = 0; $i < min($count, 10); $i++) {
                    $parsedStaff[] = ['name' => $defaults[$i % count($defaults)]];
                }
            }

            $actions[] = [
                'action' => 'create_staff',
                'details' => ['staff' => $parsedStaff]
            ];
        }

        // Check for Customers creation
        if (str_contains($lower, 'klient') || str_contains($lower, 'customer')) {
            preg_match('/(\d+)\s*(?:klient|customer)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 3;

            $parsedCustomers = [];
            if (str_contains($prompt, ':')) {
                $parts = explode(':', $prompt, 2);
                $names = explode(',', $parts[1] ?? '');
                foreach ($names as $n) {
                    $clean = trim($n);
                    if (!empty($clean)) {
                        $parsedCustomers[] = ['name' => mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8')];
                    }
                }
            }

            if (empty($parsedCustomers)) {
                $defaults = ['Sokol Prifti', 'Alban Hoxha', 'Erion Marku', 'Gentian Krasniqi', 'Dritan Basha'];
                for ($i = 0; $i < min($count, 10); $i++) {
                    $parsedCustomers[] = ['name' => $defaults[$i % count($defaults)]];
                }
            }

            $actions[] = [
                'action' => 'create_customers',
                'details' => ['customers' => $parsedCustomers]
            ];
        }

        if (empty($actions)) {
            $actions[] = [
                'action' => 'query_stats',
                'details' => ['type' => 'today']
            ];
        }

        return $actions;
    }

    public function interpretPrompt(string $prompt): array
    {
        if (!$this->apiKey) {
            throw new \Exception('GROQ_API_KEY is not set in .env.');
        }

        $systemPrompt = "You are an expert Laravel system architect. Parse user request into JSON array of module actions.
        Structure: [ { \"intent\": \"create|delete\", \"model\": \"ModelName\", \"fields\": [...], \"api\": true, \"icon\": \"...\" } ]";

        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl, [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.1,
            ]);

        $content = (string) $response->json('choices.0.message.content');
        if (preg_match('/(\[.*\]|\{.*\})/s', $content, $matches)) {
            $content = $matches[0];
        }

        $data = json_decode($content, true);
        if (is_array($data) && isset($data['model'])) {
            return [$data];
        }

        return $data ?? [];
    }
}
