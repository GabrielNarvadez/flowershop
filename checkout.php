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
    $payload = [
        'name'     => $name . ' x ' . $qty,
        'quantity' => $qty,
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
                            'msg'  => 'Order placed. CRM Order ID: ',
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

    <style>
      /* ---------- Order Summary styles ---------- */
      #order-summary .card {
        border: 1px solid #e9ecef;
        border-radius: .5rem;
      }
      #order-summary .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
      }
      /* Search row */
      .os-search .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: .375rem;
        box-shadow: 0 6px 24px rgba(0,0,0,.06);
        max-height: 260px;
        overflow: auto;
      }
      .os-search .suggestions.d-none { display: none; }
      .os-suggestion {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 8px 10px;
        border: 0;
        border-bottom: 1px solid #f3f3f3;
        background: #fff;
        cursor: pointer;
        text-align: left;
      }
      .os-suggestion:last-child { border-bottom: 0; }
      .os-suggestion:hover, .os-suggestion:focus { background: #f9fafb; }
      .os-suggestion img {
        width: 38px; height: 38px; object-fit: cover; border-radius: 4px;
      }
      .os-suggestion .name { flex: 1 1 auto; font-size: 14px; line-height: 1.2; }
      .os-suggestion .price { font-weight: 600; white-space: nowrap; }

      /* Table */
      #order-summary .table thead th {
        font-weight: 600;
        border-top: 0;
        background: #f8f9fa;
      }
      #order-summary .qty-input {
        width: 68px;
        text-align: center;
      }
      #order-summary .remove-line.btn {
        line-height: 1; padding: .25rem .5rem;
      }
      #order-summary tfoot th, #order-summary tfoot td {
        border-top: 0;
      }
      #order-summary tfoot .fw-bold {
        font-size: 1.05rem;
      }
      /* Single-row layout */
.os-row{
  display:flex;
  align-items:stretch;
  gap:.5rem;
  flex-wrap:nowrap;      /* keep everything on one line */
}

/* input-group width so qty doesn't get tiny; tweak to taste */
.os-qa{ width: 230px; min-width: 230px; }
@media (max-width: 576px){
  .os-qa{ width: 200px; min-width: 200px; }
}

/* (optional) center the number text */
.os-qa input[type="number"]{ text-align:center; }

/* keep your suggestions styles below (unchanged) */

/* Prevent the qty and button from collapsing */
.os-qty{ width:92px; text-align:center; }
.os-add-btn{ white-space:nowrap; min-width:110px; }

