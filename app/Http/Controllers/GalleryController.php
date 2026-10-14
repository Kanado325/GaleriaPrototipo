<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $publications = Publication::with(['user', 'likes'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(function ($pub) {
                return [
                    'id'           => $pub->id,
                    'title'        => $pub->title,
                    'location'     => $pub->location,
                    'photographer' => $pub->user->name ?? 'Desconhecido',
                    'image_url'    => asset('storage/' . $pub->image_path),
                    'likes_count'  => $pub->likes->count(),
                    'is_liked'     => auth()->check() ? $pub->likes->where('user_id', auth()->id())->isNotEmpty() : false,
                ];
            });

        return response()->json($publications);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'fotografo') {
            return response()->json(['message' => 'Apenas fotógrafos podem cadastrar fotos.'], 403);
        }

        $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'image'    => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $imagePath = $request->file('image')->store('photos', 'public');

        $photo = Photo::create([
            'user_id'  => Auth::id(),
            'title'    => $request->title,
            'location' => $request->location,
            'image_path'    => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto publicada com sucesso!',
            'photo'   => $photo
        ], 201);
    }
}