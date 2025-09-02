<?php
// checkout.php - Creates Contact + COrder in EspoCRM and creates CProductOut lines linked to order and product.
// Works without login (session-only cart).

declare(strict_types=1);
session_start();

// ----------------- CONFIG -----------------
const ESPO_BASE   = 'https://ecom.flyhubdigital.com/api/v1';
const ESPO_APIKEY = '7077c399cb6831c2eb97526398fe15cb'; // keep secret
const CURRENCY    = '₱'; // cosmetic only here

// ----------------- HELPERS -----------------
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function http_json(string $method, string $path, array $payload = null, array $headers = []) : array {
    $url = rtrim(ESPO_BASE, '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    $hdr = array_merge([
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Api-Key: ' . ESPO_APIKEY,
    ], $headers);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => $hdr,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }
    $raw  = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    $json = json_decode((string)$raw, true);
    return [
        'ok'   => ($http >= 200 && $http < 300),
        'http' => $http,
        'data' => is_array($json) ? $json : [],
        'raw'  => $raw,
        'err'  => $err,
        'url'  => $url,
    ];
}

function fetch_products(): array {
    // Same loader pattern as index.php
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
    foreach (fetch_products() as $p) {
        $id = (string)($p['id'] ?? '');
        if ($id === '') continue;
        $map[$id] = $p;
    }
    return $map;
}

// If your store product IDs differ from Espo Product IDs, map here.
function mapStoreIdToEspoProductId(string $storeId): string {
    // Default: assume same id
    return $storeId;
}

// Safe number
function n($v){ return (float)preg_replace('/[^0-9.\-]/','',(string)$v); }

// ----------------- ESPO ACTIONS -----------------
function espo_create_contact(array $input): array {
    $payload = [
        'firstName'    => $input['first_name'] ?? '',
        'lastName'     => $input['last_name']  ?? '',
        'emailAddress' => $input['email']      ?? '',
        'phoneNumber'  => $input['phone']      ?? null,
        'source'       => 'website-checkout',
    ];
    return http_json('POST', 'Contact', $payload);
}

function espo_create_order(array $order): array {
    $payload = [
        'name'          => $order['name'] ?? ('Web Order ' . date('Y-m-d H:i')),
        'status'        => 'New',
        'currency'      => $order['currency'] ?? 'PHP',
        'subtotal'      => $order['subtotal'] ?? 0,
        'tax'           => $order['vat'] ?? 0,
        'shippingCost'  => $order['shipping'] ?? 0,
        'total'         => $order['total'] ?? 0,

        // Address fields as per your COrder properties
        'addressCountry' => $order['country'] ?? '',
        'addressCity'    => $order['city'] ?? '',
        'addressState'   => $order['state'] ?? '',
        'cZIP'           => $order['zip'] ?? '',

        // Optional street line if you have one in COrder schema
        // 'addressStreet' => $order['addressStreet'] ?? '',

        'description'     => $order['notes'] ?? '',
    ];

    return http_json('POST', 'COrder', $payload);
}

function espo_set_order_contact(string $orderId, string $contactId): bool {
    // Try 1: set contactId field if present
    $r = http_json('PATCH', 'COrder/' . rawurlencode($orderId), ['contactId' => $contactId]);
    if ($r['ok']) return true;

    // Try 2: link name 'contact' many-to-one
    $r = http_json('POST', 'COrder/' . rawurlencode($orderId) . '/contact', ['id' => $contactId]);
    if ($r['ok']) return true;

    // Try 3: link name 'contacts' many-to-many
    $r = http_json('POST', 'COrder/' . rawurlencode($orderId) . '/contacts', ['id' => $contactId]);
    return $r['ok'];
}

