<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KitchenDestination;
use Illuminate\Support\Facades\Validator;

class KitchenDestinationController extends Controller
{
    public function index()
    {
        return view('admin.destination.list');
    }

    

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'printer_ip' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        KitchenDestination::create([
            'name' => $request->name,
            'printer_ip' => $request->printer_ip,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Destination created successfully']);
    }

    public function update(Request $request, $id)
    {
        $destination = KitchenDestination::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'printer_ip' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $destination->update([
            'name' => $request->name,
            'printer_ip' => $request->printer_ip,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Destination updated successfully']);
    }

    public function destroy($id)
    {
        KitchenDestination::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        KitchenDestination::whereIn('id', $request->ids)->delete();
        return response()->json(['status' => 'success', 'message' => 'Selected items deleted']);
    }

    public function fetchDestinations(Request $request)
    {
        $query = KitchenDestination::query();

        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('printer_ip', 'like', '%' . $request->keyword . '%');
        }

        // ✅ បន្ថែម៖ Logic សម្រាប់ Sort
        $sortBy  = $request->input('sort_by', 'created_at'); // default តាមថ្ងៃបង្កើត
        $sortDir = $request->input('sort_dir', 'desc');      // default ថ្មីទៅចាស់
        
        $query->orderBy($sortBy, $sortDir);

        $perPage = $request->input('per_page', 10);
        
        $destinations = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($destinations);
    }
}