<?php
date_default_timezone_set("Asia/Kolkata"); // Set your timezone

// Get IP Address
$ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
      $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
      $_SERVER['REMOTE_ADDR'];

// Get date/time
$datetime = date("Y-m-d H:i:s");

// Get user agent
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// Get geolocation from IP using ip-api
$api_url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,district,zip,lat,lon,isp,query";
$response = file_get_contents($api_url);
$data = json_decode($response, true);

// Handle API result
if ($data && $data['status'] === 'success') {
    $country = $data['country'];
    $region = $data['regionName'];
    $city = $data['city'];
    $district = $data['district'] ?? '';
    $zip = $data['zip'] ?? '';
    $lat = $data['lat'];
    $lon = $data['lon'];
    $isp = $data['isp'];
    $query_ip = $data['query'];
} else {
    $country = $region = $city = $district = $zip = $lat = $lon = $isp = $query_ip = "Unavailable";
}

// Log to TXT file
$log_txt = "[$datetime] IP: $ip | Country: $country | Region: $region | City: $city | District: $district | ZIP: $zip | Lat: $lat | Lon: $lon | ISP: $isp | UA: $user_agent\n";
file_put_contents("log.txt", $log_txt, FILE_APPEND);

// Log to SQLite
try {
    $db = new PDO("sqlite:log.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS ip_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT,
        country TEXT,
        region TEXT,
        city TEXT,
        district TEXT,
        zip TEXT,
        lat TEXT,
        lon TEXT,
        isp TEXT,
        user_agent TEXT,
        datetime TEXT
    )");
    
    $stmt = $db->prepare("INSERT INTO ip_logs (ip, country, region, city, district, zip, lat, lon, isp, user_agent, datetime)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $query_ip, $country, $region, $city, $district, $zip, $lat, $lon, $isp, $user_agent, $datetime
    ]);
} catch (PDOException $e) {
    file_put_contents("errors.txt", "[$datetime] SQLite Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo "📍 Visitor tracked successfully.";
?>
