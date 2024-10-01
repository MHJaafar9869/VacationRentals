<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function users(){
        $users = User::all();
        return ApiResponse::sendResponse(200, 'Success', $users);
    }
    public function owners(){
        $owners = Owner::all();
        return ApiResponse::sendResponse(200, 'Success', $owners);
    }

    public function properties(){
        $properties = Property::all();
        return ApiResponse::sendResponse(200, 'Success', $properties);
    }
}
