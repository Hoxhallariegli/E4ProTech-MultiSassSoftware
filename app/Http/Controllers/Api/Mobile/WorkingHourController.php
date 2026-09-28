<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\WorkingHourResource;
use App\Models\WorkingHour;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkingHourController extends Controller
{
    /**
     * Get weekly schedule for a specific barber.
     */
    public function index(Request $request)
    {
        abort_if_cannot('view_working_hours');

        $barberId = $request->input('barber_id');
        if (!$barberId) {
            $barberId = Barber::value('id');
        }

        if (!$barberId) {
            return response()->json(['data' => []]);
        }

        $existingHours = WorkingHour::where('barber_id', $barberId)->get()->keyBy('day_of_week');
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedule = [];

        foreach ($days as $day) {
            if ($existingHours->has($day)) {
                $schedule[] = $existingHours->get($day);
            } else {
                $schedule[] = new WorkingHour([
                    'id' => null,
                    'barber_id' => (int)$barberId,
                    'day_of_week' => $day,
                    'open_time' => null,
                    'close_time' => null,
                    'lunch_start' => null,
                    'lunch_end' => null,
                    'is_closed' => true
                ]);
            }
        }

        return WorkingHourResource::collection($schedule);
    }

    /**
     * Bulk save schedule for a barber.
     */
    public function store(Request $request)
    {
        abort_if_cannot('add_working_hours');

        $barberId = $request->input('barber_id');
        $daysData = $request->input('days', []);

        if (!$barberId) {
            return response()->json(['success' => false, 'message' => 'Barber ID is required'], 422);
        }

        $savedItems = [];

        foreach ($daysData as $dayName => $data) {
            $isClosed = filter_var($data['is_closed'] ?? true, FILTER_VALIDATE_BOOLEAN);

            $item = WorkingHour::updateOrCreate(
                [
                    'barber_id' => $barberId,
                    'day_of_week' => $dayName
                ],
                [
                    'open_time' => $isClosed ? null : ($data['open_time'] ?? null),
                    'close_time' => $isClosed ? null : ($data['close_time'] ?? null),
                    'lunch_start' => $isClosed ? null : (!empty($data['lunch_start']) ? $data['lunch_start'] : null),
                    'lunch_end' => $isClosed ? null : (!empty($data['lunch_end']) ? $data['lunch_end'] : null),
                    'is_closed' => $isClosed
                ]
            );
            $savedItems[] = $item;
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly schedule updated successfully',
            'data' => WorkingHourResource::collection($savedItems)
        ]);
    }

    public function show($id)
    {
        abort_if_cannot('view_working_hours');
        $item = WorkingHour::findOrFail($id);
        return new WorkingHourResource($item);
    }

    public function update(Request $request, $id)
    {
        abort_if_cannot('edit_working_hours');
        $item = WorkingHour::findOrFail($id);
        $isClosed = $request->boolean('is_closed');

        $item->update([
            'open_time' => $isClosed ? null : $request->input('open_time'),
            'close_time' => $isClosed ? null : $request->input('close_time'),
            'lunch_start' => $isClosed ? null : $request->input('lunch_start'),
            'lunch_end' => $isClosed ? null : $request->input('lunch_end'),
            'is_closed' => $isClosed
        ]);

        return new WorkingHourResource($item);
    }

    public function destroy($id): JsonResponse
    {
        abort_if_cannot('delete_working_hours');
        $item = WorkingHour::findOrFail($id);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'WorkingHour deleted.']);
    }
}
