<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentTraining;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class TrainingOptController extends Controller
{
    public function optInOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'training_schedule_id' => 'required|exists:training_schedules,id',
                'status' => 'required|in:opt-in,opt-out',
            ]);

            // Update or create pivot record
            $studentTraining = StudentTraining::updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'training_schedule_id' => $validated['training_schedule_id']
                ],
                [
                    'status' => $validated['status']
                ]
            );

            return response()->json([
                'message' => 'Student training status updated successfully',
                'data' => $studentTraining
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            // This catch might be redundant since validation should already cover missing related records.
            return response()->json([
                'message' => 'Student or training schedule not found',
                'error' => 'The specified student or training schedule does not exist'
            ], 404);
        } catch (Exception $e) {
            // Log the exception if you want to debug later (not shown here)
            return response()->json([
                'message' => 'An error occurred while updating training status',
                'error' => 'Internal server error'
            ], 500);
        }
    }

    public function statusList(Request $request)
    {
        try {
            $student_id = $request->query('student_id');
            if (!$student_id) {
                return response()->json(['message' => 'student_id is required'], 400);
            }

            $statuses = StudentTraining::where('student_id', $student_id)
                ->get(['training_schedule_id', 'status']);

            return response()->json($statuses, 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve training statuses',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
