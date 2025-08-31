<?php
// cart.php — session-based cart API (no login required)
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=UTF-8');

// ---------- Helpers ----------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function fetch_catalog(): array {
    // Same bootstrap pattern you already use
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $src    = $scheme . '://' . $host . ($base ? $base : '') . '/products.php?nocache=' . time();

    $json = false;
    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $json = @file_get_contents($src, false, $ctx);
    }
    if ($json === false && function_exists('curl_init')) {
        $ch = curl_init($src);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $json = curl_exec($ch);
        curl_close($ch);
    }
    $data = @json_decode($json, true);
    if (!is_array($data) || empty($data['ok'])) return [];
    return $data['items'] ?? [];
}

function catalog_indexed(): array {
    static $map = null;
    if ($map !== null) return $map;
    $map = [];
    foreach (fetch_catalog() as $p) {
        $id = (string)($p['id'] ?? '');
        if ($id === '') continue;
        $map[$id] = $p;
    }
    return $map;
}

function cart_init(): void {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = []; // id => ['id','name','price','image_url','qty']
    }
}
function cart_set(string $id, array $row): void {
    $_SESSION['cart'][$id] = $row;
}
function cart_get(): array {
    cart_init();
    return $_SESSION['cart'];
}
function cart_remove(string $id): void {
    cart_init();
    unset($_SESSION['cart'][$id]);
}
function cart_clear(): void {
    $_SESSION['cart'] = [];
}
function cart_summary(): array {
    $items = cart_get();
    $count = 0;
    $subtotal = 0.0;
    foreach ($items as $it) {
        $q = max(0, (int)($it['qty'] ?? 0));
        $count += $q;
        $subtotal += ((float)($it['price'] ?? 0)) * $q;
    }
    return [
        'items' => array_values($items),
        'count' => $count,
        'subtotal' => round($subtotal, 2),
    ];
}

// ---------- Router ----------
$action = $_POST['action'] ?? $_GET['action'] ?? 'get';

try {
    cart_init();

    if ($action === 'get') {
        echo json_encode(['ok' => true, 'cart' => cart_summary()]);
        exit;
    }

    if ($action === 'clear') {
        cart_clear();
        echo json_encode(['ok' => true, 'cart' => cart_summary()]);
        exit;
    }

    if ($action === 'remove') {
        $id = (string)($_POST['id'] ?? $_GET['id'] ?? '');
        if ($id === '') throw new RuntimeException('Missing id');
        cart_remove($id);
        echo json_encode(['ok' => true, 'cart' => cart_summary()]);
        exit;
    }

    if ($action === 'add' || $action === 'update') {
        $id  = (string)($_POST['id'] ?? '');
        $qty = (int)($_POST['qty'] ?? 1);
        if ($id === '') throw new RuntimeException('Missing id');
        if ($qty < 0)  $qty = 0;

        $catalog = catalog_indexed();
        if (!isset($catalog[$id])) {
            throw new RuntimeException('Product not found');
        }
        $p = $catalog[$id];
        $name = (string)($p['name'] ?? 'Product');
        $price = (float)($p['price'] ?? 0);
        $img = (string)(($p['image_url'] ?? '') ?: 'img/product/placeholder.png');

        if ($qty === 0) {
            cart_remove($id);
        } else {
            // If already in cart, increase for 'add', set for 'update'
            $existing = cart_get()[$id] ?? null;
            $finalQty = ($action === 'add' && $existing) ? ((int)$existing['qty'] + $qty) : $qty;
            cart_set($id, [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'image_url' => $img,
                'qty' => $finalQty,
            ]);
        }
        echo json_encode(['ok' => true, 'cart' => cart_summary()]);
        exit;
    }

    throw new RuntimeException('Unknown action');
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage(), 'cart' => cart_summary()]);
}
