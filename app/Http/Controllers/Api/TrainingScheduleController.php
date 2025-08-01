<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingSchedule;
use Illuminate\Validation\ValidationException;
use Exception;

class TrainingScheduleController extends Controller
{
    public function index()
    {
        try {
            // Eager load related Course
            $schedules = TrainingSchedule::with('course')->get();
            return response()->json($schedules);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch training schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'start_date' => 'required|date|before_or_equal:end_date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location' => 'nullable|string|max:255',
            ]);

            $schedule = TrainingSchedule::create($data);

            return response()->json($schedule, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create training schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $schedule = TrainingSchedule::with('course')->find($id);

            if (!$schedule) {
                return response()->json(['message' => 'Training Schedule not found'], 404);
            }

            return response()->json($schedule);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch training schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $schedule = TrainingSchedule::find($id);

            if (!$schedule) {
                return response()->json(['message' => 'Training Schedule not found'], 404);
            }

            $data = $request->validate([
                'course_id' => 'sometimes|required|exists:courses,id',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'location' => 'nullable|string|max:255',
            ]);

            // Validate date logic carefully especially if only one date is provided
            $startDate = $data['start_date'] ?? $schedule->start_date;
            $endDate = $data['end_date'] ?? $schedule->end_date;

            if (strtotime($startDate) > strtotime($endDate)) {
                return response()->json(['message' => 'start_date must be before or equal to end_date'], 422);
            }

            $schedule->update($data);

            return response()->json($schedule);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update training schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = TrainingSchedule::find($id);

            if (!$schedule) {
                return response()->json(['message' => 'Training Schedule not found'], 404);
            }

            $schedule->delete();

            return response()->json(['message' => 'Training Schedule deleted successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete training schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
