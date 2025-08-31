<?php
// image-proxy.php
// Purpose: fetch an image from Espo's "image" entryPoint and stream it to the browser.

ini_set('display_errors', 0);
error_reporting(E_ALL);

// 1) Validate input
$id   = $_GET['id']  ?? '';
$size = $_GET['size'] ?? 'large'; // small | medium | large | xLarge (Espo supported sizes)

if (!preg_match('/^[a-z0-9]{16,20}$/i', $id)) {
    header('HTTP/1.1 400 Bad Request');
    error_log("image-proxy: Invalid ID format: '$id'");
    exit('Bad id');
}
if (!in_array($size, ['small','medium','large','xLarge'], true)) {
    $size = 'large';
}

// 2) Build the Espo entryPoint URL
$ESPO_BASE = 'https://ecom.flyhubdigital.com';
$API_KEY   = '7077c399cb6831c2eb97526398fe15cb';

// Try the image entryPoint first (as your debug shows this works)
$url = $ESPO_BASE . '/?entryPoint=image&size=' . urlencode($size) . '&id=' . urlencode($id);

// 3) cURL with sensible timeouts and SSL verification
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => [
        'X-Api-Key: ' . $API_KEY,
        'Accept: image/*'
    ],
]);

$data       = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType= curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
$err        = curl_error($ch);
curl_close($ch);

// 4) If image entryPoint fails, try Attachment API as fallback
if ($httpCode !== 200 || empty($data)) {
    error_log("image-proxy: Image entryPoint failed (HTTP $httpCode), trying Attachment API");
    
    $attachmentUrl = $ESPO_BASE . '/api/v1/Attachment/file/' . urlencode($id);
    
    $ch2 = curl_init($attachmentUrl);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'X-Api-Key: ' . $API_KEY,
            'Accept: image/*'
        ],
    ]);
    
    $data = curl_exec($ch2);
    $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch2, CURLINFO_CONTENT_TYPE) ?: 'image/jpeg';
    $err = curl_error($ch2);
    curl_close($ch2);
}

// 5) Relay response
if ($httpCode !== 200 || empty($data)) {
    // Log errors server-side
    error_log("image-proxy error: HTTP $httpCode, cURL error: '$err', URL: $url");
    header('HTTP/1.1 ' . ($httpCode ?: 502));
    exit('Image not found');
}

// 6) Stream image
header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . strlen($data));
echo $data;