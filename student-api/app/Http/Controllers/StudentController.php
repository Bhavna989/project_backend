<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    // ── READ ALL  →  GET /api/students ──────────────
    public function index(): JsonResponse
    {
        $students = Student::all();

        return response()->json([
            'success' => true,
            'message' => 'All students retrieved',
            'data'    => $students,
        ], 200);
    }

    // ── CREATE  →  POST /api/students ───────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:students,email',
            'course' => 'required|string|max:255',
            'age'    => 'required|integer|min:1|max:100',
        ]);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data'    => $student,
        ], 201);
    }

    // ── READ ONE  →  GET /api/students/{id} ─────────
    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $student,
        ], 200);
    }

    // ── UPDATE  →  PUT /api/students/{id} ───────────
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|email|unique:students,email,'.$student->id,
            'course' => 'sometimes|string|max:255',
            'age'    => 'sometimes|integer|min:1|max:100',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data'    => $student,
        ], 200);
    }

    // ── DELETE  →  DELETE /api/students/{id} ────────
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ], 200);
    }
}