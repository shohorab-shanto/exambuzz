<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    /**
     * Get all class routines
     */
    public function index()
    {
        $routines = ClassRoutine::where('status', 1)
            ->orderBy('type')
            ->get(['id', 'type', 'title', 'pdf_file', 'status', 'created_at', 'updated_at']);

        $data = $routines->map(function ($routine) {
            return [
                'id' => $routine->id,
                'type' => $routine->type,
                'title' => $routine->title,
                'pdf_file' => $routine->pdf_file ? asset('storage/' . $routine->pdf_file) : null,
                'status' => $routine->status,
                'created_at' => $routine->created_at,
                'updated_at' => $routine->updated_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Class routines retrieved successfully',
            'data' => $data
        ], 200);
    }

    /**
     * Get routine by type (preliminary or written)
     */
    public function getByType($type)
    {
        if (!in_array($type, ['preliminary', 'written'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid type. Must be "preliminary" or "written"'
            ], 400);
        }

        $routine = ClassRoutine::where('type', $type)
            ->where('status', 1)
            ->first(['id', 'type', 'title', 'pdf_file', 'status', 'created_at', 'updated_at']);

        if (!$routine) {
            return response()->json([
                'status' => false,
                'message' => ucfirst($type) . ' exam routine not found'
            ], 404);
        }

        $data = [
            'id' => $routine->id,
            'type' => $routine->type,
            'title' => $routine->title,
            'pdf_file' => $routine->pdf_file ? asset('storage/' . $routine->pdf_file) : null,
            'status' => $routine->status,
            'created_at' => $routine->created_at,
            'updated_at' => $routine->updated_at,
        ];

        return response()->json([
            'status' => true,
            'message' => ucfirst($type) . ' exam routine retrieved successfully',
            'data' => $data
        ], 200);
    }
}
