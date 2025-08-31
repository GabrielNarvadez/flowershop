<?php
// partials/nav.php
declare(strict_types=1);

// Safe session start (won't double-start if already active)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Read cart from session and compute qty + subtotal
$__cart = $_SESSION['cart'] ?? [];
if (!is_array($__cart)) $__cart = [];

$__cartQty = 0;
$__cartSubtotal = 0.0;
foreach ($__cart as $__row) {
    $q = max(0, (int)($__row['qty'] ?? 0));
    $p = (float)($__row['price'] ?? 0);
    $__cartQty     += $q;
    $__cartSubtotal += $q * $p;
}
function __peso($n): string { return '₱' . number_format((float)$n, 2); }
?>
<!-- HEADER AREA START (header-3) -->
<header class="ltn__header-area ltn__header-3 section-bg-6">        
    <!-- ltn__header-middle-area start -->
    <div class="ltn__header-middle-area">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="site-logo">
                        <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
                    </div>
                </div>
                <div class="col header-contact-serarch-column d-none d-xl-block">
                    <div class="header-contact-search">
                        <!-- header-feature-item -->
                        <div class="header-feature-item">
                            <div class="header-feature-icon">
                                <i class="icon-phone"></i>
                            </div>
                            <div class="header-feature-info">
                                <h6>Phone</h6>
                                <p><a href="tel:0123456789">+0123-456-789</a></p>
                            </div>
                        </div>
                        <!-- header-search-2 -->
                        <div class="header-search-2">
                            <form id="#123" method="get"  action="#">
                                <input type="text" name="search" value="" placeholder="Search here..."/>
                                <button type="submit">
                                    <span><i class="icon-magnifier"></i></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <!-- header-options -->
                    <div class="ltn__header-options">
                        <ul>
                            <li class="d-none">
                                <!-- ltn__currency-menu -->
                                <div class="ltn__drop-menu ltn__currency-menu">
                                    <ul>
                                        <li><a href="#" class="dropdown-toggle"><span class="active-currency">USD</span></a>
                                            <ul>
                                                <li><a href="login.html">USD - US Dollar</a></li>
                                                <li><a href="wishlist.html">CAD - Canada Dollar</a></li>
                                                <li><a href="register.html">EUR - Euro</a></li>
                                                <li><a href="account.html">GBP - British Pound</a></li>
                                                <li><a href="wishlist.html">INR - Indian Rupee</a></li>
                                                <li><a href="wishlist.html">BDT - Bangladesh Taka</a></li>
                                                <li><a href="wishlist.html">JPY - Japan Yen</a></li>
                                                <li><a href="wishlist.html">AUD - Australian Dollar</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="d-none">
                                <!-- header-search-1 -->
                                <div class="header-search-wrap">
                                    <div class="header-search-1">
                                        <div class="search-icon">
                                            <i class="icon-magnifier  for-search-show"></i>
                                            <i class="icon-magnifier-remove  for-search-close"></i>
                                        </div>
                                    </div>
                                    <div class="header-search-1-form">
                                        <form id="#" method="get"  action="#">
                                            <input type="text" name="search" value="" placeholder="Search here..."/>
                                            <button type="submit">
                                                <span><i class="icon-magnifier"></i></span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                            <li class="d-none"> 
                                <!-- user-menu -->
                                <div class="ltn__drop-menu user-menu">
                                    <ul>
                                        <li>
                                            <a href="#"><i class="icon-user"></i></a>
                                            <ul>
                                                <li><a href="login.html">Sign in</a></li>
                                                <li><a href="register.html">Register</a></li>
                                                <li><a href="account.html">My Account</a></li>
                                                <li><a href="wishlist.html">Wishlist</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <!-- mini-cart 2 -->
                                <div class="mini-cart-icon mini-cart-icon-2">
                                    <a href="#ltn__utilize-cart-menu" class="ltn__utilize-toggle">
                                        <span class="mini-cart-icon">
                                            <i class="icon-handbag"></i>
                                            <!-- Dynamic count -->
                                            <sup id="nav-cart-count"><?= (int)$__cartQty ?></sup>
                                        </span>
                                        <h6>
                                            <span>Your Cart</span>
                                            <!-- Dynamic subtotal -->
                                            <span class="ltn__secondary-color" id="nav-cart-subtotal"><?= __peso($__cartSubtotal) ?></span>
                                        </h6>
                                    </a>
                                </div>
                            </li>
                            <li>      
                                <!-- Mobile Menu Button -->
                                <div class="mobile-menu-toggle d-lg-none">
                                    <a href="#ltn__utilize-mobile-menu" class="ltn__utilize-toggle">
                                        <svg viewBox="0 0 800 600">
                                            <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" id="top"></path>
                                            <path d="M300,320 L540,320" id="middle"></path>
                                            <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" id="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                                        </svg>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ltn__header-middle-area end -->
    
    <!-- header-bottom-area start -->
    <div class="header-bottom-area ltn__border-top ltn__header-sticky  ltn__sticky-bg-white ltn__primary-bg---- menu-color-white---- d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col header-menu-column justify-content-center">
                    <div class="sticky-logo">
                        <div class="site-logo">
                            <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
                        </div>
                    </div>
                    <div class="header-menu header-menu-2">
                        <nav>
                            <div class="ltn__main-menu">
                                <ul>
                                    <li class="menu-icon"><a href="index.php">Home</a></li>
                                    <li class="menu-icon"><a href="shop.php">Products</a>
                                        <ul class="mega-menu">
                                            <li><a href="#">By Flower</a>
                                                <ul>
                                                    <li><a href="shop.php?cat=roses">Roses</a></li>
                                                    <li><a href="shop.php?cat=tulips">Tulips</a></li>
                                                    <li><a href="shop.php?cat=lilies">Lilies</a></li>
                                                    <li><a href="shop.php?cat=sunflowers">Sunflowers</a></li>
                                                    <li><a href="shop.php?cat=orchids">Orchids</a></li>
                                                    <li><a href="shop.php?cat=mixed-bouquets">Mixed Bouquets</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="#">By Occasion</a>
                                                <ul>
                                                    <li><a href="shop.php?cat=birthday">Birthday</a></li>
                                                    <li><a href="shop.php?cat=anniversary">Anniversary</a></li>
                                                    <li><a href="shop.php?cat=sympathy">Sympathy</a></li>
                                                    <li><a href="shop.php?cat=congratulations">Congratulations</a></li>
                                                    <li><a href="shop.php?cat=get-well">Get Well</a></li>
                                                    <li><a href="shop.php?cat=thank-you">Thank You</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="#">Collections & Gifts</a>
                                                <ul>
                                                    <li><a href="shop.php?cat=new-arrivals">New Arrivals</a></li>
                                                    <li><a href="shop.php?cat=best-sellers">Best Sellers</a></li>
                                                    <li><a href="shop.php?cat=seasonal">Seasonal Picks</a></li>
                                                    <li><a href="shop.php?cat=plants-succulents">Plants &amp; Succulents</a></li>
                                                    <li><a href="shop.php?cat=gift-sets">Gift Sets</a></li>
                                                    <li><a href="shop.php?cat=add-ons">Add-ons (Chocolates, Balloons, Cards)</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-icon"><a href="about.php">About</a></li>
                                    <li><a href="contact.php">Contact</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header-bottom-area end -->