// Create line, set product via PATCH, then relate the line from the Order side
function espo_create_cproductout_and_link(array $item, string $orderId): array {
    $qty   = (int)($item['qty'] ?? 1);
    $name  = (string)($item['name'] ?? 'Line');
    $prodId = (string)($item['crmProdId'] ?? '');

    // 1) Create the CProductOut record
    // Keep only writable fields based on your metadata
    $payload = [
        'name'     => $name . ' x ' . $qty,
        'quantity' => $qty,
        // Do not send unitPrice or sKU because they are foreign read-only in your metadata
    ];

    $create = http_json('POST', 'CProductOut', $payload);
    if (!$create['ok']) {
        return ['ok' => false, 'step' => 'create', 'resp' => $create];
    }
    $lineId = (string)($create['data']['id'] ?? '');
    if ($lineId === '') {
        return ['ok' => false, 'step' => 'create-id-missing', 'resp' => $create];
    }

    // 2) Set product on the line via PATCH CProductOut/{id}
    if ($prodId) {
        $p = http_json('PATCH', 'CProductOut/' . rawurlencode($lineId), ['productId' => $prodId]);
        if (!$p['ok']) {
            return ['ok' => false, 'step' => 'patch-product', 'lineId' => $lineId, 'resp' => $p];
        }
    }

    // 3) Link the line to the order from the order side, link name productOuts
    $link = http_json('POST', 'COrder/' . rawurlencode($orderId) . '/productOuts', ['id' => $lineId]);
    if (!$link['ok']) {
        return ['ok' => false, 'step' => 'link-from-order', 'lineId' => $lineId, 'resp' => $link];
    }

    return ['ok' => true, 'lineId' => $lineId];
}

// ----------------- SUBMIT HANDLER -----------------
$flash = ['type' => null, 'msg' => null, 'details' => null];

