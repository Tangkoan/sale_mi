<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    public function index()
    {
        return view('admin.table.table_list');
    }

    public function fetchTables(Request $request)
    {
        $query = Table::query();

        // 1. Search & Filter
        if ($request->keyword) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // 2. Sorting (កូដថ្មី)
        $sortBy  = $request->input('sort_by', 'created_at'); // យកតាមអ្វីដែលផ្ញើមក ឬយក created_at ជាគោល
        $sortDir = $request->input('sort_dir', 'desc');      // asc ឬ desc

        // ការពារកុំឱ្យគេបោះឈ្មោះ Column ផ្ដេសផ្ដាស (Security)
        $allowedSorts = ['name', 'status', 'created_at'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }

        $perPage = $request->input('per_page', 10);
        $tables = ($perPage === 'all') 
            ? $query->paginate(999999) 
            : $query->paginate((int)$perPage);

        return response()->json($tables);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255|unique:tables,name', // ឈ្មោះតុមិនគួរជាន់គ្នាទេ
            'status' => 'required|in:available,busy',
        ], [
            'required'    => __('messages.field_required'),
            'name.unique' => __('messages.table_name_exist'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $table = Table::create([
            'name'   => $request->name,
            'status' => $request->status,
        ]);

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($table)->log('created table');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.table_created')]);
    }

    public function update(Request $request, $id)
    {
        $table = Table::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255|unique:tables,name,' . $id,
            'status' => 'required|in:available,busy',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data'), 'errors' => $validator->errors()], 422);
        }

        $table->update([
            'name'   => $request->name,
            'status' => $request->status,
        ]);

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($table)->log('updated table');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.table_updated')]);
    }

    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        $table->delete();

        if(function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($table)->log('deleted table');
        }

        return response()->json(['status' => 'success', 'message' => __('messages.table_deleted')]);
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array',
            'ids.*' => 'exists:tables,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => __('messages.invalid_data')], 422);
        }

        Table::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('messages.bulk_delete_success', ['count' => count($request->ids)])
        ]);
    }
}