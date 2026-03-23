<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'All students retrieved',
            'data'    => Student::all(),
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:students,email',
            'course'   => 'required|string|max:255',
            'age'      => 'required|integer|min:1|max:100',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data'    => $student,
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $student,
        ], 200);
    }

    public function update(Request $request, Student $student): JsonResponse
    {
    $validated = $request->validate([
        'name'     => 'sometimes|string|max:255',
        'email'    => 'sometimes|email|unique:students,email,' . $student->id,
        'course'   => 'sometimes|string|max:255',
        'age'      => 'sometimes|integer|min:1|max:100',
        'password' => 'sometimes|string|min:6',
    ]);

    if (isset($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    }

    $student->update($validated);
    $student->refresh(); // ← add this line

    return response()->json([
        'success' => true,
        'message' => 'Student updated successfully',
        'data'    => $student,
    ], 200);
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ], 200);
    }
}