</header>
<!-- HEADER AREA END -->

<!-- Utilize Cart Menu Start -->
<div id="ltn__utilize-cart-menu" class="ltn__utilize ltn__utilize-cart-menu">
    <div class="ltn__utilize-menu-inner ltn__scrollbar">
        <div class="ltn__utilize-menu-head">
            <span class="ltn__utilize-menu-title">Cart</span>
            <button class="ltn__utilize-close">×</button>
        </div>
        <!-- Keep your static mini-list here or wire it later -->
        <div class="mini-cart-product-area ltn__scrollbar">
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="#"><img src="img/product/1.png" alt="Image"></a>
                    <span class="mini-cart-item-delete"><i class="icon-trash"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">Premium Joyful</a></h6>
                    <span class="mini-cart-quantity">1 x $65.00</span>
                </div>
            </div>
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="#"><img src="img/product/2.png" alt="Image"></a>
                    <span class="mini-cart-item-delete"><i class="icon-trash"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">The White Rose</a></h6>
                    <span class="mini-cart-quantity">1 x $85.00</span>
                </div>
            </div>
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="#"><img src="img/product/3.png" alt="Image"></a>
                    <span class="mini-cart-item-delete"><i class="icon-trash"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">Red Rose Bouquet</a></h6>
                    <span class="mini-cart-quantity">1 x $92.00</span>
                </div>
            </div>
            <div class="mini-cart-item clearfix">
                <div class="mini-cart-img">
                    <a href="#"><img src="img/product/4.png" alt="Image"></a>
                    <span class="mini-cart-item-delete"><i class="icon-trash"></i></span>
                </div>
                <div class="mini-cart-info">
                    <h6><a href="#">Pink Flower Tree</a></h6>
                    <span class="mini-cart-quantity">1 x $68.00</span>
                </div>
            </div>
        </div>
        <div class="mini-cart-footer">
            <div class="mini-cart-sub-total">
                <h5>Subtotal: <span id="nav-cart-subtotal-dup"><?= __peso($__cartSubtotal) ?></span></h5>
            </div>
            <div class="btn-wrapper">
                <a href="cart-out.php" class="theme-btn-1 btn btn-effect-1">View Cart</a>
                <a href="checkout.html" class="theme-btn-2 btn btn-effect-2">Checkout</a>
            </div>
            <p>Free Shipping on All Orders Over $100!</p>
        </div>

    </div>
</div>
<!-- Utilize Cart Menu End -->

