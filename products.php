<?php
// products.php
ini_set('display_errors', 0);               // keep clean JSON for prod
ini_set('log_errors', 1);
error_reporting(E_ALL);

$host = "54.151.189.32";
$db   = "sme_ecom";
$user = "flyhubapp";
$pass = "KatieBruha_02";

$debug = isset($_GET['debug']) ? 1 : 0;
$payload = ['ok' => false, 'error' => null, 'items' => [], 'meta' => []];

$mysqli = @new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
  $payload['error'] = 'DB connect error: ' . $mysqli->connect_error;
  header('Content-Type: application/json');
  echo json_encode($payload);
  exit;
}

$sql = "
SELECT 
  p.id,
  p.name,
  p.description,
  p.price,
  p.stock,
  p.s_k_u AS sku,
  p.is_available,
  COALESCE(p.deleted,0) AS deleted,
  p.category_id,
  c.name AS category_name,
  c.slug AS category_slug,
  p.images_id
FROM c_product p
LEFT JOIN c_category c ON c.id = p.category_id
WHERE COALESCE(p.deleted,0) = 0
  AND p.is_available = 1
ORDER BY COALESCE(p.created_at, '1970-01-01') DESC, p.id DESC
";

$res = $mysqli->query($sql);
if (!$res) {
  $payload['error'] = 'SQL error: ' . $mysqli->error;
  header('Content-Type: application/json');
  echo json_encode($payload);
  exit;
}

while ($row = $res->fetch_assoc()) {
  // normalize types
  $row['price'] = is_null($row['price']) ? null : (0 + $row['price']);
  $row['stock'] = is_null($row['stock']) ? null : (int)$row['stock'];
  $row['image_url'] = !empty($row['images_id'])
    ? ('image-proxy.php?id=' . $row['images_id'] . '&size=large')
    : null;
  $payload['items'][] = $row;

}
$payload['ok'] = true;
$payload['meta'] = ['count' => count($payload['items'])];

header('Content-Type: application/json');
echo json_encode($payload);
