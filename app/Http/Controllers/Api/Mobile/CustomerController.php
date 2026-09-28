<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\CustomerResource;
use \App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Domain\Customer\DTOs\CustomerDTO;
use App\Domain\Customer\Actions\CreateCustomerAction;
use App\Domain\Customer\Actions\UpdateCustomerAction;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        abort_if_cannot('view_customers');

        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = Customer::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = Customer::query()->with(array (
  0 => 'barberShop',
));

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
                foreach (array (
  0 => 'name',
  1 => 'phone',
  2 => 'email',
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
            if (in_array($key, array_keys(Customer::rules()), true)) {
                $query->where($key, $value);
            }
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return CustomerResource::collection($items);
    }

    public function show($id)
    {
        abort_if_cannot('view_customers');
        $item = Customer::with(array (
  0 => 'barberShop',
))->findOrFail($id);
        return new CustomerResource($item);
    }

    public function store(Request $request)
    {
        abort_if_cannot('add_customers');
        $data = $this->prepareData($request);

        if (empty($data['barber_shop_id']) && $request->user()?->barber_shop_id) {
            $data['barber_shop_id'] = $request->user()->barber_shop_id;
        }

        $data['total_bookings'] = isset($data['total_bookings']) && $data['total_bookings'] !== '' ? (int)$data['total_bookings'] : 0;
        $data['no_show_count'] = isset($data['no_show_count']) && $data['no_show_count'] !== '' ? (int)$data['no_show_count'] : 0;

        $validated = validator($data, Customer::rules())->validate();
        $item = app(\App\Domain\Customer\Actions\CreateCustomerAction::class)->execute(\App\Domain\Customer\DTOs\CustomerDTO::fromArray($validated));
        return (new CustomerResource($item->loadMissing(array (
  0 => 'barberShop',
))))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_customers');
        $item = Customer::findOrFail($id);
        $data = $this->prepareData($request);

        if (empty($data['barber_shop_id']) && $request->user()?->barber_shop_id) {
            $data['barber_shop_id'] = $request->user()->barber_shop_id;
        }

        $validated = validator($data, Customer::rules($id))->validate();
        $item = app(\App\Domain\Customer\Actions\UpdateCustomerAction::class)->execute($item, \App\Domain\Customer\DTOs\CustomerDTO::fromArray($validated));
        return new CustomerResource($item->loadMissing(array (
  0 => 'barberShop',
)));
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_customers');

        try {
            $item = Customer::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Customer deleted.']);
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
  0 => 'photo',
) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = app(\App\Services\ImageUploadService::class)->upload($request->file($field), 'uploads/customers');
            }
        }

        return $data;
    }
}
