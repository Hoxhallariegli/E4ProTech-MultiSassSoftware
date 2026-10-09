<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ShopFrontPageSettingResource;
use \App\Models\ShopFrontPageSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO;
use App\Domain\ShopFrontPageSetting\Actions\CreateShopFrontPageSettingAction;
use App\Domain\ShopFrontPageSetting\Actions\UpdateShopFrontPageSettingAction;

class ShopFrontPageSettingController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_shop_front_page_settings');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = ShopFrontPageSetting::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = ShopFrontPageSetting::query()->with(array (
  0 => 'barberShop',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'hero_title',
  1 => 'hero_subtitle',
  2 => 'hero_button_text',
  3 => 'services_badge_text',
  4 => 'services_title',
  5 => 'staff_badge_text',
  6 => 'staff_title',
  7 => 'contact_phone',
  8 => 'contact_email',
  9 => 'contact_address',
  10 => 'google_maps_url',
  11 => 'footer_text',
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
            if (in_array($key, array_keys(ShopFrontPageSetting::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return ShopFrontPageSettingResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_shop_front_page_settings');
        $item = ShopFrontPageSetting::with(array (
  0 => 'barberShop',
))->findOrFail($id);
        return new ShopFrontPageSettingResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_shop_front_page_settings');
                $data = $this->prepareData($request);
        
        $validated = validator($data, ShopFrontPageSetting::rules())->validate();
        $item = app(\App\Domain\ShopFrontPageSetting\Actions\CreateShopFrontPageSettingAction::class)->execute(\App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO::fromArray($validated));
        return (new ShopFrontPageSettingResource($item->loadMissing(array (
  0 => 'barberShop',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_shop_front_page_settings');
                $item = ShopFrontPageSetting::findOrFail($id);
        $data = $this->prepareData($request);
        $validated = validator($data, ShopFrontPageSetting::rules($id))->validate();
        $item = app(\App\Domain\ShopFrontPageSetting\Actions\UpdateShopFrontPageSettingAction::class)->execute($item, \App\Domain\ShopFrontPageSetting\DTOs\ShopFrontPageSettingDTO::fromArray($validated));
        return new ShopFrontPageSettingResource($item->loadMissing(array (
  0 => 'barberShop',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_shop_front_page_settings');

        try {
            $item = ShopFrontPageSetting::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'ShopFrontPageSetting deleted.']);
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
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/shop-front-page-settings');
            }
        }

        return $data;
    }
}