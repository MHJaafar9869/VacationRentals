<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Owner;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SebastianBergmann\Diff\Diff;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class StripePaymentController extends Controller

{
    public function stripe(Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));
    
            $response = $stripe->checkout->sessions->create([
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $request->input('product_name'),  // Pass product name
                            ],
                            'unit_amount' => $request->input('price') * 100,  // Stripe expects amounts in cents
                        ],
                        'quantity' => $request->input('quantity', 1), // Default quantity to 1 if not provided
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
                'metadata' => [  // Add metadata to the session
                    'product_name' => $request->input('product_name'),
                    'quantity' => $request->input('quantity', 1),
                ],
            ]);
    
            if (isset($response->id)) {
                return response()->json([
                    'status' => 'success',
                    'sessionId' => $response->id,
                    'url' => $response->url,
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Unable to create Stripe session'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function success(Request $request)
    {
        try {
            if ($request->has('session_id')) {
                $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));
    
                $response = $stripe->checkout->sessions->retrieve($request->input('session_id'));
    
                $payment = new \App\Models\Payment();
                $payment->payment_id = $response->id;
                $payment->product_name = $response->metadata->product_name;  // Retrieve product name from metadata
                $payment->amount = $response->amount_total; 
                $payment->quantity = $response->metadata->quantity;  // Retrieve quantity from metadata
                $payment->currency = $response->currency;
                $payment->payer_name = $response->customer_details->name ?? 'N/A';
                $payment->payer_email = $response->customer_details->email ?? 'N/A';
                $payment->payment_status = $response->payment_status;
                $payment->payment_method = 'Stripe';
                $payment->save();
                
                $property = Property::where('name', $response->metadata->product_name)->first();
                $owner = Owner::where('id', $property->owner_id)->first();
                $owner->wallet += $response->amount_total / 100; // to insert in in $
                $owner->save();
                // dd($owner->wallet);
                return redirect('http://localhost:4200/success');  
            } else {
                return response()->json(['status' => 'error', 'message' => 'No session ID provided'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function cancel(){

        return response()->json(['status' => 'error', 'message' => 'Payment cancelled'], 400);
    }



    // ==========================================================================

    //     public function createCheckoutSession(Request $request, $id)
    //     {
    //         Stripe::setApiKey(env('STRIPE_SECRET'));

    //         $hostStripeAccountId = $request->input('host_stripe_account_id');

    //         if (!$request->has('start_date') || !$request->has('end_date')) {
    //             return response()->json([
    //                 'error' => 'You need to specify the check-in and check-out dates.'
    //             ], 200);
    //         }

    //         $start_date = Carbon::parse('Y-m-d', $request->input('start_data'));
    //         $end_date = Carbon::parse('Y-m-d', $request->input('end_date'));
    //         $days = $end_date->diffInDays($start_date);

    //         $currency = 'usd';
    //         $property = Property::find($id);

    //         $checkoutSession = Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [
    //                 [
    //                     'price_data' => [
    //                         'currency' => $currency,
    //                         'product_data' => [
    //                             'name' => $property->name,
    //                             'check-in_date' => $start_date,
    //                             'check-out_date' => $end_date
    //                         ],
    //                         'unit_amount' => $property->night_rate * $days,
    //                     ],
    //                     'quantity' => 1,
    //                 ]
    //             ],
    //             'mode' => 'payment',
    //             'success_url' => env('FRONTEND_URL') . '/success',
    //             'cancel_url' => env('FRONTEND_URL') . '/cancel',
    //             'payment_intent_data' => [
    //                 'application_fee_amount' => ($property->night_rate * $days) * 0.10,
    //                 'transfer_data' => [
    //                     'destination' => $hostStripeAccountId,
    //                 ],
    //             ],
    //         ]);


    //         return response()->json(['id' => $checkoutSession->id]);
    //     }

    //     public function ownerCreateAccount(Request $request, $id)
    //     {
    //         Stripe::setApiKey(env('STRIPE_SECRET'));

    //         $owner = Owner::find($id);

    //         if (!$owner) {
    //             return response()->json(['error' => 'Owner not found'], 200);
    //         }
    //         if (!$request->has('country') || !$request->has('email')) {
    //             return response()->json(['error' => 'You need to specify your country and your email'], 200);
    //         }

    //         try {
    //             $account = Account::create([
    //                 'type' => 'express',
    //                 'country' => $request->country,
    //                 'email' => $request->email,
    //                 'capabilities' => [
    //                     'transfers' => ['requested' => true], // Enable transfers capability
    //                 ],
    //             ]);

    //             $owner->bank_account = $account->id;
    //             $owner->save();

    //             return response()->json([
    //                 'account_id' => $account->id,
    //                 'message' => 'Connected account created successfully!',
    //             ], 200);

    //         } catch (\Exception $e) {
    //             return response()->json([
    //                 'error' => $e->getMessage(),
    //             ], 200);
    //         }
    //     }

}
