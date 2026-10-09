<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\TestModuleResource;
use \App\Models\TestModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\TestModule\DTOs\TestModuleDTO;
use App\Domain\TestModule\Actions\CreateTestModuleAction;
use App\Domain\TestModule\Actions\UpdateTestModuleAction;

class TestModuleController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_test_modules');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = TestModule::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = TestModule::query()->with(array (
  0 => 'user',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'name',
  1 => 'description',
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
            if (in_array($key, array_keys(TestModule::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return TestModuleResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_test_modules');
        $item = TestModule::with(array (
  0 => 'user',
))->findOrFail($id);
        return new TestModuleResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_test_modules');
                $data = $this->prepareData($request);
        
        $validated = validator($data, TestModule::rules())->validate();
        $item = app(\App\Domain\TestModule\Actions\CreateTestModuleAction::class)->execute(\App\Domain\TestModule\DTOs\TestModuleDTO::fromArray($validated));
        return (new TestModuleResource($item->loadMissing(array (
  0 => 'user',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_test_modules');
                $item = TestModule::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, TestModule::rules($id))->validate();
        $item = app(\App\Domain\TestModule\Actions\UpdateTestModuleAction::class)->execute($item, \App\Domain\TestModule\DTOs\TestModuleDTO::fromArray($validated));
        return new TestModuleResource($item->loadMissing(array (
  0 => 'user',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_test_modules');

        try {
            $item = TestModule::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'TestModule deleted.']);
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
  0 => 'image',
  1 => 'cover_photo',
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/test-modules');
            }
        }

        return $data;
    }
}