/* Suggestions dropdown (uses same class names you already have) */
.os-search .suggestions{
  position:absolute;
  top:100%;
  left:0;
  right:0;
  z-index:1050;
  background:#fff;
  border:1px solid #e9ecef;
  border-radius:.375rem;
  box-shadow:0 6px 24px rgba(0,0,0,.06);
  max-height:260px;
  overflow:auto;
}
.os-search .suggestions.d-none{ display:none; }
.os-suggestion{ display:flex; align-items:center; gap:10px; width:100%; padding:8px 10px; border:0; border-bottom:1px solid #f3f3f3; background:#fff; cursor:pointer; text-align:left; }
.os-suggestion:last-child{ border-bottom:0; }
.os-suggestion:hover,.os-suggestion:focus{ background:#f9fafb; }
.os-suggestion img{ width:38px; height:38px; object-fit:cover; border-radius:4px; }
.os-suggestion .name{ flex:1 1 auto; font-size:14px; line-height:1.2; }
.os-suggestion .price{ font-weight:600; white-space:nowrap; }

    </style>
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
                        <div id="order-summary" class="mt-50">
                          <div class="card shadow-sm">
                            <div class="card-header d-flex align-items-center justify-content-between">
                              <h4 class="title-2 m-0">Order Summary</h4>
                            </div>

                            <div class="card-body">
                            <div class="mb-3 os-search d-none">
  <label for="product-search" class="form-label">Search products</label>

  <!-- Single row -->
  <div class="os-row">
    <!-- Search (flexes to fill) -->
    <div class="flex-grow-1 position-relative">
      <input id="product-search" type="text" class="form-control" placeholder="Type to search" autocomplete="off">
      <div id="product-suggestions" class="suggestions d-none"></div>
    </div>

    <!-- Qty -->
<!-- Qty + Add -->
<div class="input-group os-qa">
  <input id="product-qty" type="number" min="1" value="1"
         class="form-control" aria-label="Quantity">
  <button id="add-to-order" type="button" class="btn btn-primary" disabled>Add</button>
</div>

  </div>

  <small class="text-muted">Pick a suggestion, set quantity, then Add.</small>
</div>


                              <!-- Lines -->
                              <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                  <thead>
                                    <tr>
                                      <th scope="col">Item</th>
                                      <th scope="col" class="text-center" style="width:110px;">Qty</th>
                                      <th scope="col" class="text-end" style="width:140px;">Line total</th>
                                      <th scope="col" class="text-end" style="width:56px;"><span class="visually-hidden">Remove</span></th>
                                    </tr>
                                  </thead>
                                  <tbody id="order-lines"></tbody>
                                  <tfoot id="order-totals"></tfoot>
                                </table>
                              </div>
                            </div>
                          </div>
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

    // Hardcoded product so it always works
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
        totals: document.getElementById("order-totals"),
        orderJson: document.getElementById("order-json")
    };

    const state = {
        products: FALLBACK_PRODUCTS.slice(),
        selectedProduct: null,
        cart: []
    };

    const money = n => CURRENCY + Number(n || 0).toFixed(2);
    const safe = s => (s ?? "").toString();
    const pickable = p => p && p.is_active !== false && (p.price !== null && p.price !== undefined);

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
        if (!q) return state.products.slice(0, SUGGESTION_LIMIT);
        return state.products.filter(p => {
            const hay = [safe(p.id), safe(p.name), safe(p.description), safe(p.category_name), safe(p.sku)]
                .join(" ").toLowerCase();
            return hay.includes(q);
        }).slice(0, SUGGESTION_LIMIT);
    }

    function hideSuggestions() {
        els.suggestions.classList.add("d-none");
        els.suggestions.innerHTML = "";
    }

    function renderSuggestions(list) {
        if (!list.length) { hideSuggestions(); return; }
        els.suggestions.innerHTML = list.map(p => {
            const img = safe(p.image_url) || "img/product/placeholder.png";
            const price = money(p.price);
            const name = safe(p.name) || p.id;
            return `
                <button type="button" class="os-suggestion suggestion-item" data-id="${p.id}">
                    <img src="${img}" alt="${name}">
                    <span class="name">${name}</span>
                    <span class="price">${price}</span>
                </button>`;
        }).join("");
        els.suggestions.classList.remove("d-none");
    }

    function selectById(id) {
        const p = state.products.find(x => String(x.id) === String(id));
        if (!p) return;
        state.selectedProduct = p;
        els.search.value = p.name || p.id;
        els.addBtn.disabled = false;
        hideSuggestions();
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
        const bodyRows = [];
        let subtotal = 0;

        state.cart.forEach(i => {
            const line = (i.price || 0) * (i.qty || 0);
            subtotal += line;
            bodyRows.push(`
                <tr data-line="${i.id}">
                    <td class="align-middle">
                        <div class="fw-medium">${safe(i.name)}</div>
                    </td>
                    <td class="text-center align-middle" style="width:110px;">
                        <input type="number" min="1" value="${i.qty}" aria-label="Quantity" class="form-control form-control-sm qty-input line-qty">
                    </td>
                    <td class="text-end align-middle" style="width:140px;">${money(line)}</td>
                    <td class="text-end align-middle" style="width:56px;">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Remove">&times;</button>
                    </td>
                </tr>`);
        });

        const vat = subtotal * VAT_RATE;
        const shipping = state.cart.length ? SHIPPING_FLAT : 0;
        const total = subtotal + vat + shipping;

        els.lines.innerHTML = bodyRows.join("");
        els.totals.innerHTML = `
            <tr>
                <th colspan="2" class="text-end">Shipping and Handling</th>
                <td class="text-end">${money(shipping)}</td>
                <td></td>
            </tr>
            <tr>
                <th colspan="2" class="text-end">VAT</th>
                <td class="text-end">${money(vat)}</td>
                <td></td>
            </tr>
            <tr class="fw-bold">
                <th colspan="2" class="text-end">Order Total</th>
                <td class="text-end">${money(total)}</td>
                <td></td>
            </tr>`;

        els.orderJson.value = JSON.stringify({
            items: state.cart.map(i => ({ id: i.id, qty: i.qty, price: i.price })),
            subtotal, vat, shipping, total, currency: CURRENCY
        });
    }

    // Events
    let debounce;
    els.search.addEventListener("input", function () {
        state.selectedProduct = null;
        els.addBtn.disabled = true;
        clearTimeout(debounce);
        const q = this.value || "";
        debounce = setTimeout(() => renderSuggestions(searchProducts(q)), 120);
    });

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
            hideSuggestions();
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

    // 1) Preload session cart into builder
    fetch("cart.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: "action=get", credentials: "same-origin" })
      .then(r => r.json()).then(res => {
        if (res && res.ok && res.cart && Array.isArray(res.cart.items)) {
            state.cart = res.cart.items.map(i => ({ id: i.id, name: i.name, price: Number(i.price || 0), qty: Number(i.qty || 1) }));
            renderCart();
        }
      }).catch(()=>{});

    // 2) Load products and merge with fallback
    fetchProducts().then(items => {
        if (Array.isArray(items) && items.length) {
            state.products = mergeUniqueById(state.products, items.filter(pickable));
        }
    });
})();
</script>

</body>
</html>
