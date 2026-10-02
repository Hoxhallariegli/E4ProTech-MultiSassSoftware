<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\MessageTemplateResource;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $query = MessageTemplate::query()->with(['barberShop']);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        foreach ($request->all() as $key => $value) {
            if ($value === null || $value === '' || !str_ends_with($key, '_id')) {
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
        $item = MessageTemplate::with(['barberShop'])->findOrFail($id);
        return new MessageTemplateResource($item);
    }

    public function store(Request $request)
    {
        if (!user_can('add_message_templates') && !user_can('edit_message_templates')) {
            abort(403, 'Ju nuk keni leje për këtë veprim.');
        }

        $data = $this->prepareData($request);

        $rawContent = $request->input('content');
        $contentSq = $request->input('content_sq', is_array($rawContent) ? ($rawContent['sq'] ?? '') : (is_string($rawContent) ? $rawContent : ''));
        $contentEn = $request->input('content_en', is_array($rawContent) ? ($rawContent['en'] ?? '') : '');

        $contentArr = [
            'sq' => (string) $contentSq,
            'en' => (string) $contentEn,
        ];

        $data['content'] = json_encode($contentArr, JSON_UNESCAPED_UNICODE);

        $validated = validator($data, MessageTemplate::rules())->validate();
        $validated['content'] = $contentArr;

        $item = app(\App\Domain\MessageTemplate\Actions\CreateMessageTemplateAction::class)->execute(\App\Domain\MessageTemplate\DTOs\MessageTemplateDTO::fromArray($validated));
        return (new MessageTemplateResource($item->loadMissing(['barberShop'])))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        if (!user_can('edit_message_templates') && !user_can('add_message_templates')) {
            abort(403, 'Ju nuk keni leje për këtë veprim.');
        }

        $item = MessageTemplate::findOrFail($id);
        $data = $this->prepareData($request);

        $rawContent = $request->input('content');
        $contentSq = $request->input('content_sq', is_array($rawContent) ? ($rawContent['sq'] ?? '') : (is_string($rawContent) ? $rawContent : ''));
        $contentEn = $request->input('content_en', is_array($rawContent) ? ($rawContent['en'] ?? '') : '');

        $contentArr = [
            'sq' => (string) $contentSq,
            'en' => (string) $contentEn,
        ];

        $data['content'] = json_encode($contentArr, JSON_UNESCAPED_UNICODE);

        $validated = validator($data, MessageTemplate::rules($id))->validate();
        $validated['content'] = $contentArr;

        $item = app(\App\Domain\MessageTemplate\Actions\UpdateMessageTemplateAction::class)->execute($item, \App\Domain\MessageTemplate\DTOs\MessageTemplateDTO::fromArray($validated));
        return new MessageTemplateResource($item->loadMissing(['barberShop']));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_message_templates');

        try {
            $item = MessageTemplate::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Template deleted.']);
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
        return $request->all();
    }
}
