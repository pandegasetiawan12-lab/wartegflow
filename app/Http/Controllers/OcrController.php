<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcrController extends Controller
{
    public function processOcr(Request $request)
    {
        $request->validate([
            'foto_laporan'  => 'required|image|mimes:jpeg,png,jpg|max:4096',
            'jenis_laporan' => 'required|in:penjualan,stok',
        ]);

        $imagePath = $request->file('foto_laporan')->getRealPath();
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType  = $request->file('foto_laporan')->getMimeType();
        $apiKey    = env('GEMINI_API_KEY');

        $jenis = $request->jenis_laporan;
        if ($jenis === 'penjualan') {
            $prompt = "Baca foto laporan penjualan ini. Kembalikan HANYA format JSON valid tanpa teks markdown dengan struktur array objek: [{\"nama_menu\": string, \"jumlah_terjual\": number, \"harga_satuan\": number, \"total_penjualan\": number}]";
        } else {
            $prompt = "Baca foto laporan stok bahan baku ini. Kembalikan HANYA format JSON valid tanpa teks markdown dengan struktur array objek: [{\"nama_bahan\": string, \"satuan\": string, \"stok_awal\": number, \"pembelian\": number, \"pemakaian\": number, \"stok_akhir\": number}]";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=" . $apiKey;

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(30)
            ->post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mimeType,
                                    "data"      => $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "responseMimeType" => "application/json"
                ]
            ]);

        if ($response->successful()) {
            $rawText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $cleanJson = preg_replace('/```json\s*|\s*```/', '', $rawText);
            $parsedData = json_decode(trim($cleanJson), true);

            return response()->json([
                'status' => true,
                'data'   => $parsedData
            ]);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Gagal memproses OCR dari Gemini API.',
            'error'   => $response->body()
        ], 500);
    }
}