<!-- Utilize Mobile Menu Start -->
<div id="ltn__utilize-mobile-menu" class="ltn__utilize ltn__utilize-mobile-menu">
    <div class="ltn__utilize-menu-inner ltn__scrollbar">
        <div class="ltn__utilize-menu-head">
            <div class="site-logo">
                <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
            </div>
            <button class="ltn__utilize-close">×</button>
        </div>
        <div class="ltn__utilize-menu-search-form">
            <form action="#">
                <input type="text" placeholder="Search...">
                <button><i class="icon-magnifier"></i></button>
            </form>
        </div>
        <div class="ltn__utilize-menu">
            <ul>
                <li><a href="index.php">Home</a>
                    <ul class="sub-menu">
                        <li><a href="index.php">Home Style - 01</a></li>
                        <li><a href="index-2.html">Home Style - 02</a></li>
                        <li><a href="index-3.html">Home Style - 03</a></li>
                        <li><a href="index-4.html">Home Style - 04</a></li>
                    </ul>
                </li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="#">Shop</a>
                    <ul class="sub-menu">
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="shop-grid.html">Shop Grid</a></li>
                        <li><a href="shop-left-sidebar.html">Shop Left sidebar</a></li>
                        <li><a href="shop-right-sidebar.html">Shop right sidebar</a></li>
                        <li><a href="product-details.html">Shop details </a></li>
                        <li><a href="cart-out.php">Cart</a></li>
                        <li><a href="wishlist.html">Wishlist</a></li>
                        <li><a href="checkout.html">Checkout</a></li>
                        <li><a href="order-tracking.html">Order Tracking</a></li>
                        <li><a href="account.html">My Account</a></li>
                        <li><a href="login.html">Sign in</a></li>
                        <li><a href="register.html">Register</a></li>
                    </ul>
                </li>
                <li><a href="#">Pages</a>
                    <ul class="sub-menu">
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="portfolio.html">Portfolio</a></li>
                        <li><a href="portfolio-2.html">Portfolio - 02</a></li>
                        <li><a href="portfolio-details.html">Portfolio Details</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="locations.html">Google Map Locations</a></li>
                        <li><a href="404.html">404</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="coming-soon.html">Coming Soon</a></li>
                    </ul>
                </li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="ltn__utilize-buttons ltn__utilize-buttons-2">
            <ul>
                <li>
                    <a href="account.html" title="My Account">
                        <span class="utilize-btn-icon">
                            <i class="icon-user"></i>
                        </span>
                        My Account
                    </a>
                </li>
                <li>
                    <a href="wishlist.html" title="Wishlist">
                        <span class="utilize-btn-icon">
                            <i class="icon-heart"></i>
                            <sup>3</sup>
                        </span>
                        Wishlist
                    </a>
                </li>
                <li>
                    <a href="cart-out.php" title="Shoping Cart">
                        <span class="utilize-btn-icon">
                            <i class="icon-handbag"></i>
                            <!-- Dynamic count (mobile) -->
                            <sup id="nav-cart-count-mobile"><?= (int)$__cartQty ?></sup>
                        </span>
                        Shoping Cart
                    </a>
                </li>
            </ul>
        </div>
        <div class="ltn__social-media-2">
            <ul>
                <li><a href="#" title="Facebook"><i class="icon-social-facebook"></i></a></li>
                <li><a href="#" title="Twitter"><i class="icon-social-twitter"></i></a></li>
                <li><a href="#" title="Pinterest"><i class="icon-social-pinterest"></i></a></li>
                <li><a href="#" title="Instagram"><i class="icon-social-instagram"></i></a></li>
            </ul>
        </div>
    </div>
</div>
<!-- Utilize Mobile Menu End -->

<div class="ltn__utilize-overlay"></div>

<!-- Lightweight auto-refresh for counts/subtotal (works with cart.php) -->
<script>
(function(){
  function peso(n){ var x = parseFloat(n||0); if (isNaN(x)) x = 0; return "₱" + x.toFixed(2); }
  function applyCounts(items){
    var count = 0, sub = 0;
    (items||[]).forEach(function(it){
      var q = parseInt(it.qty||0,10) || 0;
      var p = parseFloat(it.price||0) || 0;
      count += q; sub += q*p;
    });
    var c1 = document.getElementById('nav-cart-count');
    var c2 = document.getElementById('nav-cart-count-mobile');
    var s1 = document.getElementById('nav-cart-subtotal');
    var s2 = document.getElementById('nav-cart-subtotal-dup'); // in offcanvas
    if (c1) c1.textContent = count;
    if (c2) c2.textContent = count;
    if (s1) s1.textContent = peso(sub);
    if (s2) s2.textContent = peso(sub);
  }

  // Define refreshMiniCart only if not defined yet (cart.js might define it too)
  if (typeof window.refreshMiniCart !== 'function') {
    window.refreshMiniCart = function(){
      fetch('cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: 'action=get',
        credentials: 'same-origin'
      }).then(function(r){ return r.json(); })
        .then(function(res){ if(res && res.ok && res.cart) applyCounts(res.cart.items||[]); })
        .catch(function(){ /* ignore */ });
    };
  }

  // Initial sync on load
  document.addEventListener('DOMContentLoaded', function(){ try { window.refreshMiniCart(); } catch(e){} });
})();
</script>
