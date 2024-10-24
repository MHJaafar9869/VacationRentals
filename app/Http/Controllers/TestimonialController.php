<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    //

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(200, 'Validation failed', $validator->errors());
        }
 

        $data = $request->all();
        $data['user_id'] = Auth::id();
        Testimonial::create([
            'name' => $data['name'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'user_id' => $data['user_id'],
            'email' => $data['email'],
        ]);
        // dd($data);
        return ApiResponse::sendResponse(200, 'Testimonial submitted successfully', null);
    }


    public function getTestimonials(){

        $testimonials = Testimonial::with('user')->get();
        // dd($testimonials);
        return ApiResponse::sendResponse(200, 'Testimonial fetched successfully', TestimonialResource::collection($testimonials));
      
    }

    public function getTestimonialForUser(){
        $testimonials = Testimonial::where('subject' ,'Feedback')->with('user')->get();
        return ApiResponse::sendResponse(200, 'Testimonial fetched successfully', TestimonialResource::collection($testimonials));
    }

    public function destroy($id){
        $testimonial = Testimonial::find($id);
        $testimonial->delete();
        return ApiResponse::sendResponse(200, 'Testimonial deleted successfully', null);
    }
}
