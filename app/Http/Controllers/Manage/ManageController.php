<?php

namespace App\Http\Controllers\Manage;

use App\Models\User;
use App\Traits\ResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
class ManageController extends BaseController
{
    use ResourceTrait;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function extractReports(Request $request)
    {
        
    }

    public function viewOrders(Request $request)
    {
        // Logic to view all orders --- IGNORE ---
    }
}
