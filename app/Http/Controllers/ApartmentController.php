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
        try {
            // Обновляем сессию
            if (request()->hasSession()) {
                request()->session()->put('_last_activity', now()->timestamp);
            }
            
            $apartment = Apartment::with('images', 'reviews.user', 'owner')->findOrFail($id);
            return response()->json($this->formatApartment($apartment))
                ->header('X-CSRF-TOKEN', csrf_token());
        } catch (\Exception $e) {
            \Log::error('Apartment apiShow error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Apartment not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function apiSearch(Request $request)
    {
        $query = Apartment::query();
        
        // Фильтруем только доступные апартаменты
        $query->where('status', 'available');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
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
            'balcony' => $this->translateBalcony($apartment->balcony),
            'furniture' => $this->translateFurniture($apartment->furniture),
            'appliances' => $this->translateAppliances($apartment->appliances),
            'internet' => $apartment->has_internet,
            'has_internet' => $apartment->has_internet,
            'bathroom' => $this->translateBathroom($apartment->bathroom),
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
                
                // Убираем лишние слеши
                $path = ltrim($path, '/');
                
                // Если путь начинается с images/apartments, используем его напрямую
                if (str_starts_with($path, 'images/apartments/')) {
                    // Для путей в public используем прямой путь
                    $url = '/' . $path;
                } else {
                    // Для старых путей из storage
                    $path = ltrim($path, 'storage/');
                    $url = asset('storage/' . $path);
                }
                
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

    private function translateFurniture($value)
    {
        if (empty($value)) return 'нет';
        
        // Если это строка с запятыми, разбиваем и переводим
        if (is_string($value)) {
            $items = array_map('trim', explode(',', $value));
            $translations = [
                'bed' => 'кровать',
                'wardrobe' => 'шкаф',
                'sofa' => 'диван',
                'table' => 'стол',
                'chairs' => 'стулья',
                'dresser' => 'комод',
                'nightstand' => 'тумбочка',
                'bookshelf' => 'книжная полка',
                'tv_stand' => 'ТВ-тумба',
                'armchair' => 'кресло'
            ];
            
            $translated = array_map(function($item) use ($translations) {
                return $translations[trim($item)] ?? trim($item);
            }, $items);
            
            return implode(', ', $translated);
        }
        
        return $value;
    }

    private function translateAppliances($value)
    {
        if (empty($value)) return 'нет';
        
        // Если это строка с запятыми, разбиваем и переводим
        if (is_string($value)) {
            $items = array_map('trim', explode(',', $value));
            $translations = [
                'refrigerator' => 'холодильник',
                'stove' => 'плита',
                'oven' => 'духовка',
                'microwave' => 'микроволновка',
                'dishwasher' => 'посудомойка',
                'washing_machine' => 'стиральная машина',
                'tv' => 'телевизор',
                'air_conditioner' => 'кондиционер',
                'water_heater' => 'водонагреватель',
                'coffee_maker' => 'кофемашина'
            ];
            
            $translated = array_map(function($item) use ($translations) {
                return $translations[trim($item)] ?? trim($item);
            }, $items);
            
            return implode(', ', $translated);
        }
        
        return $value;
    }
}
