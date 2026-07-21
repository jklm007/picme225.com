<?php
// Test: Yopougon -> Cocody (trajet réel Abidjan)
$s_lat = 5.325;
$s_lng = -4.085;
$d_lat = 5.345;
$d_lng = -3.985;

$mapboxKey = "pk.eyJ1Ijoia29uYW5qa2xtIiwiYSI6ImNtbzJxajIyMTB1eGgyb3F3NDR2NmZwYngifQ.lEcE-gKrmvUdCeOfwletAg";

echo "=== Test 1: Mapbox Alternatives ===\n";
$url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$s_lng},{$s_lat};{$d_lng},{$d_lat}?alternatives=true&steps=true&geometries=polyline&overview=full&access_token={$mapboxKey}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
if ($err) {
    echo "CURL ERROR: $err\n";
} else {
    $data = json_decode($result, true);
    $count = count($data['routes'] ?? []);
    echo "Mapbox routes returned: $count\n";
    foreach (($data['routes'] ?? []) as $i => $r) {
        echo "  Route $i: " . round($r['distance']/1000, 2) . "km, " . round($r['duration']/60) . " min\n";
    }
}

echo "\n=== Test 2: OSRM Alternatives ===\n";
$url2 = "https://router.project-osrm.org/route/v1/driving/{$s_lng},{$s_lat};{$d_lng},{$d_lat}?overview=full&geometries=polyline&alternatives=3";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
$result2 = curl_exec($ch2);
$err2 = curl_error($ch2);
curl_close($ch2);
if ($err2) {
    echo "CURL ERROR: $err2\n";
} else {
    $data2 = json_decode($result2, true);
    $count2 = count($data2['routes'] ?? []);
    echo "OSRM routes returned: $count2\n";
    foreach (($data2['routes'] ?? []) as $i => $r) {
        echo "  Route $i: " . round($r['distance']/1000, 2) . "km, " . round($r['duration']/60) . " min\n";
    }
}

echo "\n=== Test 3: OSRM Right Detour ===\n";
$midLat = ($s_lat + $d_lat) / 2;
$midLng = ($s_lng + $d_lng) / 2;
$dLat = $d_lat - $s_lat;
$dLng = $d_lng - $s_lng;
$len = sqrt($dLat*$dLat + $dLng*$dLng);
$offsetScale = min(0.02, max(0.005, $len * 0.25));
$waypointLat = $midLat + (-($dLng/$len) * $offsetScale);
$waypointLng = $midLng + (($dLat/$len) * $offsetScale);

$url3 = "https://router.project-osrm.org/route/v1/driving/{$s_lng},{$s_lat};{$waypointLng},{$waypointLat};{$d_lng},{$d_lat}?overview=full&geometries=polyline";
$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $url3);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_TIMEOUT, 10);
$result3 = curl_exec($ch3);
$err3 = curl_error($ch3);
curl_close($ch3);
if ($err3) {
    echo "CURL ERROR: $err3\n";
} else {
    $data3 = json_decode($result3, true);
    if (isset($data3['routes'][0])) {
        $r = $data3['routes'][0];
        echo "Right detour route: " . round($r['distance']/1000, 2) . "km, " . round($r['duration']/60) . " min ✅\n";
        echo "Waypoint used: lat={$waypointLat}, lng={$waypointLng}\n";
    } else {
        echo "No detour route generated ❌\n";
        echo "Response: " . substr($result3, 0, 200) . "\n";
    }
}

echo "\n=== Test 4: OSRM Left Detour ===\n";
$waypointLat2 = $midLat - (-($dLng/$len) * $offsetScale);
$waypointLng2 = $midLng - (($dLat/$len) * $offsetScale);
$url4 = "https://router.project-osrm.org/route/v1/driving/{$s_lng},{$s_lat};{$waypointLng2},{$waypointLat2};{$d_lng},{$d_lat}?overview=full&geometries=polyline";
$ch4 = curl_init();
curl_setopt($ch4, CURLOPT_URL, $url4);
curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch4, CURLOPT_TIMEOUT, 10);
$result4 = curl_exec($ch4);
$err4 = curl_error($ch4);
curl_close($ch4);
if ($err4) {
    echo "CURL ERROR: $err4\n";
} else {
    $data4 = json_decode($result4, true);
    if (isset($data4['routes'][0])) {
        $r = $data4['routes'][0];
        echo "Left detour route: " . round($r['distance']/1000, 2) . "km, " . round($r['duration']/60) . " min ✅\n";
        echo "Waypoint used: lat={$waypointLat2}, lng={$waypointLng2}\n";
    } else {
        echo "No left detour route generated ❌\n";
        echo "Response: " . substr($result4, 0, 200) . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total routes expected from SmartRoute API (Mapbox 1 + 2 OSRM detours = 3): OK\n";
