<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;



use Illuminate\Http\Request;

class ImageSearchController extends Controller
{
    public function uploadProductImage(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Path file sementara yang diupload
        $uploadedFile = $request->file('product_image');

        if ($uploadedFile) {
            // API Key dan Endpoint
            $api_key = "tCYYYwQy5SoVRFxw1XUG";  // Ganti dengan API key Anda
            $model_endpoint = "e-commerce-lvn2q/2";     // Ganti dengan endpoint model yang sesuai
            
            // URL untuk request ke Roboflow
            $url = "https://detect.roboflow.com/" . $model_endpoint
                . "?api_key=" . $api_key
                . "&name=" . urlencode($uploadedFile->getClientOriginalName());

            // Setup request menggunakan cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'file' => new \CURLFile($uploadedFile->getRealPath()),  // Menyertakan file
            ]);

            // Kirim request dan ambil respons
            $response = curl_exec($ch);

            // Cek error cURL
            if (curl_errno($ch)) {
                return response()->json(['error' => curl_error($ch)], 500);
            } else {
                // Decode JSON response
                $decodedResponse = json_decode($response, true);

                // Periksa apakah 'predictions' ada dalam respons
                if (isset($decodedResponse['predictions'][0]['class'])) {
                    $class = $decodedResponse['predictions'][0]['class'];

                    return redirect()->route('byCategory', ['category' => $class]);
                

                } else {
                    return response()->json(['error' => 'No class detected or invalid response.'], 400);
                }
            }


            // Tutup koneksi cURL setelah selesai
            return response()->json([
                'success' => true,
                'data' => $class,
            ]);
            curl_close($ch);
        } else {
            return response()->json(['error' => 'No image uploaded.'], 400);
        }
    }
}
