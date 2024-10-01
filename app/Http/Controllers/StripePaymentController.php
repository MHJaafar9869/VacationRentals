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
    public function createCheckoutSession(Request $request, $id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $hostStripeAccountId = $request->input('host_stripe_account_id');

        if (!$request->has('start_date') || !$request->has('end_date')) {
            return response()->json([
                'error' => 'You need to specify the check-in and check-out dates.'
            ], 200);
        }

        $start_date = Carbon::parse('Y-m-d', $request->input('start_data'));
        $end_date = Carbon::parse('Y-m-d', $request->input('end_date'));
        $days = $end_date->diffInDays($start_date);

        $currency = 'usd';
        $property = Property::find($id);

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $property->name,
                            'check-in_date' => $start_date,
                            'check-out_date' => $end_date
                        ],
                        'unit_amount' => $property->night_rate * $days,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => env('FRONTEND_URL') . '/success',
            'cancel_url' => env('FRONTEND_URL') . '/cancel',
            'payment_intent_data' => [
                'application_fee_amount' => ($property->night_rate * $days) * 0.10,
                'transfer_data' => [
                    'destination' => $hostStripeAccountId,
                ],
            ],
        ]);


        return response()->json(['id' => $checkoutSession->id]);
    }

    public function ownerCreateAccount(Request $request, $id)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $owner = Owner::find($id);

        if (!$owner) {
            return response()->json(['error' => 'Owner not found'], 200);
        }
        if (!$request->has('country') || !$request->has('email')) {
            return response()->json(['error' => 'You need to specify your country and your email'], 200);
        }

        try {
            $account = Account::create([
                'type' => 'express',
                'country' => $request->country,
                'email' => $request->email,
                'capabilities' => [
                    'transfers' => ['requested' => true], // Enable transfers capability
                ],
            ]);

            $owner->bank_account = $account->id;
            $owner->save();

            return response()->json([
                'account_id' => $account->id,
                'message' => 'Connected account created successfully!',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 200);
        }
    }

}