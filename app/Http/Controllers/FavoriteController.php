<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Apartment $apartment)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('apartment_id', $apartment->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        }

        Favorite::create([
            'user_id' => Auth::id(),
            'apartment_id' => $apartment->id,
        ]);

        return response()->json(['status' => 'added']);
    }

    public function list()
    {
        $favorites = Auth::user()->favorites()
            ->with('apartment.images')
            ->get();

        return view('favorites.index', ['favorites' => $favorites]);
    }
}
