<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\PropertyAccepted;
use App\Mail\PropertyRejected;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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



    public function acceptProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->status = 'accepted'; // Update status
        $property->save();

        $owner = $property->owner; // Assuming a relation exists
        Mail::to($owner->email)->send(new PropertyAccepted($owner)); // Send acceptance email

        return response()->json(['message' => 'Property accepted and email sent.']);
    }
    public function rejectProperty($id)
    {
        // Find the property by ID
        $property = Property::findOrFail($id);

        // Update the status to 'rejected'
        $property->status = 'rejected';
        $property->save();

        // Send email to the owner
        $owner = $property->owner; // Assuming there's a relationship
        Mail::to($owner->email)->send(new PropertyRejected($owner));

        return response()->json(['message' => 'Property rejected and email sent.']);
    }

    protected function sendAcceptanceEmail($owner)
    {
        // Send email using Laravel's Mail facade
        Mail::to($owner->email)->send(new PropertyAccepted($owner));
    }
    public function deleteuser($id){
        $user = User::find($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function deleteowner($id){
        $owner = Owner::find($id);
        $owner->delete();
        return response()->json(['message' => 'Owner deleted successfully']);
    }

}
