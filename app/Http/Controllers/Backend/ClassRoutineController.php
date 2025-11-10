<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ClassRoutine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassRoutineController extends Controller
{
    /**
     * Display a listing of class routines
     */
    public function index()
    {
        $routines = ClassRoutine::orderBy('type')->get();
        return view('backend.class-routine.index', compact('routines'));
    }

    /**
     * Show the form for creating/editing a routine
     */
    public function createOrEdit($type)
    {
        $routine = ClassRoutine::where('type', $type)->first();
        return view('backend.class-routine.create-edit', compact('routine', 'type'));
    }

    /**
     * Store or update a routine
     */
    public function store(Request $request)
    {
        $routine = ClassRoutine::where('type', $request->type)->first();
        
        // Validation rules - PDF required for new uploads, optional for updates
        $rules = [
            'type' => 'required|in:preliminary,written',
            'title' => 'nullable|string|max:255',
            'pdf_file' => ($routine ? 'nullable' : 'required') . '|file|mimes:pdf|max:2048', // Max 2MB (based on PHP upload_max_filesize)
            'status' => 'required|boolean',
        ];

        $request->validate($rules);

        $data = [
            'type' => $request->type,
            'title' => $request->title,
            'status' => $request->status,
        ];

        // Handle PDF upload
        if ($request->hasFile('pdf_file')) {
            // Delete old file if exists
            if ($routine && $routine->pdf_file) {
                Storage::disk('public')->delete($routine->pdf_file);
            }

            $file = $request->file('pdf_file');
            $filename = 'class_routine_' . $request->type . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            try {
                $path = $file->storeAs('class_routines', $filename, 'public');
                $data['pdf_file'] = $path;
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to upload PDF file: ' . $e->getMessage())->withInput();
            }
        }

        if ($routine) {
            // Update existing
            $routine->update($data);
            $message = 'Class routine updated successfully!';
        } else {
            // Create new
            ClassRoutine::create($data);
            $message = 'Class routine created successfully!';
        }

        return redirect()->route('class-routine.index')->with('success', $message);
    }

    /**
     * Remove the specified routine
     */
    public function destroy($type)
    {
        $routine = ClassRoutine::where('type', $type)->first();

        if ($routine) {
            // Delete PDF file
            if ($routine->pdf_file) {
                Storage::disk('public')->delete($routine->pdf_file);
            }

            $routine->delete();
            return redirect()->route('class-routine.index')->with('success', 'Class routine deleted successfully!');
        }

        return redirect()->route('class-routine.index')->with('error', 'Class routine not found!');
    }

    /**
     * Toggle status
     */
    public function toggleStatus($type)
    {
        $routine = ClassRoutine::where('type', $type)->first();

        if ($routine) {
            $routine->update(['status' => !$routine->status]);
            $status = $routine->status ? 'activated' : 'deactivated';
            return redirect()->route('class-routine.index')->with('success', "Class routine {$status} successfully!");
        }

        return redirect()->route('class-routine.index')->with('error', 'Class routine not found!');
    }
}
