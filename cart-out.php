<?php
// cart-out.php — dynamic cart page (guest/session cart)
declare(strict_types=1);
session_start();

// ---- Helpers ----
function peso($n): string {
    $f = number_format((float)$n, 2);
    return '₱' . $f;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Pull cart from session (populated via cart.php API)
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) $cart = [];

// Compute totals
$subtotal = 0.0;
$count    = 0;
foreach ($cart as $row) {
    $qty   = max(0, (int)($row['qty'] ?? 0));
    $price = (float)($row['price'] ?? 0);
    $subtotal += $price * $qty;
    $count    += $qty;
}

// tweak these if you want shipping/tax
$SHIPPING = 0.00;   // flat shipping (₱)
$VAT_RATE = 0.00;   // e.g. 0.12 for 12%
$vat      = $subtotal * $VAT_RATE;
$total    = $subtotal + $SHIPPING + $vat;
?>
<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Your Cart</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Place favicon.png in the root directory -->
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" />
    <!-- Font Icons css -->
    <link rel="stylesheet" href="css/font-icons.css">
    <!-- plugins css -->
    <link rel="stylesheet" href="css/plugins.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Responsive css -->
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>

<div class="body-wrapper">
<?php include 'partials/nav.php';?>

    <!-- SHOPING CART AREA START -->
    <div class="liton__shoping-cart-area mb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="shoping-cart-inner">

                        <div class="shoping-cart-table table-responsive">
                            <table class="table">
                                <thead class="d-none d-md-table-header-group">
                                    <tr>
                                        <th class="cart-product-remove">Remove</th>
                                        <th class="cart-product-image">Image</th>
                                        <th class="cart-product-info">Product</th>
                                        <th class="cart-product-price">Price</th>
                                        <th class="cart-product-quantity">Quantity</th>
                                        <th class="cart-product-subtotal">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-tbody">
                                <?php if (!empty($cart)): ?>
                                    <?php foreach ($cart as $it):
                                        $id   = (string)($it['id'] ?? '');
                                        $name = (string)($it['name'] ?? 'Product');
                                        $img  = (string)(($it['image_url'] ?? '') ?: 'img/product/placeholder.png');
                                        $qty  = max(1, (int)($it['qty'] ?? 1));
                                        $price= (float)($it['price'] ?? 0);
                                        $rowSubtotal = $price * $qty;
                                    ?>
                                    <tr data-id="<?= h($id); ?>">
                                        <td class="cart-product-remove">
                                            <a href="#" class="cart-remove" data-id="<?= h($id); ?>">x</a>
                                        </td>
                                        <td class="cart-product-image">
                                            <a href="product-details.php?id=<?= urlencode($id); ?>">
                                                <img src="<?= h($img); ?>" alt="#">
                                            </a>
                                        </td>
                                        <td class="cart-product-info">
                                            <h4>
                                                <a href="product-details.php?id=<?= urlencode($id); ?>"><?= h($name); ?></a>
                                            </h4>
                                        </td>
                                        <td class="cart-product-price"><?= peso($price); ?></td>
                                        <td class="cart-product-quantity">
                                            <div class="cart-plus-minus">
                                                <input type="text"
                                                       value="<?= (int)$qty; ?>"
                                                       name="qtybutton"
                                                       class="cart-plus-minus-box cart-qty-input"
                                                       data-id="<?= h($id); ?>">
                                            </div>
                                        </td>
                                        <td class="cart-product-subtotal"><?= peso($rowSubtotal); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="cart-coupon-row">
                                        <td colspan="6">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                <div class="cart-coupon">
                                                    <input type="text" name="cart-coupon" placeholder="Coupon code" disabled>
                                                    <button type="button" class="btn theme-btn-2 btn-effect-2" disabled>Apply Coupon</button>
                                                </div>
                                                <div>
                                                    <button id="update-cart" type="button" class="btn theme-btn-2 btn-effect-2">Update Cart</button>
                                                    <button id="clear-cart" type="button" class="btn theme-btn-2 btn-effect-2">Clear Cart</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Your cart is empty. <a href="shop.php">Continue shopping</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="shoping-cart-total mt-50" id="totals-box" <?= empty($cart) ? 'style="display:none;"' : '';?>>
                            <h4>Cart Totals</h4>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Cart Subtotal</td>
                                        <td id="cart-subtotal"><?= peso($subtotal); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping and Handling</td>
                                        <td id="cart-shipping"><?= peso($SHIPPING); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Vat</td>
                                        <td id="cart-vat"><?= peso($vat); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Order Total</strong></td>
                                        <td><strong id="cart-total"><?= peso($total); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="btn-wrapper text-right">
                                <a href="checkout.html" class="theme-btn-1 btn btn-effect-1">Proceed to checkout</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SHOPING CART AREA END -->

    <!-- BRAND LOGO AREA START -->
    <div class="ltn__brand-logo-area  ltn__brand-logo-1 section-bg-1 pt-35 pb-35 plr--5">
        <div class="container-fluid">
            <div class="row ltn__brand-logo-active">
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/1.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/2.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/3.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/4.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/5.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/1.png" alt="Brand Logo"></div></div>
                <div class="col-lg-12"><div class="ltn__brand-logo-item"><img src="img/brand-logo/2.png" alt="Brand Logo"></div></div>
            </div>
        </div>
    </div>
    <!-- BRAND LOGO AREA END -->

    <!-- FOOTER AREA START -->
    <?php include 'partials/footer.php';?>
    <!-- FOOTER AREA END -->

</div>

<!-- All JS Plugins -->
<script src="js/plugins.js"></script>
<!-- Main JS -->
<script src="js/main.js"></script>
<!-- Cart glue (from previous step) -->
<script src="cart.js"></script>

