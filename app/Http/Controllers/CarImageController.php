<?php

namespace App\Http\Controllers;

use App\Models\CarImage;
use Illuminate\Http\Request;

class CarImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $images = CarImage::where("driver_id" , $id)->get();
    return response()->json($images);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $uploadedFiles = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                
                $filePath = $file->store('images', 'public');
                $fileNmae = $file->getClientOriginalName();
                // Save file information to the database
                $image = new CarImage();
                $image->name = $filePath;
                $image->driver_id = $request->driver_id;
                $image->save();

                $uploadedFiles[] = $image;
            }

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully!',
                'files' => $uploadedFiles
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No images found to upload.'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CarImage $carImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CarImage $carImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CarImage $carImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $image = CarImage::find($id);
        if ($image) {
           
            \Storage::disk('public')->delete($image->name);

           
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }
        return response()->json(['success' => false, 'message' => 'Image not found.'], 404);
    }
}
