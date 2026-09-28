<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MessageTemplateResource;
use \App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Domain\MessageTemplate\Actions\CreateMessageTemplateAction;
use App\Domain\MessageTemplate\Actions\UpdateMessageTemplateAction;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_message_templates');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = MessageTemplate::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = MessageTemplate::query()->with(array (
  0 => 'barberShop',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'content',
) as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
                continue;
            }
            if (in_array($key, array (
), true)) {
                continue;
            }
            if (in_array($key, array_keys(MessageTemplate::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return MessageTemplateResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_message_templates');
        $item = MessageTemplate::with(array (
  0 => 'barberShop',
))->findOrFail($id);
        return new MessageTemplateResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_message_templates');
                $data = $this->prepareData($request);
        
        $validated = validator($data, MessageTemplate::rules())->validate();
        $item = app(\App\Domain\MessageTemplate\Actions\CreateMessageTemplateAction::class)->execute(\App\Domain\MessageTemplate\DTOs\MessageTemplateDTO::fromArray($validated));
        return (new MessageTemplateResource($item->loadMissing(array (
  0 => 'barberShop',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_message_templates');
                $item = MessageTemplate::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, MessageTemplate::rules($id))->validate();
        $item = app(\App\Domain\MessageTemplate\Actions\UpdateMessageTemplateAction::class)->execute($item, \App\Domain\MessageTemplate\DTOs\MessageTemplateDTO::fromArray($validated));
        return new MessageTemplateResource($item->loadMissing(array (
  0 => 'barberShop',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_message_templates');

        try {
            $item = MessageTemplate::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'MessageTemplate deleted.']);
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

        foreach (array (
) as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $decoded = json_decode($data[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data[$field] = $decoded;
                }
            }
        }

        foreach (array (
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/message-templates');
            }
        }

        return $data;
    }
}