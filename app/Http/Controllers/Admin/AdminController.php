<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PropertyResource;
use App\Mail\PropertyAccepted;
use App\Mail\PropertyRejected;
use App\Mail\SendEmailNotification;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //
    public function getOwnerDetails($id){
        $owner = Owner::where('id', $id)->where('role', 'owner')->with(['properties'])->first();
        if (!$owner) {
            return response()->json([
                'message' => 'Owner not found or not authorized.'
            ], 404);
        }

        // Return the owner details with payments, favorites, and reviews relationships
        return response()->json([
            'owner' => $owner
        ], 200);
    
    }
    public function users(){
        $users = User::all();
        return ApiResponse::sendResponse(200, 'Success', $users);
    }
    public function owners(){
        $owners = Owner::where('role', 'owner')->get();
        return ApiResponse::sendResponse(200, 'Success', $owners);
    }

    public function properties(){
        $properties = Property::all();
        return PropertyResource::collection($properties);
        
        // ApiResponse::sendResponse(200, 'Success', $properties);
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,accepted,rejected',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422); 
        }
    
        $property = Property::findOrFail($id);
    
        $property->status = $request->input('status');
        $property->save();
    
       
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'data' => $property
        ], 200);
    }

    

    public function sendEmail(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
           'mail_greeting' => 'required|string',
            'mail_body' => 'required|string',
            'mail_action_text' => 'required|string',
            'mail_action_url' => 'required|url',
            'mail_end_line' => 'required|string',        ]);
        $request->validate([
            
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        }
        $contact = Owner::findOrFail($id); 
    
        $details = [
            'mail_greeting' => $request->mail_greeting,
            'mail_body' => $request->mail_body,
            'mail_action_text' => $request->mail_action_text,
            'mail_action_url' => $request->mail_action_url,
            'mail_end_line' => $request->mail_end_line,
        ];
    
        Mail::to($contact->email)->send(new SendEmailNotification($details));
    
        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully!',
        ], 200);
    }
    

    public function index(Request $request)
    {
        $query = Property::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $properties = $query->get(); // Assuming there's a relationship with an owner

        return PropertyResource::collection($properties);

    }

    public function show($id){
        $property = Property::find($id);
        return new PropertyResource($property);
    }
    public function showowner($id){
        $owner = Owner::find($id);
        return response()->json([
            'owner' => $owner
        ]);
    }
    public function payments(){

        $payments = Payment::all();

        return PaymentResource::collection($payments);
    }
}