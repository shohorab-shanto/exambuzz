<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialFolder;
use Illuminate\Http\Request;

class MaterialFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $material = MaterialFolder::with('parent')->latest()->paginate(15);

        return view('backend.material.folder.index', compact('material'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show all folders as potential parents for multi-level nesting
        $folders = MaterialFolder::orderBy('name')->get();
        return view('backend.material.folder.create', compact('folders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string|max:255',
            'name' => 'required|max:255',
            'status' => 'required|string',
        ]);

        MaterialFolder::create([
            'type' => $validatedData['type'],
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
            'parent_id' => $request->input('parent_id', null), // Handle parent_id if provided
        ]);

        return redirect()->route('material.folder.index')->withToastSuccess('Material folder created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $material = MaterialFolder::where('id', $id)->first();

        if (!isset($material)) {
            return back()->withToastError('Material folder not found');
        }

        // Show all folders except current one (to prevent circular reference)
        $folders = MaterialFolder::where('id', '!=', $id)
            ->orderBy('name')
            ->get();

        return view('backend.material.folder.create', compact('material', 'folders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $material = MaterialFolder::where('id', $id)->first();

        if (!isset($material)) {
            return back()->withToastError('Material folder not found');
        }

        MaterialFolder::where('id', $id)->update([
            'type' => $request->input('type'),
            'name' => $request->input('name'),
            'status' => $request->input('status'),
            'parent_id' => $request->input('parent_id', null), // Handle parent_id if provided
        ]);

        return redirect()->route('material.folder.index')->withToastSuccess('Material folder updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        MaterialFolder::destroy($id);
        return redirect()->route('material.folder.index')->withToastSuccess('Material folder deleted successfully');
    }
}
