<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Validation\ValidationException;
use Exception;

class CourseController extends Controller
{
    public function index()
    {
        try {
            return response()->json(Course::all());
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch courses.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration'    => 'required|integer|min:1',
            ]);

            $course = Course::create($data);

            return response()->json($course, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Course creation failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            return response()->json($course);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch course.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            $data = $request->validate([
                'name'        => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'duration'    => 'sometimes|required|integer|min:1',
            ]);

            $course->update($data);

            return response()->json($course);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Update failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['message' => 'Course not found'], 404);
            }

            $course->delete();

            return response()->json(['message' => 'Course deleted successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Delete failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
