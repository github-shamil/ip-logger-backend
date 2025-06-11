<?php
$ip = $_SERVER['REMOTE_ADDR'];
$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);

$log = "[".date("Y-m-d H:i:s")."] IP: {$ip}, Country: {$details['country']}, Region: {$details['regionName']}, City: {$details['city']}, ISP: {$details['isp']}\n";

file_put_contents("log.txt", $log, FILE_APPEND);

// SQLite logging
$db = new SQLite3('logdata.db');
$db->exec("CREATE TABLE IF NOT EXISTS logs (ip TEXT, country TEXT, region TEXT, city TEXT, isp TEXT, time TEXT)");
$stmt = $db->prepare("INSERT INTO logs (ip, country, region, city, isp, time) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $details['country']);
$stmt->bindValue(3, $details['regionName']);
$stmt->bindValue(4, $details['city']);
$stmt->bindValue(5, $details['isp']);
$stmt->bindValue(6, date("Y-m-d H:i:s"));
$stmt->execute();

echo "Location captured.";
?>
