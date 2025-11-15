<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\ApartmentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentAdminController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('ApartmentAdminController::store called', [
            'user_id' => auth()->id(),
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0
        ]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'price_per_night' => 'required|numeric|min:0',
                'rooms' => 'required|integer|min:1',
                'total_area' => 'required|numeric|min:0',
                'kitchen_area' => 'nullable|numeric|min:0',
                'living_area' => 'nullable|numeric|min:0',
                'floor' => 'nullable|integer',
                'balcony' => 'nullable|string',
                'bathroom' => 'nullable|string',
                'renovation' => 'nullable|string',
                'furniture' => 'nullable|string',
                'appliances' => 'nullable|string',
                'deposit' => 'nullable|numeric|min:0',
                'commission' => 'nullable|numeric|min:0',
                'other_utilities' => 'nullable|string',
                'max_guests' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $apartmentData = array_merge($validated, [
                'owner_id' => auth()->id(),
                'has_internet' => $request->input('has_internet') == '1' || $request->boolean('has_internet'),
                'meter_based' => $request->input('meter_based') == '1' || $request->boolean('meter_based'),
                'allows_children' => $request->input('allows_children') == '1' || $request->boolean('allows_children'),
                'allows_pets' => $request->input('allows_pets') == '1' || $request->boolean('allows_pets'),
                'allows_smoking' => $request->input('allows_smoking') == '1' || $request->boolean('allows_smoking'),
                'status' => 'available',
            ]);

            $apartment = Apartment::create($apartmentData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('apartments', 'public');
                    ApartmentImage::create([
                        'apartment_id' => $apartment->id,
                        'image_path' => $path,
                        'is_main' => $index === 0,
                        'order' => $index,
                    ]);
                }
            }

            \Log::info('Apartment created successfully', ['apartment_id' => $apartment->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Apartment created successfully.',
                'apartment' => $apartment->load('images')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $apartment = Apartment::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'price_per_night' => 'required|numeric|min:0',
                'rooms' => 'required|integer|min:1',
                'total_area' => 'required|numeric|min:0',
                'kitchen_area' => 'nullable|numeric|min:0',
                'living_area' => 'nullable|numeric|min:0',
                'floor' => 'nullable|integer',
                'balcony' => 'nullable|string',
                'bathroom' => 'nullable|string',
                'renovation' => 'nullable|string',
                'furniture' => 'nullable|string',
                'appliances' => 'nullable|string',
                'deposit' => 'nullable|numeric|min:0',
                'commission' => 'nullable|numeric|min:0',
                'other_utilities' => 'nullable|string',
                'max_guests' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            if ($apartment->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Можно редактировать только апартаменты со статусом "Свободно".'
                ], 422);
            }

            $apartmentData = array_merge($validated, [
                'has_internet' => $request->input('has_internet') == '1' || $request->boolean('has_internet'),
                'meter_based' => $request->input('meter_based') == '1' || $request->boolean('meter_based'),
                'allows_children' => $request->input('allows_children') == '1' || $request->boolean('allows_children'),
                'allows_pets' => $request->input('allows_pets') == '1' || $request->boolean('allows_pets'),
                'allows_smoking' => $request->input('allows_smoking') == '1' || $request->boolean('allows_smoking'),
            ]);

            $apartment->update($apartmentData);

            return response()->json([
                'success' => true,
                'message' => 'Apartment updated successfully.',
                'apartment' => $apartment->load('images')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        \Log::info('ApartmentAdminController::destroy called', [
            'apartment_id' => $id,
            'user_id' => auth()->id()
        ]);
        
        try {
            $apartment = Apartment::findOrFail($id);
            
            if ($apartment->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Можно удалять только апартаменты со статусом "Свободно".'
                ], 422);
            }

            // Удаляем связанные изображения
            foreach ($apartment->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            $apartment->delete();
            
            \Log::info('Apartment deleted successfully', ['apartment_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Apartment deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting apartment: ' . $e->getMessage()
            ], 500);
        }
    }
}