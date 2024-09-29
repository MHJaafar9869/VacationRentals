<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function searchAvailableProperties(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $availableProperties = DB::table('properties AS p')
            ->whereNotExists(function ($query) use ($start_date, $end_date) {
                $query->select(DB::raw(1))
                    ->from('booking AS b')
                    ->whereColumn('b.property_id', 'p.id')
                    ->where('b.status', 'confirmed')
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('b.start_date', [$start_date, $end_date])
                            ->orWhereBetween('b.end_date', [$start_date, $end_date])
                            ->orWhere(function ($query) use ($start_date, $end_date) {
                                $query->where('b.start_date', '<=', $start_date)
                                    ->where('b.end_date', '>=', $end_date);
                            });
                    });
            })
            ->get();

        return response()->json($availableProperties);
    }
}