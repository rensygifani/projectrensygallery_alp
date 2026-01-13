<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * Menampilkan daftar provinsi dari API Raja Ongkir
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Mengambil data provinsi dari API Raja Ongkir
        $response = Http::withHeaders([

            //headers yang diperlukan untuk API Raja Ongkir
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),

        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province?limit=100');

        // Memeriksa apakah permintaan berhasil
        if ($response->successful()) {

            // Mengambil data provinsi dari respons JSON
            // Jika 'data' tidak ada, inisialisasi dengan array kosong
            $provinces = $response->json()['data'] ?? [];
        }

        // returning the view with provinces data
        return view('ongkir.index', compact('provinces'));
    }

     /**
     * Mengambil data kota berdasarkan ID provinsi
     *
     * @param int $provinceId
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getCities($provinceId)
    // {
    //     // Mengambil data kota berdasarkan ID provinsi dari API Raja Ongkir
    //     $response = Http::withHeaders([

    //         //headers yang diperlukan untuk API Raja Ongkir
    //         'Accept' => 'application/json',
    //         'key' => config('rajaongkir.api_key'),

    //     ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

    //     if ($response->successful()) {

    //         // Mengambil data kota dari respons JSON
    //         // Jika 'data' tidak ada, inisialisasi dengan array kosong
    //         return response()->json($response->json()['data'] ?? []);
    //     }
    // }

    // /**
    //  * Mengambil data kecamatan berdasarkan ID kota
    //  *
    //  * @param int $cityId
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function getDistricts($cityId)
    // {
    //     // Mengambil data kecamatan berdasarkan ID kota dari API Raja Ongkir
    //     $response = Http::withHeaders([

    //         //headers yang diperlukan untuk API Raja Ongkir
    //         'Accept' => 'application/json',
    //         'key' => config('rajaongkir.api_key'),

    //     ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

    //     if ($response->successful()) {

    //         // Mengambil data kecamatan dari respons JSON
    //         // Jika 'data' tidak ada, inisialisasi dengan array kosong
    //         return response()->json($response->json()['data'] ?? []);
    //     }
    // }


public function getCities($provinceId)
{
    \Log::info('API Key: '.config('rajaongkir.api_key'));

    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'key' => config('rajaongkir.api_key'),
    ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}", [
        'province_id' => $provinceId
    ]);

    \Log::info('Response body: '.$response->body());

    if ($response->successful()) {
        return response()->json($response->json()['data'] ?? []);
    }

    return response()->json([], 500);
}


public function getDistricts($cityId)
{
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'key' => config('rajaongkir.api_key'),
    ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}", [
        'city_id' => $cityId
    ]);

    if ($response->successful()) {
        return response()->json($response->json()['data'] ?? []);
    }

    return response()->json([], 500);
}


     /**
     * Menghitung ongkos kirim berdasarkan data yang diberikan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
   public function checkOngkir(Request $request)
{
    $response = Http::asForm()->withHeaders([
        'Accept' => 'application/json',
        'key' => config('rajaongkir.api_key'),
    ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
        'origin'      => 3855,
        'destination' => $request->district_id,
        'weight'      => $request->weight,
        'courier'     => $request->courier,
    ]);

    if (!$response->successful()) {
        \Log::error('RajaOngkir Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return response()->json([], 500);
    }

    // 🔥 KOMERCE SUDAH RAPI
    return response()->json($response->json('data') ?? []);
}


}