if (($_POST['action'] ?? '') === 'place_order') {
    // 1) Gather form
    $first    = trim($_POST['first_name'] ?? '');
    $last     = trim($_POST['last_name']  ?? '');
    $email    = trim($_POST['email']      ?? '');
    $phone    = trim($_POST['phone']      ?? '');
    $country  = trim($_POST['country']    ?? '');
    $address1 = trim($_POST['address1']   ?? '');
    $address2 = trim($_POST['address2']   ?? '');
    $city     = trim($_POST['city']       ?? '');
    $state    = trim($_POST['state']      ?? '');
    $zip      = trim($_POST['zip']        ?? '');
    $notes    = trim($_POST['notes']      ?? '');

    // 2) Parse items from builder, else fallback to session cart
    $lines  = [];
    $posted = json_decode($_POST['order_json'] ?? '[]', true);
    if (isset($posted['items']) && is_array($posted['items'])) {
        $lines = $posted['items'];
    }

    if (!$lines && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $row) {
            $lines[] = [
                'id'    => (string)$row['id'],
                'qty'   => (int)$row['qty'],
                'price' => (float)$row['price'],
            ];
        }
    }

    // Basic validation
    if (!$first || !$last || (!$email && !$phone)) {
        $flash = ['type' => 'error', 'msg' => 'Please provide first name, last name, and at least an email or phone.'];
    } elseif (!$lines) {
        $flash = ['type' => 'error', 'msg' => 'Your order has no items. Please add products.'];
    } else {
        // 3) Re-price on the server using catalog
        $catalog     = catalog_indexed();
        $subtotal    = 0.0;
        $itemsForCrm = [];

        foreach ($lines as $ln) {
            $sid = (string)($ln['id'] ?? '');
            $qty = (int)($ln['qty'] ?? 0);
            if ($sid === '' || $qty <= 0) continue;

            $cat   = $catalog[$sid] ?? null;
            $price = $cat ? (float)($cat['price'] ?? 0) : (float)($ln['price'] ?? 0);
            $line  = $price * $qty;
            $subtotal += $line;

            $itemsForCrm[] = [
                'storeId'   => $sid,
                'crmProdId' => mapStoreIdToEspoProductId($sid),
                'qty'       => $qty,
                'price'     => $price,
                'name'      => $cat['name'] ?? ($ln['name'] ?? $sid),
            ];
        }

        $vat      = round($subtotal * 0.12, 2);
        $shipping = 0.00; // adjust if you have rules
        $total    = round($subtotal + $vat + $shipping, 2);

        if (!$itemsForCrm) {
            $flash = ['type' => 'error', 'msg' => 'No valid items to submit.'];
        } else {
            // 4) Create Contact
            $cRes = espo_create_contact([
                'first_name' => $first,
                'last_name'  => $last,
                'email'      => $email,
                'phone'      => $phone,
            ]);

            if (!$cRes['ok']) {
                $flash = ['type' => 'error', 'msg' => 'Failed to create contact in CRM.', 'details' => $cRes['raw']];
            } else {
                $contactId = (string)($cRes['data']['id'] ?? '');

                // 5) Create Order
                $oRes = espo_create_order([
                    'name'      => 'Web Order ' . date('Y-m-d H:i:s'),
                    'subtotal'  => $subtotal,
                    'vat'       => $vat,
                    'shipping'  => $shipping,
                    'total'     => $total,
                    'currency'  => 'PHP',

                    // Address values for COrder fields used above
                    'country'   => $country,
                    'city'      => $city,
                    'state'     => $state,
                    'zip'       => $zip,

                    // If you add addressStreet in espo_create_order, pass it here
                    // 'addressStreet' => trim($address1 . ($address2 ? (' ' . $address2) : '')),

                    'notes'     => $notes,
                ]);

                if (!$oRes['ok']) {
                    $flash = ['type' => 'error', 'msg' => 'Failed to create order in CRM.', 'details' => $oRes['raw']];
                } else {
                    $orderId = (string)($oRes['data']['id'] ?? '');

                    // 6) Link Contact to Order
                    $linkedContact = $contactId ? espo_set_order_contact($orderId, $contactId) : false;

                    // 7) Create CProductOut lines and link them to order and product
                    $lineCreateOk = true;
                    $lineErrors = [];

                    foreach ($itemsForCrm as $it) {
                        $r = espo_create_cproductout_and_link($it, $orderId);
                        if (!$r['ok']) {
                            $lineCreateOk = false;
                            $who = $it['crmProdId'] ?? $it['storeId'] ?? 'unknown';
                            $step = $r['step'] ?? 'unknown';
                            $http = isset($r['resp']['http']) ? (' http ' . $r['resp']['http']) : '';
                            $lineErrors[] = $step . ' for ' . $who . $http;
                        }
                    }

                    // Optional: clear session cart on success
                    // $_SESSION['cart'] = [];

                    if ($orderId) {
                        $flash = [
                            'type' => 'success',
                            'msg'  => 'Order placed. CRM Order ID: ' . h($orderId) .
                                      ($linkedContact ? '' : ' (contact link pending)') .
                                      ($lineCreateOk ? '' : ' (some lines failed: ' . h(implode(', ', $lineErrors)) . ')'),
                        ];
                    } else {
                        $flash = ['type' => 'error', 'msg' => 'Order created but missing CRM ID.'];
                    }
                }
            }
        }
    }
}
?>



<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Checkout</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/font-icons.css">
    <link rel="stylesheet" href="css/plugins.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
