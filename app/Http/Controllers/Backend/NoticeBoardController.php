<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\NoticeBoard;
use Illuminate\Http\Request;

class NoticeBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function all_notice_board()
    {
        $material = NoticeBoard::where('status', 1)->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $material,
        ]);
    }

    public function index()
    {
        $material = NoticeBoard::orderBy('created_at', 'desc')->paginate(15);

        return view('backend.notice-board.index', compact('material'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.notice-board.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'status' => 'required|string',
        ]);

        NoticeBoard::create($request->all());

        return redirect()->route('notice-board.index')->withToastSuccess('Material folder created successfully');
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
        $material = NoticeBoard::where('id', $id)->first();

        if (!isset($material)) {
            return back()->withToastSuccess('Material folder not found');
        }

        return view('backend.notice-board.create', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $material = NoticeBoard::where('id', $id)->first();

        if (!isset($material)) {
            return back()->withToastSuccess('Material folder not found');
        }

        NoticeBoard::where('id', $id)->update($request->except('_token', '_method'));

        return redirect()->route('notice-board.index')->withToastSuccess('Material folder deleted successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        NoticeBoard::destroy($id);
        return redirect()->route('notice-board.index')->withToastSuccess('Material folder deleted successfully');
    }
}
