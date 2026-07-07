<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['doctor', 'patient', 'service']);
        
        // فلترة اختيارية بالتاريخ
        if ($request->has('date')) {
            $query->where('appointment_date', $request->date);
        }
        
        return $query->latest()->get();
    }

    public function destroy($id)
{
    $appointment = Appointment::findOrFail($id);
    $appointment->delete();
    return response()->json(['message' => 'تم حذف الموعد بنجاح']);
}
}