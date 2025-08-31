<?php
// debug-espo.php
// Test Espo authentication and image endpoints

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Espo Connection Debug</h2>";

$ESPO_BASE = 'https://ecom.flyhubdigital.com';
$API_KEY = '7077c399cb6831c2eb97526398fe15cb';

// Get a sample image ID from database
$host = "54.151.189.32";
$db   = "sme_ecom";
$user = "flyhubapp";
$pass = "KatieBruha_02";

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die('DB connect error: ' . $mysqli->connect_error);
}

$sql = "SELECT images_id FROM c_product WHERE images_id IS NOT NULL AND images_id != '' LIMIT 1";
$res = $mysqli->query($sql);
$testId = null;

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $testId = $row['images_id'];
    echo "<p><strong>Found test image ID:</strong> $testId</p>";
} else {
    echo "<p><strong>No image IDs found in database.</strong> Using dummy ID for testing.</p>";
    $testId = "dummyid12345678901"; // Dummy for testing auth methods
}

echo "<hr>";

// Test different endpoints and auth methods
$testCases = [
    [
        'name' => 'Image entryPoint with X-Api-Key',
        'url' => $ESPO_BASE . '/?entryPoint=image&size=large&id=' . urlencode($testId),
        'headers' => ['X-Api-Key: ' . $API_KEY, 'Accept: image/*']
    ],
    [
        'name' => 'Image entryPoint with Authorization Bearer',
        'url' => $ESPO_BASE . '/?entryPoint=image&size=large&id=' . urlencode($testId),
        'headers' => ['Authorization: Bearer ' . $API_KEY, 'Accept: image/*']
    ],
    [
        'name' => 'Image entryPoint with apiKey parameter',
        'url' => $ESPO_BASE . '/?entryPoint=image&size=large&id=' . urlencode($testId) . '&apiKey=' . urlencode($API_KEY),
        'headers' => ['Accept: image/*']
    ],
    [
        'name' => 'Attachment API endpoint',
        'url' => $ESPO_BASE . '/api/v1/Attachment/file/' . urlencode($testId),
        'headers' => ['X-Api-Key: ' . $API_KEY, 'Accept: image/*']
    ],
    [
        'name' => 'Generic API test (Product list)',
        'url' => $ESPO_BASE . '/api/v1/Product?maxSize=1',
        'headers' => ['X-Api-Key: ' . $API_KEY, 'Accept: application/json']
    ]
];

function testEndpoint($name, $url, $headers) {
    echo "<h3>Testing: $name</h3>";
    echo "<p><strong>URL:</strong> $url</p>";
    echo "<p><strong>Headers:</strong> " . implode(', ', $headers) . "</p>";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false, // Temporarily disable for testing
        CURLOPT_SSL_VERIFYHOST => 0,     // Temporarily disable for testing
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_VERBOSE => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    echo "<p><strong>Content-Type:</strong> " . ($contentType ?: 'none') . "</p>";
    
    if ($error) {
        echo "<p><strong style='color: red;'>cURL Error:</strong> $error</p>";
    }
    
    if ($httpCode === 200) {
        echo "<p style='color: green;'><strong>SUCCESS!</strong></p>";
        if (strpos($contentType, 'image/') !== false) {
            echo "<p>This appears to be an image response.</p>";
        } else {
            echo "<p><strong>Response preview:</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        }
    } elseif ($httpCode === 403) {
        echo "<p style='color: red;'><strong>FORBIDDEN (403)</strong> - Authentication failed</p>";
    } elseif ($httpCode === 404) {
        echo "<p style='color: orange;'><strong>NOT FOUND (404)</strong> - Endpoint or resource doesn't exist</p>";
    } else {
        echo "<p style='color: red;'><strong>HTTP Error $httpCode</strong></p>";
        if (!empty($response)) {
            echo "<p><strong>Response:</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        }
    }
    
    echo "<hr>";
}

// Run all tests
foreach ($testCases as $test) {
    testEndpoint($test['name'], $test['url'], $test['headers']);
}

// Additional suggestions
echo "<h2>Troubleshooting Tips</h2>";
echo "<ul>";
echo "<li><strong>If all tests show 403:</strong> Your API key might be invalid or lack permissions</li>";
echo "<li><strong>If generic API test works but images fail:</strong> Image endpoints might need different authentication</li>";
echo "<li><strong>If 404 on image endpoints:</strong> The entryPoint might be disabled or use different URL format</li>";
echo "<li><strong>Contact your Espo admin to:</strong>";
echo "<ul>";
echo "<li>Verify the API key has read permissions for attachments/images</li>";
echo "<li>Check if image entryPoint is enabled</li>";
echo "<li>Confirm the correct URL format for images</li>";
echo "</ul>";
echo "</li>";
echo "</ul>";
?>