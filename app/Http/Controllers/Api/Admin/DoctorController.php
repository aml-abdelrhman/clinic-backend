<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use App\HandlesImageUpload;

class DoctorController extends Controller
{
    use HandlesImageUpload;

    public function store(Request $request)
    {
        try {
            $data = $this->validateDoctor($request);

            if ($request->hasFile('image')) {
                $imageData = $this->uploadImage($request->file('image'), 'doctors');
                $data['image'] = $imageData['url'];
            }

            $doctor = Doctor::create($data);

            return response()->json([
                'status'  => true,
                'message' => 'Doctor created successfully',
                'data'    => $doctor
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        return response()->json(Doctor::with('specialty')->get());
    }

    public function show($id)
    {
        return response()->json(Doctor::with('specialty')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);
            $validatedData = $this->validateDoctor($request, $id);

            if ($request->hasFile('image')) {
                if ($doctor->image) {
                    $this->deleteImage($doctor->image);
                }
                
                $imageData = $this->uploadImage($request->file('image'), 'doctors');
                $validatedData['image'] = $imageData['url'];
            }

            $doctor->update($validatedData);
            return response()->json(['status' => true, 'message' => 'تم التحديث بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        
        if ($doctor->image) {
            $this->deleteImage($doctor->image);
        }
        
        $doctor->delete();
        return response()->json(['status' => true, 'message' => 'تم الحذف بنجاح']);
    }

    private function validateDoctor(Request $request, $id = null)
    {
        $rules = [
            'specialty_id'     => 'required|exists:specialties,id',
            'name'             => 'required',
            'bio'              => 'nullable',
            'years_experience' => 'nullable|integer|min:0',
            'price_from'       => 'required|numeric|min:0',
            'rating'           => 'nullable|numeric',
            'languages'        => 'nullable',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $data = $request->validate($rules);

        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = json_decode($data['name'], true);
        }
        if (isset($data['bio']) && is_string($data['bio'])) {
            $data['bio'] = json_decode($data['bio'], true);
        }
        if (isset($data['languages']) && is_string($data['languages'])) {
            $data['languages'] = json_decode($data['languages'], true);
        }

        return $data;
    }
}