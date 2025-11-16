<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends Controller
{
    public function index()
    {
        $apartments = Apartment::where('status', 'available')
            ->orWhere('status', 'booked')
            ->with('images')
            ->get();

        return view('apartments.index', ['apartments' => $apartments]);
    }

    public function show(Apartment $apartment)
    {
        $apartment->load('images', 'reviews.user', 'owner');
        return view('apartments.show', ['apartment' => $apartment]);
    }

    

    public function search(Request $request)
    {
        $query = Apartment::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        }

        if ($request->has('check_in') && $request->has('check_out')) {
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in', '<=', $checkIn)
                                    ->where('check_out', '>=', $checkOut);
                            });
                    });
            });
        }

        if ($request->has('guests')) {
            $guests = $request->input('guests');
            $query->where('max_guests', '>=', $guests);
        }

        return response()->json($query->with('images')->get());
    }

    public function apiIndex(Request $request)
    {
        $query = Apartment::with('images', 'reviews.user');
        
        // For admin, include current booking with user
        if (auth()->check() && auth()->user()->role === 'admin') {
            $query->with('currentBooking.user');
        }
        
        $apartments = $query->get()
            ->map(function($apt) {
                return $this->formatApartment($apt);
            });

        return response()->json($apartments);
    }

    public function apiShow($id)
    {
        $apartment = Apartment::with('images', 'reviews.user', 'owner')->findOrFail($id);
        return response()->json($this->formatApartment($apartment));
    }

    public function apiSearch(Request $request)
    {
        $query = Apartment::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        }

        if ($request->has('check_in') && $request->has('check_out')) {
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($query) use ($checkIn, $checkOut) {
                        $query->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in', '<=', $checkIn)
                                    ->where('check_out', '>=', $checkOut);
                            });
                    });
            });
        }

        if ($request->has('guests')) {
            $guests = $request->input('guests');
            $query->where('max_guests', '>=', $guests);
        }

        $apartments = $query->with('images', 'reviews.user')
            ->get()
            ->map(function($apt) {
                return $this->formatApartment($apt);
            });

        return response()->json($apartments);
    }

     private function formatApartment($apartment)
    {
        $formatted = [
            'id' => $apartment->id,
            'name' => $apartment->name,
            'address' => $apartment->address,
            'price' => $apartment->price_per_night,
            'price_per_night' => $apartment->price_per_night,
            'rooms' => $apartment->rooms,
            'total_area' => $apartment->total_area,
            'kitchen_area' => $apartment->kitchen_area,
            'living_area' => $apartment->living_area,
            'floor' => $apartment->floor,
            'max_guests' => $apartment->max_guests,
            'balcony' => $apartment->balcony,
            'furniture' => $apartment->furniture, // Теперь это строка
            'appliances' => $apartment->appliances, // Теперь это строка
            'internet' => $apartment->has_internet,
            'has_internet' => $apartment->has_internet,
            'bathroom' => $apartment->bathroom,
            'repair' => $apartment->renovation,
            'renovation' => $apartment->renovation,
            'deposit' => $apartment->deposit,
            'commission' => $apartment->commission,
            'counters' => $apartment->meter_based,
            'meter_based' => $apartment->meter_based,
            'other_utilities' => $apartment->other_utilities,
            'children_allowed' => $apartment->allows_children,
            'allows_children' => $apartment->allows_children,
            'pets_allowed' => $apartment->allows_pets,
            'allows_pets' => $apartment->allows_pets,
            'smoking_allowed' => $apartment->allows_smoking,
            'allows_smoking' => $apartment->allows_smoking,
            'description' => $apartment->description,
            'latitude' => $apartment->latitude,
            'longitude' => $apartment->longitude,
            'status' => $apartment->status,
            'rating' => $apartment->reviews->avg('rating') ?? 0,
            'reviews_count' => $apartment->reviews->count(),
            'images' => $apartment->images->map(function($img) {
                // Формируем правильный URL для изображения
                $path = $img->image_path;
                
                // Если путь уже содержит полный URL, возвращаем его
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return [
                        'id' => $img->id,
                        'url' => $path,
                        'image_path' => $path
                    ];
                }
                
                // Убираем 'storage/' из начала, если есть
                $path = ltrim($path, 'storage/');
                $path = ltrim($path, '/');
                
                // Используем asset() для формирования URL относительно public
                $url = asset('storage/' . $path);
                
                return [
                    'id' => $img->id,
                    'url' => $url,
                    'image_path' => $img->image_path
                ];
            }),
            'reviews' => $apartment->reviews->map(fn($rev) => [
                'id' => $rev->id,
                'user' => ['id' => $rev->user->id, 'name' => $rev->user->name],
                'rating' => $rev->rating,
                'text' => $rev->comment, // Исправлено с text на comment
                'created_at' => $rev->created_at
            ]),
        ];
        
        // Add current booking info for admin
        if ($apartment->relationLoaded('currentBooking') && $apartment->currentBooking) {
            $formatted['current_booking'] = [
                'id' => $apartment->currentBooking->id,
                'user' => [
                    'id' => $apartment->currentBooking->user->id,
                    'first_name' => $apartment->currentBooking->user->first_name,
                    'last_name' => $apartment->currentBooking->user->last_name,
                    'patronymic' => $apartment->currentBooking->user->patronymic,
                ]
            ];
        }
        
        return $formatted;
    }

    private function translateBalcony($value)
    {
        $translations = [
            'none' => 'нет',
            'balcony' => 'балкон',
            'loggia' => 'лоджия',
        ];
        return $translations[$value] ?? $value;
    }

    private function translateBathroom($value)
    {
        $translations = [
            'shared' => 'совмещенный',
            'private' => 'раздельный',
            'multiple' => 'несколько',
        ];
        return $translations[$value] ?? $value;
    }
}
