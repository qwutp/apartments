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
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'request_data' => $request->all()
        ]);
        
        try {
            // Проверяем обязательные поля
            if (!$request->has('name') || trim($request->input('name')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['name' => ['Поле название обязательно для заполнения.']]
                ], 422);
            }
            if (!$request->has('address') || trim($request->input('address')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['address' => ['Поле адрес обязательно для заполнения.']]
                ], 422);
            }
            
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
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
            
            // Преобразуем числовые поля после валидации
            $validated['price_per_night'] = (float) $validated['price_per_night'];
            $validated['rooms'] = (int) $validated['rooms'];
            $validated['total_area'] = (float) $validated['total_area'];
            $validated['max_guests'] = (int) $validated['max_guests'];
            if (isset($validated['kitchen_area'])) {
                $validated['kitchen_area'] = (float) $validated['kitchen_area'];
            }
            if (isset($validated['living_area'])) {
                $validated['living_area'] = (float) $validated['living_area'];
            }
            if (isset($validated['floor'])) {
                $validated['floor'] = (int) $validated['floor'];
            }

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

            // Обработка изображений
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                // Создаем директорию если её нет
                $uploadPath = public_path('images/apartments');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Если images - массив
                if (is_array($images)) {
                    foreach ($images as $index => $image) {
                        if ($image && $image->isValid()) {
                            // Генерируем уникальное имя файла
                            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            // Сохраняем в public/images/apartments
                            $image->move($uploadPath, $fileName);
                            // Сохраняем путь относительно public
                            $imagePath = 'images/apartments/' . $fileName;
                            
                            ApartmentImage::create([
                                'apartment_id' => $apartment->id,
                                'image_path' => $imagePath,
                                'is_main' => $index === 0,
                                'order' => $index,
                            ]);
                        }
                    }
                } else {
                    // Если одно изображение
                    if ($images->isValid()) {
                        // Генерируем уникальное имя файла
                        $fileName = time() . '_' . uniqid() . '.' . $images->getClientOriginalExtension();
                        // Сохраняем в public/images/apartments
                        $images->move($uploadPath, $fileName);
                        // Сохраняем путь относительно public
                        $imagePath = 'images/apartments/' . $fileName;
                        
                        ApartmentImage::create([
                            'apartment_id' => $apartment->id,
                            'image_path' => $imagePath,
                            'is_main' => true,
                            'order' => 0,
                        ]);
                    }
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
        \Log::info('ApartmentAdminController::update called', [
            'apartment_id' => $id,
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'has_method_override' => $request->has('_method'),
            'request_data' => $request->all(),
            'request_input' => $request->input(),
            'request_all' => $request->all()
        ]);
        
        try {
            $apartment = Apartment::findOrFail($id);
            
            // Проверяем обязательные поля
            if (!$request->has('name') || trim($request->input('name')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['name' => ['Поле название обязательно для заполнения.']]
                ], 422);
            }
            if (!$request->has('address') || trim($request->input('address')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => ['address' => ['Поле адрес обязательно для заполнения.']]
                ], 422);
            }
            
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
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
            
            // Преобразуем числовые поля после валидации
            $validated['price_per_night'] = (float) $validated['price_per_night'];
            $validated['rooms'] = (int) $validated['rooms'];
            $validated['total_area'] = (float) $validated['total_area'];
            $validated['max_guests'] = (int) $validated['max_guests'];
            if (isset($validated['kitchen_area'])) {
                $validated['kitchen_area'] = (float) $validated['kitchen_area'];
            }
            if (isset($validated['living_area'])) {
                $validated['living_area'] = (float) $validated['living_area'];
            }
            if (isset($validated['floor'])) {
                $validated['floor'] = (int) $validated['floor'];
            }

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

            // Обработка удаления изображений
            if ($request->has('deleted_images') && is_array($request->input('deleted_images'))) {
                $deletedImageIds = $request->input('deleted_images');
                foreach ($deletedImageIds as $imageId) {
                    $image = ApartmentImage::find($imageId);
                    if ($image && $image->apartment_id == $apartment->id) {
                        // Удаляем файл из public/images/apartments
                        $filePath = public_path($image->image_path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        // Удаляем запись из БД
                        $image->delete();
                    }
                }
            }

            // Обработка новых изображений
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                // Создаем директорию если её нет
                $uploadPath = public_path('images/apartments');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Если images - массив
                if (is_array($images)) {
                    $existingCount = $apartment->images()->count();
                    foreach ($images as $index => $image) {
                        if ($image && $image->isValid()) {
                            // Генерируем уникальное имя файла
                            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            // Сохраняем в public/images/apartments
                            $image->move($uploadPath, $fileName);
                            // Сохраняем путь относительно public
                            $imagePath = 'images/apartments/' . $fileName;
                            
                            ApartmentImage::create([
                                'apartment_id' => $apartment->id,
                                'image_path' => $imagePath,
                                'is_main' => ($existingCount === 0 && $index === 0), // Первое изображение становится главным, если их еще нет
                                'order' => $existingCount + $index,
                            ]);
                        }
                    }
                } else {
                    // Если одно изображение
                    if ($images->isValid()) {
                        $existingCount = $apartment->images()->count();
                        // Генерируем уникальное имя файла
                        $fileName = time() . '_' . uniqid() . '.' . $images->getClientOriginalExtension();
                        // Сохраняем в public/images/apartments
                        $images->move($uploadPath, $fileName);
                        // Сохраняем путь относительно public
                        $imagePath = 'images/apartments/' . $fileName;
                        
                        ApartmentImage::create([
                            'apartment_id' => $apartment->id,
                            'image_path' => $imagePath,
                            'is_main' => $existingCount === 0,
                            'order' => $existingCount,
                        ]);
                    }
                }
            }

            \Log::info('Apartment updated successfully', ['apartment_id' => $apartment->id]);

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