<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {


        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $destinationPath = public_path('uploads');

                // Create directory if not exists
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);


                $document = new Document;
                $document->driver_id = $request->driver_id;
                $document->file_name = $filename;
                $document->original_name = $file->getClientOriginalName();
                $document->save();
            }
        }

        return back()->with('success', 'Documents uploaded successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
   public function download($id)
{
    $document = Document::findOrFail($id);
    $filePath = public_path('uploads/' . $document->file_name);

    if (file_exists($filePath)) {
        return Response::download($filePath, $document->original_name);
    }

    return redirect()->back()->with('error', 'File not found.');
}

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
{
    $document = Document::findOrFail($id);
    $filePath = public_path('uploads/' . $document->file_name);

    if (File::exists($filePath)) {
        File::delete($filePath);
    }

    $document->delete();

    return back()->with('success', 'Document deleted successfully.');
}
}
