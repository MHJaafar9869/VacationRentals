<?php

// WeatherService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function getTemperature($latitude, $longitude)
    {
        $apiKey = env('WEATHER_API_KEY');
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$apiKey}&units=metric";

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            return $data['main']['temp']; 
        }

        return "No";
    }
}
