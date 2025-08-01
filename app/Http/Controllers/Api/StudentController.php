<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class StudentController extends Controller
{
    /**
     * Display a list of all students with their user info.
     */
    public function index(Request $request)
    {
        try {
            $query = Student::with('user');

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $students = $query->get();
            return response()->json($students);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch students.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created student and user.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'      => 'required|string|max:255', // for users table
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|string|confirmed|min:6',

                'first_name'=> 'required|string|max:255', // for students table
                'last_name' => 'required|string|max:255',
                'phone'     => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'student', // Assign student role
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User and Student created successfully',
                'user' => $user,
                'student' => $student->load('user'),
            ], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create user and student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific student with user info.
     */
    public function show($id)
    {
        try {
            $student = Student::with('user')->find($id);

            if (!$student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            return response()->json($student);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a specific student and user details.
     */
    public function update(Request $request, $id)
    {
        try {
            $student = Student::with('user')->find($id);
            if (!$student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            $data = $request->validate([
                'first_name' => 'sometimes|required|string|max:255',
                'last_name'  => 'sometimes|required|string|max:255',
                'phone'      => 'nullable|string|max:20',

                'name'      => 'sometimes|required|string|max:255', // user name
                'email'     => 'sometimes|required|email|unique:users,email,' . $student->user->id,
                'password'  => 'sometimes|nullable|string|min:6|confirmed',
            ]);

            DB::beginTransaction();

            if (isset($data['name']) || isset($data['email']) || array_key_exists('password', $data)) {
                $userData = [];
                if (isset($data['name'])) $userData['name'] = $data['name'];
                if (isset($data['email'])) $userData['email'] = $data['email'];
                if (!empty($data['password'])) $userData['password'] = Hash::make($data['password']);
                $student->user->update($userData);
            }

            $studentData = array_filter($data, fn($key) => in_array($key, ['first_name', 'last_name', 'phone']), ARRAY_FILTER_USE_KEY);
            if (!empty($studentData)) {
                $student->update($studentData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Student and user updated successfully',
                'student' => $student->fresh('user'),
            ]);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update student and user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student by user_id.
     */
    public function getByUserId($userId)
    {
        try {
            $student = Student::with('user')->where('user_id', $userId)->first();

            if (!$student) {
                return response()->json(['message' => 'Student not found for this user'], 404);
            }

            return response()->json($student);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch student by user ID.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a student record for an existing user.
     */
    public function createForUser(Request $request, $userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $existingStudent = Student::where('user_id', $userId)->first();
            if ($existingStudent) {
                return response()->json(['message' => 'Student record already exists for this user'], 409);
            }

            $data = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'phone'      => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            $student = Student::create([
                'user_id'    => $userId,
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'] ?? null,
                'email'      => $user->email,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully for existing user',
                'student' => $student->load('user'),
            ], 201);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create student for user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a student and the linked user.
     */
    public function destroy($id)
    {
        try {
            $student = Student::with('user')->find($id);

            if (!$student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            DB::beginTransaction();

            $student->user->delete();

            DB::commit();

            return response()->json(['message' => 'Student and user deleted successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete student and user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
