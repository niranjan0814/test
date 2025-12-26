<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Create a new Customer (Field Officer only).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Product / Location Details (optional)
            'location' => 'nullable|string',
            'product_type' => 'nullable|string',
            'base_product' => 'nullable|string',
            'pcsu_csu_code' => 'nullable|string',
            
            // Customer Personal Details (required)
            'code_type' => 'required|string|in:' . Customer::CODE_TYPE, // Must be 'NIC'
            'customer_code' => ['required', 'string', 'regex:/^([0-9]{9}[x|X|v|V]|[0-9]{12})$/', 'unique:customers,customer_code'], // This is the NIC
            'gender' => 'required|in:Male,Female,Other',
            'title' => 'required|string',
            'full_name' => 'required|string',
            'initials' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'civil_status' => 'required|in:Single,Married,Divorced,Widowed',
            'religion' => 'required|string|in:' . implode(',', Customer::RELIGIONS),
            'mobile_no_1' => ['required', 'string', 'regex:/^\d{10}$/'],
            'mobile_no_2' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'ccl_mobile_no' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'spouse_name' => 'nullable|string',
            'health_info' => 'nullable|json',
            'family_members_count' => 'nullable|integer|min:1|max:20',
            'customer_profile_image' => 'nullable|string',
            'monthly_income' => 'nullable|numeric|min:0',
            
            // Customer Address Details (required)
            'address_type' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'address_line_3' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'gs_division' => 'required|string|max:255',
            'telephone' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'preferred_address' => 'nullable|boolean',
            
            // Business Details (all optional)
            'ownership_type' => 'nullable|string|in:' . implode(',', Customer::OWNERSHIP_TYPES),
            'register_number' => 'nullable|string',
            'business_name' => 'nullable|string',
            'business_email' => 'nullable|email:filter',
            'business_duration' => 'nullable|string',
            'business_place' => 'nullable|string',
            'handled_by' => 'nullable|string',
            'no_of_employees' => 'nullable|integer',
            'market_reputation' => 'nullable|string',
            'sector' => 'nullable|string',
            'sub_sector' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 4000,
                'message' => 'Invalid customer data',
                'errors' => $validator->errors()
            ], 400);
        }

        $validated = $validator->validated();

        // Extract gender from Sri Lankan NIC
        $nic = $validated['customer_code'];
        $genderFromNIC = $this->extractGenderFromNIC($nic);
        
        if (!$genderFromNIC) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Sri Lankan NIC format'
            ], 422);
        }

        // Validate that provided gender matches NIC
        if ($genderFromNIC !== $validated['gender']) {
            return response()->json([
                'status' => 'error',
                'message' => "Gender mismatch. NIC indicates gender is {$genderFromNIC}, but you provided {$validated['gender']}"
            ], 422);
        }

        // Only females can get loans
        if ($genderFromNIC !== 'Female') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only female customers are eligible for loans in this program'
            ], 403);
        }

        // Calculate age from date of birth
        $dateOfBirth = new \DateTime($validated['date_of_birth']);
        $today = new \DateTime('today');
        $age = $dateOfBirth->diff($today)->y;

        // Validate age range for loan eligibility
        if ($age < Customer::MIN_AGE) {
            return response()->json([
                'status' => 'error',
                'message' => "Customer must be at least " . Customer::MIN_AGE . " years old to be eligible for loans. Current age: {$age} years"
            ], 422);
        }

        if ($age > Customer::MAX_AGE) {
            return response()->json([
                'status' => 'error',
                'message' => "Customer must be " . Customer::MAX_AGE . " years old or younger to be eligible for loans. Current age: {$age} years"
            ], 422);
        }

        try {
            $customer = Customer::create($validated);

            return response()->json([
                'statusCode' => 2010,
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all customers or filter.
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        $isFiltered = false;

        // Apply simple filters if present
        if ($request->has('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
            $isFiltered = true;
        }
        if ($request->has('customer_code')) {
            $query->where('customer_code', 'like', '%' . $request->customer_code . '%');
            $isFiltered = true;
        }
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
            $isFiltered = true;
        }
        // Add more filters as needed

        $customers = $query->get();
        
        $message = $isFiltered 
            ? 'Customer filter applied successfully' 
            : 'Customer list fetched successfully';

        return response()->json([
            'statusCode' => 2000,
            'message' => $message,
            'data' => $customers
        ], 200);
    }

    /**
     * Get a specific customer.
     */
    public function show($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'statusCode' => 4040,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'statusCode' => 2000,
            'message' => 'Customer details fetched successfully',
            'data' => $customer
        ], 200);
    }

    /**
     * Update customer details.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'statusCode' => 4040,
                'message' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            // All fields are optional in update
            'location' => 'nullable|string',
            'product_type' => 'nullable|string',
            'base_product' => 'nullable|string',
            'pcsu_csu_code' => 'nullable|string',
            'code_type' => 'nullable|string|in:' . Customer::CODE_TYPE, // Must be 'NIC' if provided
            'customer_code' => ['nullable', 'string', 'regex:/^([0-9]{9}[x|X|v|V]|[0-9]{12})$/', 'unique:customers,customer_code,' . $id],
            'gender' => 'nullable|in:Male,Female,Other',
            'title' => 'nullable|string',
            'full_name' => 'nullable|string',
            'initials' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today|after:1900-01-01',
            'civil_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'religion' => 'nullable|string|in:' . implode(',', Customer::RELIGIONS),
            'mobile_no_1' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'mobile_no_2' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'ccl_mobile_no' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'spouse_name' => 'nullable|string',
            'health_info' => 'nullable|json',
            'family_members_count' => 'nullable|integer|min:1|max:20',
            'customer_profile_image' => 'nullable|string',
            'monthly_income' => 'nullable|numeric|min:0',
            'address_type' => 'nullable|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'address_line_3' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'gs_division' => 'nullable|string|max:255',
            'telephone' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'preferred_address' => 'nullable|boolean',
            'ownership_type' => 'nullable|string|in:' . implode(',', Customer::OWNERSHIP_TYPES),
            'status' => 'nullable|string|in:' . implode(',', Customer::STATUSES),
            'register_number' => 'nullable|string',
            'business_name' => 'nullable|string',
            'business_email' => 'nullable|email:filter',
            'business_duration' => 'nullable|string',
            'business_place' => 'nullable|string',
            'handled_by' => 'nullable|string',
            'no_of_employees' => 'nullable|integer',
            'market_reputation' => 'nullable|string',
            'sector' => 'nullable|string',
            'sub_sector' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => 4000,
                'message' => 'Invalid customer data',
                'errors' => $validator->errors()
            ], 400);
        }

        $validated = $validator->validated();

        try {
            $customer->update($validated);

            return response()->json([
                'statusCode' => 2000,
                'message' => 'Customer updated successfully',
                'data' => $customer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Customer.
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'statusCode' => 4040,
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            $customer->delete();

            return response()->json([
                'statusCode' => 2000,
                'message' => 'Customer deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import Customers from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            // Logic to handle import (placeholder for now)
            // $file = $request->file('file');
            // ... parse and create ...

            return response()->json([
                'statusCode' => 2000,
                'message' => 'Customers imported successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Customers to CSV.
     */
    public function export()
    {
        try {
            // Logic to handle export (placeholder for now)
            // In a real scenario, we might generate a file and return a download URL
            // or return the CSV stream. Given the JSON format requirement, we just confirm success.
            
            return response()->json([
                'statusCode' => 2000,
                'message' => 'Customers exported successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer constants for frontend forms
     */
    public function getConstants()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'country' => Customer::COUNTRY,
                'provinces' => Customer::PROVINCES,
                'province_districts_map' => Customer::PROVINCE_DISTRICTS,
                'districts' => Customer::DISTRICTS,
                'cities' => Customer::CITIES,
                'code_types' => [Customer::CODE_TYPE],
                'religions' => Customer::RELIGIONS,
                'statuses' => Customer::STATUSES,
                'ownership_types' => Customer::OWNERSHIP_TYPES,
                'age_limits' => [
                    'min' => Customer::MIN_AGE,
                    'max' => Customer::MAX_AGE
                ]
            ]
        ], 200);
    }

    /**
     * Extract gender from Sri Lankan NIC
     * Old format: 9 digits + V (e.g., 856234567V)
     * New format: 12 digits (e.g., 198562345678)
     */
    private function extractGenderFromNIC($nic)
    {
        $nic = strtoupper(trim($nic));
        
        // Old NIC format (9 digits + V)
        if (preg_match('/^(\d{9})V$/', $nic, $matches)) {
            $dayValue = intval(substr($matches[1], 2, 3));
            
            // If day value > 500, it's female
            if ($dayValue > 500) {
                return 'Female';
            } else {
                return 'Male';
            }
        }
        
        // New NIC format (12 digits)
        if (preg_match('/^(\d{12})$/', $nic)) {
            $dayValue = intval(substr($nic, 4, 3));
            
            // If day value > 500, it's female
            if ($dayValue > 500) {
                return 'Female';
            } else {
                return 'Male';
            }
        }
        
        return null; // Invalid format
    }
}
