<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medias;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    //
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 3);
        $sildes = Slide::with('media')->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $sildes
        ], 200);

    }

    public function getSlidesEnable()
    {
        $sildes = Slide::with('media')->orderBy('created_at', 'desc')->where('is_active', true)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $sildes
        ], 200);
    }
    public function store(Request $request)
    {

        $validator = \Validator::make($request->all(), [

            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'errors' => $validator->errors()->first(),
            ], 422);
        }

        $slide= Slide::create($validator->validated());
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $directory = 'Slider' . '/' . now()->format('y') . '.' . now()->format('m');
            $path = $image->store($directory, 'public');
            Medias::create([
                'mediable_id' => $slide->id,
                'mediable_type' => Slide::class,
                'file_name' => $image->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'type' => 'slide'
            ]);
        }
        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'image ajouter avec success',
            'data' => $slide->load('media')
        ], 201);
    }

    public function show(Slide $slide)
    {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $slide->load('media')
        ], 200);
    }

    public function update(Request $request, Slide $slide)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'errors' => $validator->errors()->first(),
            ], 422);
        }

        $slide->update($validator->validated());

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $directory = 'Slider' . '/' . now()->format('y') . '.' . now()->format('m');
            $path = $image->store($directory, 'public');

            $existingMedia = $slide->media()->first();
            if ($existingMedia) {
                \Storage::disk('public')->delete($existingMedia->file_path);
                $existingMedia->update([
                    'file_name' => $image->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                ]);
            } else {
                Medias::create([
                    'mediable_id' => $slide->id,
                    'mediable_type' => Slide::class,
                    'file_name' => $image->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'type' => 'slide'
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Slide mis à jour avec succès',
            'data' => $slide->load('media')
        ], 200);
    }

    public function destroy(Slide $slide)
    {
        foreach ($slide->media as $media) {
            \Storage::disk('public')->delete($media->file_path);
            $media->delete();
        }
        $slide->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Slide supprimé avec succès'
        ], 200);
    }

    public function enable(Slide $slide)
    {
        $slide->update([
            'is_active' => true
        ]);
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'image activer avec success',
            'data' => $slide
        ], 200);
    }
    public function disable(Slide $slide)
    {
        $slide->update([
            'is_active' => false
        ]);
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'image desactiver avec success',
            'data' => $slide
        ], 200);
    }
}
