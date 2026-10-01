<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MessageQueueResource;
use \App\Models\MessageQueue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\MessageQueue\DTOs\MessageQueueDTO;
use App\Domain\MessageQueue\Actions\CreateMessageQueueAction;
use App\Domain\MessageQueue\Actions\UpdateMessageQueueAction;

class MessageQueueController extends Controller
{
    public function pendingMessages(Request $request): JsonResponse
    {
        $user = $request->user();
        $shopId = $user?->barber_shop_id;

        if (!$shopId) {
            return response()->json(['data' => []]);
        }

        $items = MessageQueue::where('barber_shop_id', $shopId)
            ->where('status', 'pending')
            ->where('channel', 'sms')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        return response()->json(['data' => $items]);
    }

    public function markSent(Request $request): JsonResponse
    {
        $id = $request->input('sms_id') ?? $request->input('id');

        $item = MessageQueue::find($id);
        if ($item) {
            $status = in_array($request->input('status'), ['sent', 'failed']) ? $request->input('status') : 'sent';
            $item->update(['status' => $status]);
            \App\Models\AuditTrail::log($item, 'update', 'MessageQueues');

            // Update matching MessageLog record to 'sent' or 'failed'
            $log = \App\Models\MessageLog::where('barber_shop_id', $item->barber_shop_id)
                ->where('channel', 'sms')
                ->where('customer_id', $item->booking?->customer_id)
                ->latest('id')
                ->first();

            if ($log) {
                $log->update([
                    'status' => $status,
                    'sent_at' => $status === 'sent' ? now() : null,
                ]);
                \App\Models\AuditTrail::log($log, 'update', 'MessageLogs');
            }

            return response()->json(['success' => true, 'message' => "SMS status updated to {$status}!"]);
        }

        return response()->json(['success' => false, 'message' => 'Nuk u gjet mesazhi.'], 404);
    }

    public function index(Request $request)
    {
        abort_if_cannot('view_message_queues');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = MessageQueue::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = MessageQueue::query()->with([
            'barberShop',
            'booking',
        ]);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (['phone_number', 'message_content'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, [], true)) {
                continue;
            }
            if (in_array($key, array_keys(MessageQueue::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return MessageQueueResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_message_queues');
        $item = MessageQueue::with([
            'barberShop',
            'booking',
        ])->findOrFail($id);
        return new MessageQueueResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_message_queues');
        $data = $this->prepareData($request);

        $validated = validator($data, MessageQueue::rules())->validate();
        $item = app(\App\Domain\MessageQueue\Actions\CreateMessageQueueAction::class)->execute(\App\Domain\MessageQueue\DTOs\MessageQueueDTO::fromArray($validated));
        return (new MessageQueueResource($item->loadMissing([
            'barberShop',
            'booking',
        ])))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_message_queues');
        $item = MessageQueue::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, MessageQueue::rules($id))->validate();
        $item = app(\App\Domain\MessageQueue\Actions\UpdateDeviceTokenAction::class)->execute($item, \App\Domain\MessageQueue\DTOs\MessageQueueDTO::fromArray($validated));
        return new MessageQueueResource($item->loadMissing([
            'barberShop',
            'booking',
        ]));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_message_queues');

        try {
            $item = MessageQueue::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'MessageQueue deleted.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Delete Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Record is referenced by other data and cannot be deleted.',
            ], 409);
        }
    }

    private function prepareData(Request $request): array
    {
        $data = $request->all();

        foreach ([
        ] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$field] = $decoded;
                }
            }
        }

        foreach ([
        ] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/message-queues');
            }
        }

        return $data;
    }
}