<!-- Page wiring: qty change, remove, clear, live totals -->
<script>
(function () {
  function peso(n){ var x=parseFloat(n||0); if(isNaN(x)) x=0; return "₱"+x.toFixed(2); }
  function api(action, data){
    var body = new URLSearchParams(Object.assign({action:action}, data||{}));
    return fetch("cart.php", {
      method:"POST",
      headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},
      body: body.toString(),
      credentials: "same-origin"
    }).then(function(r){ return r.json(); });
  }

  function render(cart){
    var tbody = document.getElementById("cart-tbody");
    var totalsBox = document.getElementById("totals-box");
    if (!tbody) return;

    if (!cart || !cart.items || !cart.items.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center">Your cart is empty. <a href="shop.php">Continue shopping</a></td></tr>';
      if (totalsBox) totalsBox.style.display = "none";
      // also refresh header mini cart
      try { if (window.refreshMiniCart) window.refreshMiniCart(); } catch(e){}
      return;
    }

    var rows = cart.items.map(function(it){
      var rowSubtotal = parseFloat(it.price||0) * parseInt(it.qty||0,10);
      return ''+
        '<tr data-id="'+String(it.id)+'">'+
          '<td class="cart-product-remove"><a href="#" class="cart-remove" data-id="'+String(it.id)+'">x</a></td>'+
          '<td class="cart-product-image"><a href="product-details.php?id='+encodeURIComponent(it.id)+'"><img src="'+(it.image_url||"img/product/placeholder.png")+'" alt="#"></a></td>'+
          '<td class="cart-product-info"><h4><a href="product-details.php?id='+encodeURIComponent(it.id)+'">'+(it.name||"Product")+'</a></h4></td>'+
          '<td class="cart-product-price">'+peso(it.price)+'</td>'+
          '<td class="cart-product-quantity"><div class="cart-plus-minus">'+
              '<input type="text" value="'+String(it.qty)+'" name="qtybutton" class="cart-plus-minus-box cart-qty-input" data-id="'+String(it.id)+'">'+
            '</div></td>'+
          '<td class="cart-product-subtotal">'+peso(rowSubtotal)+'</td>'+
        '</tr>';
    }).join("");

    rows += ''+
      '<tr class="cart-coupon-row">'+
        '<td colspan="6">'+
          '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">'+
            '<div class="cart-coupon">'+
              '<input type="text" name="cart-coupon" placeholder="Coupon code" disabled>'+
              '<button type="button" class="btn theme-btn-2 btn-effect-2" disabled>Apply Coupon</button>'+
            '</div>'+
            '<div>'+
              '<button id="update-cart" type="button" class="btn theme-btn-2 btn-effect-2">Update Cart</button> '+
              '<button id="clear-cart" type="button" class="btn theme-btn-2 btn-effect-2">Clear Cart</button>'+
            '</div>'+
          '</div>'+
        '</td>'+
      '</tr>';

    tbody.innerHTML = rows;

    // Totals
    if (totalsBox) {
      totalsBox.style.display = "";
      var sub = document.getElementById("cart-subtotal");
      var ship= document.getElementById("cart-shipping");
      var vat = document.getElementById("cart-vat");
      var tot = document.getElementById("cart-total");
      if (sub) sub.textContent = peso(cart.subtotal);
      // shipping/vat preserved from server if you want them dynamic via server rules
      // If you prefer client-side only, set to 0 and total = subtotal.
      if (tot) {
        var shipping = parseFloat((ship && ship.textContent.replace(/[^\d.]/g,'')) || 0) || 0;
        var vatVal   = parseFloat((vat  && vat.textContent.replace(/[^\d.]/g,'')) || 0) || 0;
        tot.textContent = peso((cart.subtotal||0)+shipping+vatVal);
      }
    }

    // refresh header mini cart numbers too
    try { if (window.refreshMiniCart) window.refreshMiniCart(); } catch(e){}
  }

  function refresh(){
    api("get").then(function(res){ if(res && res.ok) render(res.cart); });
  }

  // Remove
  document.addEventListener("click", function(e){
    var rm = e.target.closest(".cart-remove");
    if (!rm) return;
    e.preventDefault();
    var id = rm.getAttribute("data-id");
    if (!id) return;
    api("remove", {id:id}).then(function(res){ if(res && res.ok) render(res.cart); });
  });

  // Clear all
  document.addEventListener("click", function(e){
    var btn = e.target.closest("#clear-cart");
    if (!btn) return;
    e.preventDefault();
    api("clear").then(function(res){ if(res && res.ok) render(res.cart); });
  });

  // Update all (reads every row input)
  document.addEventListener("click", function(e){
    var btn = e.target.closest("#update-cart");
    if (!btn) return;
    e.preventDefault();

    var inputs = document.querySelectorAll(".cart-qty-input");
    var ops = [];
    inputs.forEach(function(inp){
      var id  = inp.getAttribute("data-id");
      var qty = parseInt(inp.value,10);
      if (!id) return;
      if (isNaN(qty) || qty < 0) qty = 0;
      ops.push(api("update", {id:id, qty:qty}));
    });
    Promise.all(ops).then(function(){ refresh(); });
  });

  // Update a single line when qty field changes
  document.addEventListener("change", function(e){
    var inp = e.target.closest(".cart-qty-input");
    if (!inp) return;
    var id  = inp.getAttribute("data-id");
    var qty = parseInt(inp.value,10);
    if (!id) return;
    if (isNaN(qty) || qty < 0) qty = 0;
    api("update", {id:id, qty:qty}).then(function(res){ if(res && res.ok) render(res.cart); });
  });

  // Initial sync (in case cart changed on another page)
  document.addEventListener("DOMContentLoaded", refresh);
})();
</script>

</body>
</html>
