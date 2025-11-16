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

    public function apiToggle($apartmentId)
    {
        try {
            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Обновляем сессию чтобы она не истекала
            if (request()->hasSession()) {
                request()->session()->put('_last_activity', now()->timestamp);
            }
            
            $apartment = Apartment::findOrFail($apartmentId);
            
            $favorite = Favorite::where('user_id', Auth::id())
                ->where('apartment_id', $apartment->id)
                ->first();

            if ($favorite) {
                $favorite->delete();
                return response()->json(['status' => 'removed'])
                    ->header('X-CSRF-TOKEN', csrf_token());
            }

            Favorite::create([
                'user_id' => Auth::id(),
                'apartment_id' => $apartment->id,
            ]);

            return response()->json(['status' => 'added'])
                ->header('X-CSRF-TOKEN', csrf_token());
        } catch (\Exception $e) {
            \Log::error('Favorite toggle error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function apiList()
    {
        try {
            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Обновляем сессию чтобы она не истекала
            if (request()->hasSession()) {
                request()->session()->put('_last_activity', now()->timestamp);
            }
            
            $favorites = Auth::user()->favorites()
            ->with('apartment.images')
            ->get()
            ->map(function($fav) {
                return [
                    'id' => $fav->id,
                    'apartment_id' => $fav->apartment_id,
                    'apartment' => $fav->apartment ? [
                        'id' => $fav->apartment->id,
                        'name' => $fav->apartment->name,
                        'address' => $fav->apartment->address,
                        'price_per_night' => $fav->apartment->price_per_night,
                        'images' => $fav->apartment->images->map(function($img) {
                            $path = $img->image_path;
                            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                                return ['id' => $img->id, 'url' => $path];
                            }
                            $path = ltrim($path, '/');
                            if (str_starts_with($path, 'images/apartments/')) {
                                // Для путей в public используем прямой путь
                                $url = '/' . $path;
                            } else {
                                $path = ltrim($path, 'storage/');
                                $url = asset('storage/' . $path);
                            }
                            return ['id' => $img->id, 'url' => $url];
                        })
                    ] : null
                ];
            });

            return response()->json($favorites)
                ->header('X-CSRF-TOKEN', csrf_token());
        } catch (\Exception $e) {
            \Log::error('Favorite list error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        $favorites = Auth::user()->favorites()
            ->with('apartment.images')
            ->get();

        return view('favorites.index', ['favorites' => $favorites]);
    }
}
