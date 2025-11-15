<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'price_per_night', 'rooms', 'total_area',
        'kitchen_area', 'living_area', 'floor', 'balcony', 'bathroom',
        'renovation', 'furniture', 'appliances', 'has_internet', // Изменено: furniture, appliances вместо has_furniture, has_appliances
        'deposit', 'commission', 'meter_based', 'other_utilities',
        'max_guests', 'allows_children', 'allows_pets', 'allows_smoking',
        'description', 'rating', 'review_count', 'latitude', 'longitude',
        'owner_id', 'status'
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'deposit' => 'decimal:2',
        'commission' => 'decimal:2',
        'rating' => 'decimal:2',
        'has_internet' => 'boolean',
        'meter_based' => 'boolean',
        'allows_children' => 'boolean',
        'allows_pets' => 'boolean',
        'allows_smoking' => 'boolean',
        // Убраны: 'has_furniture' => 'boolean', 'has_appliances' => 'boolean'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(ApartmentImage::class)->orderBy('order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function currentBooking()
    {
        return $this->hasOne(Booking::class)
            ->where('status', 'paid')
            ->where('check_out', '>=', now())
            ->where('check_in', '<=', now())
            ->latest();
    }

    // Accessor для удобного получения массива мебели
    public function getFurnitureListAttribute()
    {
        return $this->furniture ? explode(', ', $this->furniture) : [];
    }

    // Accessor для удобного получения массива техники
    public function getAppliancesListAttribute()
    {
        return $this->appliances ? explode(', ', $this->appliances) : [];
    }
}