<div class="body-wrapper">

    <?php include 'partials/nav.php';?>

    <div class="ltn__checkout-area mb-100">
        <div class="container">
            <?php if ($flash['type']): ?>
                <div class="alert <?php echo $flash['type']==='success'?'alert-success':'alert-danger'; ?>" role="alert">
                    <?php echo h($flash['msg']); ?>
                    <?php if (!empty($flash['details'])): ?>
                        <details class="mt-2"><summary>Details</summary><pre style="white-space:pre-wrap;"><?php echo h($flash['details']); ?></pre></details>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form id="checkout-form" method="post" action="checkout.php" novalidate>
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="order_json" id="order-json" value="[]">

                <div class="row">
                    <div class="col-lg-6">
                        <div class="ltn__checkout-inner">
                            <div class="ltn__checkout-single-content mt-0">
                                <h4 class="title-2">Billing Details</h4>
                                <div class="ltn__checkout-single-content-info">
                                    <h6>Personal Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-item input-item-name ltn__custom-icon">
                                                <input type="text" name="first_name" placeholder="First name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-name ltn__custom-icon">
                                                <input type="text" name="last_name" placeholder="Last name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-email ltn__custom-icon">
                                                <input type="email" name="email" placeholder="Email address">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-phone ltn__custom-icon">
                                                <input type="text" name="phone" placeholder="Phone number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Country</h6>
                                            <div class="input-item">
                                                <select class="nice-select" name="country">
                                                    <option value="">Select Country</option>
                                                    <option>Philippines</option>
                                                    <option>United States (US)</option>
                                                    <option>United Kingdom (UK)</option>
                                                    <option>Australia</option>
                                                    <option>Canada</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12">
                                            <h6>Address</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-item">
                                                        <input type="text" name="address1" placeholder="House number and street name" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-item">
                                                        <input type="text" name="address2" placeholder="Apartment, suite, unit etc. (optional)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Town / City</h6>
                                            <div class="input-item">
                                                <input type="text" name="city" placeholder="City" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>State</h6>
                                            <div class="input-item">
                                                <input type="text" name="state" placeholder="State">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Zip</h6>
                                            <div class="input-item">
                                                <input type="text" name="zip" placeholder="Zip" required>
                                            </div>
                                        </div>
                                    </div>

                                    <h6>Order Notes (optional)</h6>
                                    <div class="input-item input-item-textarea ltn__custom-icon">
                                        <textarea name="notes" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="ltn__checkout-payment-method mt-40">
                                <h4 class="title-2">Payment Method</h4>
                                <div id="checkout_accordion_1">
                                    <div class="card">
                                        <h5 class="ltn__card-title" data-bs-toggle="collapse" data-bs-target="#faq-item-2-2" aria-expanded="true">
                                            Cash on delivery
                                        </h5>
                                        <div id="faq-item-2-2" class="collapse show" data-bs-parent="#checkout_accordion_1">
                                            <div class="card-body">
                                                <p>Pay with cash upon delivery.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ltn__payment-note mt-30 mb-30">
                                    <p>Your personal data will be used to process your order.</p>
                                </div>
                                <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">Place order</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <!-- ORDER SUMMARY (product picker + lines) -->
                        <div class="shoping-cart-total mt-50" id="order-summary">
                            <h4 class="title-2">Order Summary</h4>

                            <div class="mb-20" style="position:relative;">
                                <div class="row g-2">
                                    <div class="col-12 col-md-8" style="position:relative;">
                                        <input id="product-search" type="text" class="form-control" placeholder="Search products..." autocomplete="off">
                                        <div id="product-suggestions" class="box-shadow" style="display:none; position:absolute; z-index:9999; background:#fff; width:100%; max-height:260px; overflow:auto; border:1px solid #eee; top:100%; left:0;"></div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <input id="product-qty" type="number" min="1" value="1" class="form-control" aria-label="Quantity">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <button id="add-to-order" type="button" class="theme-btn-1 btn btn-effect-1 w-100" disabled>Add</button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Type to search, click a suggestion, set qty, then Add.</small>
                            </div>

                            <table class="table">
                                <tbody id="order-lines"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="ltn__brand-logo-area  ltn__brand-logo-1 section-bg-1 pt-35 pb-35 plr--5">
        <div class="container-fluid">
            <div class="row ltn__brand-logo-active">
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/1.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/2.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/3.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/4.png" alt="Brand Logo"></div></div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php';?>
</div>

<script src="js/plugins.js"></script>
<script src="js/main.js"></script>

