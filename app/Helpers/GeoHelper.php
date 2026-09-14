<?php
namespace App\Helpers;

class GeoHelper
{
    public static function hitungJarak(
        float $lat1, float $lon1,
        float $lat2, float $lon2
    ): float {
        $r    = 6371000;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lon2 - $lon1);

        $a = sin($dphi / 2) ** 2
           + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $r * $c;

        // Cetak log proses perhitungan Haversine secara matematis ke Terminal
        $logText = "\n=== MULAI PERHITUNGAN HAVERSINE FORMULA ===\n" .
            "[Input] Lat Kamera: {$lat1}, Lng Kamera: {$lon1}\n" .
            "[Input] Lat Kantor: {$lat2}, Lng Kantor: {$lon2}\n" .
            "[Proses] delta Phi (dphi): {$dphi}\n" .
            "[Proses] delta Lambda (dlam): {$dlam}\n" .
            "[Proses] a = {$a}\n" .
            "[Proses] c = {$c}\n" .
            "[HASIL AKHIR] Jarak (R * c): " . round($distance, 2) . " meter\n" .
            "=============================================\n";
        
        file_put_contents('php://stderr', $logText);

        return $distance;
    }

    public static function dalamRadius(float $lat, float $lon): array
    {
        $kantorLat = config('kantor.latitude');
        $kantorLon = config('kantor.longitude');
        $radius    = config('kantor.radius_meter');

        $jarak = self::hitungJarak($lat, $lon, $kantorLat, $kantorLon);

        return [
            'valid'       => $jarak <= $radius,
            'jarak_meter' => round($jarak, 2),
        ];
    }
}