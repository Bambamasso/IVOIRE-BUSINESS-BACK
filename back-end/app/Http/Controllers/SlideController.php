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
    public function Store(Request $request)
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
        if ($request->hasFile('image')) { {
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

        }
        return response()->json([
            'status' => 'success',
            'code' => 201,
            'message' => 'image ajouter avec success',
            'data' => $slide->load('media')
        ], 201);
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
