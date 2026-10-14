<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Like;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, $id)
    {
        $user = auth()->user();
        $publication = Publication::findOrFail($id);

        $like = Like::where('user_id', $user->id)
                    ->where('publication_id', $publication->id)
                    ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'publication_id' => $publication->id,
            ]);
            $isLiked = true;
        }

        return response()->json([
            'is_liked' => $isLiked,
            'likes_count' => $publication->likes()->count(),
        ]);
    }
}