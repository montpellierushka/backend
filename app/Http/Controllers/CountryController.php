<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CountryController extends Controller
{
    /**
     * Получение списка стран
     */
    public function index()
    {
        try {
            $countries = Country::all();

            return response()->json([
                'status' => 'success',
                'data' => $countries
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CountryController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении списка стран'
            ], 500);
        }
    }

    /**
     * Получение информации о стране
     */
    public function show(Country $country)
    {
        try {
            $country->load(['recipes', 'routes']);

            return response()->json([
                'status' => 'success',
                'data' => $country
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CountryController@show: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при получении информации о стране'
            ], 500);
        }
    }

    /**
     * Создание новой страны
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|size:2|unique:countries',
                'flag' => 'required|image|max:2048',
                'description' => 'required|string'
            ]);

            \DB::beginTransaction();

            $path = $request->file('flag')->store('flags', 'public');

            $country = Country::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'flag' => $path,
                'description' => $validated['description']
            ]);

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $country
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error in CountryController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при создании страны'
            ], 500);
        }
    }

    /**
     * Обновление страны
     */
    public function update(Request $request, Country $country)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|size:2|unique:countries,code,' . $country->id,
                'flag' => 'nullable|image|max:2048',
                'description' => 'required|string'
            ]);

            \DB::beginTransaction();

            $data = [
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description']
            ];

            if ($request->hasFile('flag')) {
                if ($country->flag) {
                    Storage::disk('public')->delete($country->flag);
                }
                $path = $request->file('flag')->store('flags', 'public');
                $data['flag'] = $path;
            }

            $country->update($data);

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $country
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error in CountryController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при обновлении страны'
            ], 500);
        }
    }

    /**
     * Удаление страны
     */
    public function destroy(Country $country)
    {
        try {
            \DB::beginTransaction();

            if ($country->flag) {
                Storage::disk('public')->delete($country->flag);
            }

            $country->delete();

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Страна успешно удалена'
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error in CountryController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Произошла ошибка при удалении страны'
            ], 500);
        }
    }
} 