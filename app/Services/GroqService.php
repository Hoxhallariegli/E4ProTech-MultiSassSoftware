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
                1. 'create_services': details: { services: [ { name: 'Prerje', price: 1000, duration_minutes: 30 } ] }
                2. 'create_bookings': details: { count: 1, start_hour: 13, start_minute: 0, customer_name: 'Ferid', is_walkin: true, send_sms: false }
                3. 'create_staff': details: { staff: [ { name: 'Ardit' } ] }
                4. 'create_customers': details: { customers: [ { name: 'Klient 1' } ] }
                5. 'delete_bookings': details: { count: 5, ids: [] }
                6. 'delete_services': details: { count: 5 }
                7. 'delete_staff': details: { count: 3 }
                8. 'query_stats': details: { type: 'today' }

                RETURN ONLY A VALID JSON ARRAY OF ACTIONS:
                [
                  {
                    \"action\": \"create_services|create_bookings|create_staff|create_customers|delete_bookings|delete_services|delete_staff|query_stats\",
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
        $hasBookingAction = false;

        // CHECK INTENT: DELETE vs CREATE
        $isDelete = str_contains($lower, 'fshi') || str_contains($lower, 'fshij') || str_contains($lower, 'anulo') || str_contains($lower, 'delete') || str_contains($lower, 'remove');

        if ($isDelete) {
            // Delete Bookings
            if (str_contains($lower, 'booking') || str_contains($lower, 'rezervim') || str_contains($lower, 'walkin') || str_contains($lower, 'walk-in') || str_contains($lower, 'takim') || str_contains($lower, 'krijove') || str_contains($lower, 'sapo')) {
                preg_match('/(\d+)/u', $lower, $m);
                $count = isset($m[1]) ? (int) $m[1] : 5;

                preg_match_all('/#?(\d{2,})/u', $prompt, $idMatches);
                $ids = !empty($idMatches[1]) ? array_map('intval', $idMatches[1]) : [];

                return [
                    [
                        'action' => 'delete_bookings',
                        'details' => [
                            'count' => $count,
                            'ids' => $ids,
                        ]
                    ]
                ];
            }

            // Delete Services
            if (str_contains($lower, 'sherbim') || str_contains($lower, 'shërbim') || str_contains($lower, 'service')) {
                preg_match('/(\d+)/u', $lower, $m);
                $count = isset($m[1]) ? (int) $m[1] : 5;

                return [
                    [
                        'action' => 'delete_services',
                        'details' => ['count' => $count]
                    ]
                ];
            }

            // Delete Staff
            if (str_contains($lower, 'staf') || str_contains($lower, 'staff') || str_contains($lower, 'berber') || str_contains($lower, 'parukier')) {
                preg_match('/(\d+)/u', $lower, $m);
                $count = isset($m[1]) ? (int) $m[1] : 3;

                return [
                    [
                        'action' => 'delete_staff',
                        'details' => ['count' => $count]
                    ]
                ];
            }

            return [
                [
                    'action' => 'delete_bookings',
                    'details' => ['count' => 5]
                ]
            ];
        }

        // CREATE ACTIONS

        // Services creation
        if (str_contains($lower, 'sherbim') || str_contains($lower, 'shërbim') || str_contains($lower, 'service')) {
            preg_match('/(\d+)\s*(?:sherbim|shërbim|service)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 3;

            $parsedServices = [];
            if (str_contains($prompt, ':')) {
                $parts = explode(':', $prompt, 2);
                $listStr = $parts[1] ?? '';
                $items = explode(',', $listStr);

                foreach ($items as $item) {
                    $itemTrim = trim($item);
                    if (empty($itemTrim)) continue;

                    preg_match('/(\d+)\s*(?:lekë|leke|lek|eur|euro|€|\$)?/ui', $itemTrim, $priceMatch);
                    $price = isset($priceMatch[1]) ? (float) $priceMatch[1] : 1000;

                    preg_match('/(\d+)\s*(?:min|minuta)/ui', $itemTrim, $durMatch);
                    $dur = isset($durMatch[1]) ? (int) $durMatch[1] : 30;

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

        // Bookings creation
        if (str_contains($lower, 'booking') || str_contains($lower, 'rezervim') || str_contains($lower, 'walkin') || str_contains($lower, 'walk-in') || str_contains($lower, 'takim')) {
            $hasBookingAction = true;
            preg_match('/(\d+)\s*(?:booking|rezervim|walkin|walk-in|takim)/u', $lower, $m);
            $count = isset($m[1]) ? (int) $m[1] : 1;

            // Extract exact time e.g. "ne ore 13:00" or "ora 13" or "13:00"
            preg_match('/(?:ore|ora|orën)?\s*(\d{1,2})(?::(\d{2}))?/ui', $lower, $tMatch);
            $startHour = isset($tMatch[1]) ? (int) $tMatch[1] : 13;
            $startMin = isset($tMatch[2]) ? (int) $tMatch[2] : 0;

            if ($startHour < 7 && $startHour > 0) $startHour += 12; // e.g. "1" -> 13

            // Extract customer name e.g. "klient Ferid" or "klientin Sokol"
            $customerName = null;
            if (preg_match('/klient(?:in)?\s+([a-zA-ZçÇëË\s]+)/ui', $prompt, $custMatch)) {
                $customerName = trim(preg_replace('/\s+/', ' ', $custMatch[1]));
            }

            $actions[] = [
                'action' => 'create_bookings',
                'details' => [
                    'count' => $count,
                    'start_hour' => $startHour,
                    'start_minute' => $startMin,
                    'end_hour' => $startHour + 1,
                    'customer_name' => $customerName,
                    'is_walkin' => true,
                    'send_sms' => false,
                ]
            ];
        }

        // Staff creation
        if (str_contains($lower, 'staf') || str_contains($lower, 'staff') || str_contains($lower, 'berber') || str_contains($lower, 'parukier')) {
            $count = 1; // Default to 1 for singular command

            if (preg_match('/(\d+)\s*(?:staf|staff|berber|parukier)/u', $lower, $m)) {
                $count = (int) $m[1];
            } elseif (preg_match('/(?:dy|2)\s*(?:staf|staff|berber|parukier)/u', $lower)) {
                $count = 2;
            } elseif (preg_match('/(?:tre|3)\s*(?:staf|staff|berber|parukier)/u', $lower)) {
                $count = 3;
            } elseif (preg_match('/(?:katër|kater|4)\s*(?:staf|staff|berber|parukier)/u', $lower)) {
                $count = 4;
            } elseif (preg_match('/(?:pesë|pese|5)\s*(?:staf|staff|berber|parukier)/u', $lower)) {
                $count = 5;
            } elseif (preg_match('/(?:një|nje|1|a|one)\s*(?:staf|staff|berber|parukier)/u', $lower)) {
                $count = 1;
            }

            $parsedStaff = [];

            // Colon format e.g. "shto berber: Ermin Cimi"
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

            // Explicit name match e.g. "me emrin Ermin Cimi", "me ermin cimi", "named Ermin Cimi"
            if (empty($parsedStaff)) {
                if (preg_match('/(?:me\s+)?(?:emrin|ermin|emër|emer|named)\s+([a-zA-ZçÇëË\s]+)/ui', $prompt, $nameMatch)) {
                    $extracted = trim($nameMatch[1]);
                    if (!empty($extracted)) {
                        $parsedStaff[] = ['name' => mb_convert_case($extracted, MB_CASE_TITLE, 'UTF-8')];
                    }
                }
            }

            // Direct name match after staff/berber keyword e.g. "shto berber Ermin Cimi"
            if (empty($parsedStaff)) {
                if (preg_match('/(?:shto|krijo|add|create)\s+(?:një|nje|1)?\s*(?:staf|staff|berber|parukier)\s+([a-zA-ZçÇëË\s]+)/ui', $prompt, $directMatch)) {
                    $extracted = trim($directMatch[1]);
                    $extracted = preg_replace('/^(?:me|per|për|me\s+emrin|me\s+ermin)\s+/ui', '', $extracted);
                    if (!empty($extracted)) {
                        $parsedStaff[] = ['name' => mb_convert_case(trim($extracted), MB_CASE_TITLE, 'UTF-8')];
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

        // Customers creation (ONLY if not a booking action)
        if (!$hasBookingAction && (str_contains($lower, 'klient') || str_contains($lower, 'customer'))) {
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