<!-- Order Summary logic (search, add, totals) -->
<script>
(function () {
    const CURRENCY = "₱";
    const VAT_RATE = 0.12;
    const SHIPPING_FLAT = 0;
    const SUGGESTION_LIMIT = 8;

    // 👉 Hardcoded product (edit name/price as needed)
    const FALLBACK_PRODUCTS = [
        {
            id: "68b3e53c35cbfbf5c",
            name: "Hardcoded Bouquet",
            price: 1200.00,
            image_url: "img/product/placeholder.png",
            is_active: true
        }
    ];

    const els = {
        search: document.getElementById("product-search"),
        suggestions: document.getElementById("product-suggestions"),
        qty: document.getElementById("product-qty"),
        addBtn: document.getElementById("add-to-order"),
        lines: document.getElementById("order-lines"),
        orderJson: document.getElementById("order-json")
    };

    const state = {
        // seed with hardcoded product so it always works
        products: FALLBACK_PRODUCTS.slice(),
        selectedProduct: null,
        cart: []
    };

    const money = n => CURRENCY + Number(n || 0).toFixed(2);
    const safe = s => (s ?? "").toString();
    const pickable = p => p && p.is_active !== false && (p.price !== null && p.price !== undefined);

    // If your products endpoint starts working again, this will add/merge them
    function candidateUrls() {
        const ts = "nocache=" + Date.now();
        const here = new URL(window.location.href);
        const sameDir = new URL("products.php?" + ts, here);
        const root = new URL("/products.php?" + ts, window.location.origin);
        const baseEl = document.querySelector("base[href]");
        const fromBase = baseEl ? new URL("products.php?" + ts, baseEl.href) : null;
        const urls = [sameDir, root];
        if (fromBase) urls.unshift(fromBase);
        const seen = new Set();
        return urls.filter(u => { const k=u.toString(); if (seen.has(k)) return false; seen.add(k); return true; });
    }

    function withTimeout(ms, promise) {
        const ctrl = new AbortController();
        const t = setTimeout(() => ctrl.abort(), ms);
        return promise(ctrl.signal).finally(() => clearTimeout(t));
    }

    async function fetchProducts() {
        for (const u of candidateUrls()) {
            try {
                const data = await withTimeout(7000, (signal) =>
                    fetch(u.toString(), { credentials: "same-origin", signal }).then(r => r.json())
                );
                if (data && data.ok && Array.isArray(data.items)) {
                    return data.items.filter(pickable);
                }
            } catch (e) {}
        }
        return [];
    }

    function mergeUniqueById(existing, extra) {
        const byId = new Map(existing.map(p => [String(p.id), p]));
        extra.forEach(p => { byId.set(String(p.id), p); });
        return Array.from(byId.values());
    }

    function searchProducts(q) {
        q = (q || "").trim().toLowerCase();
        // 👉 if empty query, show everything (so the hardcoded product appears immediately)
        if (!q) return state.products.slice(0, SUGGESTION_LIMIT);
        return state.products.filter(p => {
            const hay = [safe(p.id), safe(p.name), safe(p.description), safe(p.category_name), safe(p.sku)]
                .join(" ").toLowerCase();
            return hay.includes(q);
        }).slice(0, SUGGESTION_LIMIT);
    }

    function renderSuggestions(list) {
        if (!list.length) {
            els.suggestions.style.display = "none";
            els.suggestions.innerHTML = "";
            return;
        }
        const items = list.map(p => {
            const img = safe(p.image_url) || "img/product/placeholder.png";
            const price = money(p.price);
            const name = safe(p.name) || p.id;
            return `
                <button type="button" class="w-100 text-start suggestion-item" data-id="${p.id}"
                        style="display:flex; gap:10px; align-items:center; padding:8px 10px; background:#fff; border:0; border-bottom:1px solid #f1f1f1; cursor:pointer;">
                    <img src="${img}" alt="${name}" style="width:38px;height:38px;object-fit:cover;border-radius:4px;">
                    <span style="flex:1 1 auto; font-size:14px; line-height:1.2;">${name}</span>
                    <span style="white-space:nowrap; font-weight:600;">${price}</span>
                </button>`;
        }).join("");
        els.suggestions.innerHTML = items;
        els.suggestions.style.display = "block";
    }

    function selectById(id) {
        const p = state.products.find(x => String(x.id) === String(id));
        if (!p) return;
        state.selectedProduct = p;
        els.search.value = p.name || p.id;
        els.addBtn.disabled = false;
        els.suggestions.style.display = "none";
    }

    function addSelectedToCart() {
        const p = state.selectedProduct;
        const qty = Math.max(1, parseInt(els.qty.value, 10) || 1);
        if (!p) return;
        const existing = state.cart.find(i => String(i.id) === String(p.id));
        if (existing) existing.qty += qty;
        else state.cart.push({ id: p.id, name: p.name || p.id, price: Number(p.price || 0), qty });
        state.selectedProduct = null;
        els.addBtn.disabled = true;
        els.qty.value = 1;
        renderCart();
    }

    function removeFromCart(id) {
        state.cart = state.cart.filter(i => String(i.id) !== String(id));
        renderCart();
    }

    function updateQty(id, newQty) {
        const item = state.cart.find(i => String(i.id) === String(id));
        if (!item) return;
        item.qty = Math.max(1, parseInt(newQty, 10) || 1);
        renderCart();
    }

    function renderCart() {
        const rows = [];
        let subtotal = 0;

        state.cart.forEach(i => {
            const line = (i.price || 0) * (i.qty || 0);
            subtotal += line;
            rows.push(`
                <tr data-line="${i.id}">
                    <td>
                        ${safe(i.name)} <strong>× 
                            <input type="number" min="1" value="${i.qty}" aria-label="Quantity"
                                   style="width:64px; display:inline-block; margin-left:2px;"
                                   class="form-control form-control-sm line-qty">
                        </strong>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-line" title="Remove">×</button>
                    </td>
                    <td>${money(line)}</td>
                </tr>`);
        });

        const vat = subtotal * VAT_RATE;
        const shipping = state.cart.length ? SHIPPING_FLAT : 0;
        const total = subtotal + vat + shipping;

        rows.push(`
            <tr><td>Shipping and Handling</td><td>${money(shipping)}</td></tr>
            <tr><td>VAT</td><td>${money(vat)}</td></tr>
            <tr><td><strong>Order Total</strong></td><td><strong>${money(total)}</strong></td></tr>`);

        els.lines.innerHTML = rows.join("");
        els.orderJson.value = JSON.stringify({
            items: state.cart.map(i => ({ id: i.id, qty: i.qty, price: i.price })),
            subtotal, vat, shipping, total, currency: CURRENCY
        });
    }

    // --- Events
    let debounce;
    els.search.addEventListener("input", function () {
        state.selectedProduct = null;
        els.addBtn.disabled = true;
        clearTimeout(debounce);
        const q = this.value || "";
        debounce = setTimeout(() => renderSuggestions(searchProducts(q)), 120);
    });

    // Show suggestions immediately on focus (so the hardcoded product is visible)
    els.search.addEventListener("focus", function () {
        renderSuggestions(searchProducts(els.search.value || ""));
    });

    els.suggestions.addEventListener("mousedown", function (e) {
        const btn = e.target.closest(".suggestion-item");
        if (!btn) return;
        e.preventDefault();
        selectById(btn.getAttribute("data-id"));
    });

    document.addEventListener("click", function (e) {
        if (!e.target.closest("#product-suggestions") && !e.target.closest("#product-search")) {
            els.suggestions.style.display = "none";
        }
    });

    els.addBtn.addEventListener("click", addSelectedToCart);

    els.lines.addEventListener("click", function (e) {
        const rm = e.target.closest(".remove-line");
        if (rm) {
            const tr = rm.closest("tr");
            if (tr) removeFromCart(tr.getAttribute("data-line"));
        }
    });

    els.lines.addEventListener("change", function (e) {
        const qtyInput = e.target.closest(".line-qty");
        if (qtyInput) {
            const tr = qtyInput.closest("tr");
            updateQty(tr.getAttribute("data-line"), qtyInput.value);
        }
    });

    // 1) Preload session cart into builder (unchanged)
    fetch("cart.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: "action=get", credentials: "same-origin" })
      .then(r => r.json()).then(res => {
        if (res && res.ok && res.cart && Array.isArray(res.cart.items)) {
            state.cart = res.cart.items.map(i => ({ id: i.id, name: i.name, price: Number(i.price || 0), qty: Number(i.qty || 1) }));
            renderCart();
        }
      }).catch(()=>{});

    // 2) Try to load products; if successful, merge with hardcoded one
    fetchProducts().then(items => {
        if (Array.isArray(items) && items.length) {
            state.products = mergeUniqueById(state.products, items.filter(pickable));
        }
    });
})();
</script>

</body>
</html>
