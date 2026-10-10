<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\SubscriptionRenewalResource;
use App\Models\SubscriptionRenewal;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionRenewalController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);
        $sortField = (string) $request->input('sort', 'id');
        $direction = strtolower((string) $request->input('direction', 'desc'));
        $allowedSorts = SubscriptionRenewal::sortable();
        $sortField = in_array($sortField, $allowedSorts, true) ? $sortField : 'id';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        $query = SubscriptionRenewal::query()->with(['barberShop', 'plan']);

        $user = auth()->user();
        if (!$user->is_global_admin) {
            $query->where('barber_shop_id', $user->barber_shop_id);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy($sortField, $direction)->paginate($perPage)->withQueryString();
        return SubscriptionRenewalResource::collection($items);
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = SubscriptionRenewal::with(['barberShop', 'plan']);

        if (!$user->is_global_admin) {
            $query->where('barber_shop_id', $user->barber_shop_id);
        }

        $item = $query->findOrFail($id);
        return new SubscriptionRenewalResource($item);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $data = $this->prepareData($request);

        if (!$user->is_global_admin) {
            $data['barber_shop_id'] = $user->barber_shop_id;
            $data['status'] = 'pending';
        }

        if (empty($data['amount']) && !empty($data['plan_id'])) {
            $plan = Plan::find($data['plan_id']);
            if ($plan) {
                $data['amount'] = $plan->price ?? 0;
            }
        }

        $validated = validator($data, SubscriptionRenewal::rules())->validate();
        $item = app(\App\Domain\SubscriptionRenewal\Actions\CreateSubscriptionRenewalAction::class)
            ->execute(\App\Domain\SubscriptionRenewal\DTOs\SubscriptionRenewalDTO::fromArray($validated));

        return (new SubscriptionRenewalResource($item->loadMissing(['barberShop', 'plan'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $query = SubscriptionRenewal::query();

        if (!$user->is_global_admin) {
            $query->where('barber_shop_id', $user->barber_shop_id);
        }

        $item = $query->findOrFail($id);
        $data = $this->prepareData($request);

        if (!$user->is_global_admin) {
            $data['barber_shop_id'] = $user->barber_shop_id;
            $data['status'] = $item->status; // Non-admins cannot self-approve
        }

        $validated = validator($data, SubscriptionRenewal::rules($id))->validate();
        $item = app(\App\Domain\SubscriptionRenewal\Actions\UpdateSubscriptionRenewalAction::class)
            ->execute($item, \App\Domain\SubscriptionRenewal\DTOs\SubscriptionRenewalDTO::fromArray($validated));

        return new SubscriptionRenewalResource($item->loadMissing(['barberShop', 'plan']));
    }

    public function destroy($id): JsonResponse
    {
        $user = auth()->user();
        $query = SubscriptionRenewal::query();

        if (!$user->is_global_admin) {
            $query->where('barber_shop_id', $user->barber_shop_id);
        }

        try {
            $item = $query->findOrFail($id);
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Subscription Renewal request deleted.']);
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

        if ($request->hasFile('transfer_document')) {
            $data['transfer_document'] = app(\App\Services\ImageUploadService::class)
                ->upload($request->file('transfer_document'), 'uploads/subscription-renewals');
        }

        return $data;
    }
}
