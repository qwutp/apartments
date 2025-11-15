<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function apartments()
    {
        $apartments = Apartment::with('owner')->get();
        return view('admin.apartments.index', ['apartments' => $apartments]);
    }

    public function calendar()
    {
        $apartments = Apartment::all();
        return view('admin.calendar', ['apartments' => $apartments]);
    }

    public function notifications()
    {
        $bookings = Booking::where('status', 'paid')
            ->with('user', 'apartment')
            ->orderByDesc('paid_at')
            ->get();

        return view('admin.notifications', ['bookings' => $bookings]);
    }

    public function renters()
    {
        $users = User::where('role', 'user')
            ->withCount('bookings')
            ->get();

        return view('admin.renters', ['users' => $users]);
    }
}
