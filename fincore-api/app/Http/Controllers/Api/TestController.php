<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    // GET api/dashboard
    public function dashboard()
    {
        return response()->json([
            'app' => 'FinCore',
            'module' => 'Dashboard',
            'total_clients' => 120,
            'active_loans' => 45,
            'total_collections' => 150000,
        ]);
    }

    // POST api/client
    public function createClient(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Client created successfully',
            'data' => $request->all(),
        ]);
    }
}
