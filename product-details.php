<?php
// -----------------------------
// Data bootstrap (same pattern you use on shop.php)
// -----------------------------
function fetch_products($limit = 12) {
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

    $items = $data['items'] ?? [];
    if ($limit && count($items) > $limit) {
        $items = array_slice($items, 0, $limit);
    }
    return $items;
}

function price_html($p) {
    if ($p === null || $p === '') return '';
    $amt = number_format((float)$p, 2);
    return '₱' . $amt;
}
function stars_html($rating = null) {
    // rating: 0..5 (can be float). Fallback to 5 full stars if null.
    $r = is_numeric($rating) ? max(0, min(5, (float)$rating)) : 5.0;
    $full = (int)floor($r);
    $half = ($r - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;

    $out = '';
    for ($i=0; $i<$full; $i++)  $out .= '<li><a href="#"><i class="icon-star"></i></a></li>';
    if ($half)                  $out .= '<li><a href="#"><i class="icon-star"></i></a></li>'; // template has only one star icon, keep layout
    for ($i=0; $i<$empty; $i++) $out .= '<li><a href="#"><i class="icon-star"></i></a></li>';
    return $out;
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// -----------------------------
// Load current product + collection
// -----------------------------
$idParam = $_GET['id'] ?? '';
$allProducts = fetch_products(0);  // 0 => no slice
$byId = [];
foreach ($allProducts as $it) {
    $key = (string)($it['id'] ?? '');
    if ($key !== '') $byId[$key] = $it;
}
$current = $byId[(string)$idParam] ?? null;

// Graceful 404-ish if not found
if (!$current) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    $productImages = ['img/product/placeholder.png'];
    $name = 'Product not found';
    $price = null; $priceCompare = null;
    $desc = 'Sorry, the product you’re looking for does not exist.';
    $sku = ''; $category = ''; $tags = [];
    $rating = null; $ratingCount = 0;
    $related = array_slice($allProducts, 0, 12);
} else {
    $pageTitle = ($current['name'] ?? 'Product') . ' - The Flower Bucket';

    // images: prefer images[] else image_url
    $imgs = [];
    if (!empty($current['images']) && is_array($current['images'])) {
        foreach ($current['images'] as $u) {
            if ($u) $imgs[] = $u;
        }
    }
    if (!$imgs && !empty($current['image_url'])) $imgs[] = $current['image_url'];
    if (!$imgs) $imgs[] = 'img/product/placeholder.png';
    $productImages = $imgs;

    $name = $current['name'] ?? 'Product';
    $price = $current['price'] ?? null;

    // optional compare/original price keys supported
    $priceCompare = null;
    if (isset($current['compare_at_price'])) $priceCompare = $current['compare_at_price'];
    elseif (isset($current['original_price'])) $priceCompare = $current['original_price'];
    if ($priceCompare !== null && (float)$priceCompare <= (float)$price) $priceCompare = null;

    $desc = $current['description'] ?? '—';
    $sku  = $current['sku'] ?? ($current['id'] ?? '');
    $category = $current['category'] ?? 'Flower';
    $tags = [];
    if (!empty($current['tags'])) {
        $tags = is_array($current['tags']) ? $current['tags'] : array_map('trim', explode(',', (string)$current['tags']));
    }

    $rating = $current['rating'] ?? null;            // 0..5
    $ratingCount = $current['rating_count'] ?? null; // int

    // related: others excluding current
    $related = [];
    foreach ($allProducts as $it) {
        if ((string)($it['id'] ?? '') === (string)$current['id']) continue;
        $related[] = $it;
    }
    // take first 12
    if (count($related) > 12) $related = array_slice($related, 0, 12);
}
?>
<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= h($pageTitle) ?></title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="<?= h(strip_tags((string)$desc)) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/font-icons.css">
    <link rel="stylesheet" href="css/plugins.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>

<body>
<!--[if lte IE 9]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
<![endif]-->

<!-- Body main wrapper start -->
<div class="body-wrapper">

<?php include 'partials/nav.php';?>

    <!-- SHOP DETAILS AREA START -->
    <div class="ltn__shop-details-area pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="ltn__shop-details-inner">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="ltn__shop-details-img-gallery ltn__shop-details-img-gallery-2">
                                    <div class="ltn__shop-details-small-img slick-arrow-2">
                                        <?php foreach ($productImages as $u): ?>
                                            <div class="single-small-img">
                                                <img src="<?= h($u) ?>" alt="<?= h($name) ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="ltn__shop-details-large-img">
                                        <?php foreach ($productImages as $u): ?>
                                            <div class="single-large-img">
                                                <a href="<?= h($u) ?>" data-rel="lightcase:myCollection">
                                                    <img src="<?= h($u) ?>" alt="<?= h($name) ?>">
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modal-product-info shop-details-info pl-0">
                                    <h3><?= h($name) ?></h3>
                                    <div class="product-price-ratting mb-20">
                                        <ul>
                                            <li>
                                                <div class="product-price">
                                                    <?php if ($price !== null): ?>
                                                        <span><?= h(price_html($price)) ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($priceCompare !== null): ?>
                                                        <del><?= h(price_html($priceCompare)) ?></del>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="product-ratting">
                                                    <ul>
                                                        <?= stars_html($rating) ?>
                                                        <?php if ($ratingCount): ?>
                                                            <li class="review-total"><a href="#"> ( <?= (int)$ratingCount ?> Reviews )</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="modal-product-brief">
                                        <p><?= nl2br(h($desc)) ?></p>
                                    </div>

                                    <!-- Keep layout; options remain as-is (no product options feed provided) -->
                                    <div class="modal-product-meta ltn__product-details-menu-1 mb-20 d-none">
                                        <ul>
                                            <li>
                                                <div class="ltn__color-widget clearfix">
                                                    <strong class="d-meta-title">Color</strong>
                                                    <ul>
                                                        <li class="theme"><a href="#"></a></li>
                                                        <li class="green-2"><a href="#"></a></li>
                                                        <li class="blue-2"><a href="#"></a></li>
                                                        <li class="white"><a href="#"></a></li>
                                                        <li class="red"><a href="#"></a></li>
                                                        <li class="yellow"><a href="#"></a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="ltn__size-widget clearfix mt-25 d-none">
                                                    <strong class="d-meta-title">Size</strong>
                                                    <ul>
                                                        <li><a href="#">S</a></li>
                                                        <li><a href="#">M</a></li>
                                                        <li><a href="#">L</a></li>
                                                        <li><a href="#">XL</a></li>
                                                        <li><a href="#">XXL</a></li>
                                                    </ul>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="ltn__product-details-menu-2 product-cart-wishlist-btn mb-30">
                                        <ul>
                                            <li>
                                                <div class="cart-plus-minus">
                                                    <input type="text" value="1" name="qtybutton" class="cart-plus-minus-box">
                                                </div>
                                            </li>
                                            <li>
                                                <a href="#" class="theme-btn-1 btn btn-effect-1 d-add-to-cart" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal">
                                                    <span>ADD TO CART</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" class="btn btn-effect-1 d-add-to-wishlist" title="Add to Wishlist" data-bs-toggle="modal" data-bs-target="#liton_wishlist_modal">
                                                    <i class="icon-heart"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="ltn__social-media mb-30">
                                        <ul>
                                            <li class="d-meta-title">Share:</li>
                                            <li><a href="#" title="Facebook"><i class="icon-social-facebook"></i></a></li>
                                            <li><a href="#" title="Twitter"><i class="icon-social-twitter"></i></a></li>
                                            <li><a href="#" title="Pinterest"><i class="icon-social-pinterest"></i></a></li>
                                            <li><a href="#" title="Instagram"><i class="icon-social-instagram"></i></a></li>
                                        </ul>
                                    </div>

                                    <div class="modal-product-meta ltn__product-details-menu-1 mb-30">
                                        <ul>
                                            <li><strong>SKU:</strong> <span><?= h($sku) ?></span></li>
                                            <li>
                                                <strong>Categories:</strong>
                                                <span>
                                                    <a href="#"><?= h($category ?: 'Flower') ?></a>
                                                </span>
                                            </li>
                                            <li>
                                                <strong>Tags:</strong>
                                                <span>
                                                    <?php if ($tags): ?>
                                                        <?php foreach ($tags as $t): ?>
                                                            <a href="#"><?= h($t) ?></a>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <a href="#">Flower</a>
                                                    <?php endif; ?>
                                                </span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="ltn__safe-checkout d-none">
                                        <h5>Guaranteed Safe Checkout</h5>
                                        <img src="img/icons/payment-2.png" alt="Payment Image">
                                    </div>
                                </div>
                            </div>
                        </div><!-- row -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SHOP DETAILS AREA END -->

    <!-- SHOP DETAILS TAB AREA START -->
    <div class="ltn__shop-details-tab-area pb-60">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__shop-details-tab-inner">
                        <div class="ltn__shop-details-tab-menu">
                            <div class="nav">
                                <a class="active show" data-bs-toggle="tab" href="#liton_tab_details_1_1">Description</a>
                                <a data-bs-toggle="tab" href="#liton_tab_details_1_2" class="">Reviews</a>
                                <a data-bs-toggle="tab" href="#liton_tab_details_1_4" class="">Shipping</a>
                            </div>
                        </div>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="liton_tab_details_1_1">
                                <div class="ltn__shop-details-tab-content-inner text-center">
                                    <p><?= nl2br(h($desc)) ?></p>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="liton_tab_details_1_2">
                                <div class="ltn__shop-details-tab-content-inner">
                                    <div class="customer-reviews-head text-center">
                                        <h4 class="title-2">Customer Reviews</h4>
                                        <div class="product-ratting">
                                            <ul>
                                                <?= stars_html($rating) ?>
                                                <?php if ($ratingCount): ?>
                                                    <li class="review-total"><a href="#"> ( <?= (int)$ratingCount ?> Reviews )</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <hr>
                                    <!-- Keep sample review blocks to preserve layout -->
                                    <div class="row">
                                        <div class="col-lg-7">
                                            <div class="ltn__comment-area mb-30">
                                                <div class="ltn__comment-inner">
                                                    <ul>
                                                        <li>
                                                            <div class="ltn__comment-item clearfix">
                                                                <div class="ltn__commenter-img">
                                                                    <img src="img/testimonial/1.jpg" alt="Image">
                                                                </div>
                                                                <div class="ltn__commenter-comment">
                                                                    <h6><a href="#">Customer</a></h6>
                                                                    <div class="product-ratting"><ul><?= stars_html($rating) ?></ul></div>
                                                                    <p>Beautiful arrangement and fresh flowers!</p>
                                                                    <span class="ltn__comment-reply-btn">Recently</span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="ltn__comment-reply-area ltn__form-box mb-60">
                                                <form action="#">
                                                    <h4 class="title-2">Add a Review</h4>
                                                    <div class="mb-30">
                                                        <div class="add-a-review">
                                                            <h6>Your Ratings:</h6>
                                                            <div class="product-ratting">
                                                                <ul>
                                                                    <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                    <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                    <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                    <li><a href="#"><i class="fas fa-star-half-alt"></i></a></li>
                                                                    <li><a href="#"><i class="far fa-star"></i></a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="input-item input-item-textarea ltn__custom-icon">
                                                        <textarea placeholder="Type your comments...."></textarea>
                                                    </div>
                                                    <div class="input-item input-item-name ltn__custom-icon">
                                                        <input type="text" placeholder="Type your name....">
                                                    </div>
                                                    <div class="input-item input-item-email ltn__custom-icon">
                                                        <input type="email" placeholder="Type your email....">
                                                    </div>
                                                    <div class="input-item input-item-website ltn__custom-icon">
                                                        <input type="text" name="website" placeholder="Type your website....">
                                                    </div>
                                                    <label class="mb-0"><input type="checkbox" name="agree"> Save my info for next time.</label>
                                                    <div class="btn-wrapper">
                                                        <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div><!-- row -->
                                </div>
                            </div>
                            <div class="tab-pane fade" id="liton_tab_details_1_4">
                                <div class="ltn__shop-details-tab-content-inner">
                                    <h4 class="title-2">Shipping policy for our store</h4>
                                    <p>Orders placed before 3PM are prepared the same day. Metro Manila deliveries typically arrive within 1–2 business days.</p>
                                    <ul>
                                        <li>1–2 business days (Typically by end of day)</li>
                                        <li><a href="#">30 days money back guaranty</a></li>
                                        <li>24/7 live support</li>
                                    </ul>
                                    <p>For provincial shipping, lead time may vary based on courier coverage.</p>
                                </div>
                            </div>
                        </div><!-- tab-content -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SHOP DETAILS TAB AREA END -->

    <!-- PRODUCT SLIDER AREA START -->
    <div class="ltn__product-slider-area pb-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area text-center">
                        <h1 class="section-title section-title-border">related products</h1>
                    </div>
                </div>
            </div>
            <div class="row ltn__related-product-slider-one-active slick-arrow-1">
                <?php if (!empty($related)): ?>
                    <?php foreach ($related as $rp):
                        $rid   = h($rp['id'] ?? '');
                        $rname = h($rp['name'] ?? 'Product');
                        $rimg  = h(($rp['image_url'] ?? '') ?: 'img/product/placeholder.png');
                        $rprice = isset($rp['price']) ? price_html($rp['price']) : '';
                        $rpriceCompare = null;
                        if (isset($rp['compare_at_price'])) $rpriceCompare = $rp['compare_at_price'];
                        elseif (isset($rp['original_price'])) $rpriceCompare = $rp['original_price'];
                        $detailsHref = 'product-details.php?id=' . urlencode($rid);
                    ?>
                    <div class="col-12">
                        <div class="ltn__product-item ltn__product-item-4">
                            <div class="product-img">
                                <a href="<?= $detailsHref ?>"><img src="<?= $rimg ?>" alt="<?= $rname ?>"></a>
                                <div class="product-badge">
                                    <ul><!-- badges preserved --></ul>
                                </div>
                                <div class="product-hover-action product-hover-action-3">
                                    <ul>
                                        <li>
                                            <a href="#" title="Quick View" data-bs-toggle="modal" data-bs-target="#quick_view_modal">
                                                <i class="icon-magnifier"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal">
                                                <i class="icon-handbag"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" title="Quick View" data-bs-toggle="modal" data-bs-target="#quick_view_modal">
                                                <i class="icon-shuffle"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-ratting">
                                    <ul>
                                        <?= stars_html($rp['rating'] ?? null) ?>
                                    </ul>
                                </div>
                                <h2 class="product-title"><a href="<?= $detailsHref ?>"><?= $rname ?></a></h2>
                                <div class="product-price">
                                    <?php if ($rprice): ?><span><?= h($rprice) ?></span><?php endif; ?>
                                    <?php if ($rpriceCompare !== null && (float)$rpriceCompare > (float)($rp['price'] ?? 0)): ?>
                                        <del><?= h(price_html($rpriceCompare)) ?></del>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p class="text-center">No related products available.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- PRODUCT SLIDER AREA END -->

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

    <!-- MODALS (kept as-is to preserve layout) -->
    <div class="ltn__modal-area ltn__quick-view-modal-area">
        <div class="modal fade" id="quick_view_modal" tabindex="-1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                         <div class="ltn__quick-view-modal-inner">
                             <div class="modal-product-item">
                                <div class="row">
                                    <div class="col-lg-6 col-12">
                                        <div class="modal-product-img">
                                            <img src="img/product/4.png" alt="#">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="modal-product-info shop-details-info pl-0">
                                            <h3>Pink Flower Tree Red</h3>
                                            <div class="product-price-ratting mb-20">
                                                <ul>
                                                    <li><div class="product-price"><span>₱49.00</span><del>₱65.00</del></div></li>
                                                    <li><div class="product-ratting"><ul><li><a href="#"><i class="icon-star"></i></a></li><li><a href="#"><i class="icon-star"></i></a></li><li><a href="#"><i class="icon-star"></i></a></li><li><a href="#"><i class="icon-star"></i></a></li><li><a href="#"><i class="icon-star"></i></a></li><li class="review-total"><a href="#"> ( 95 Reviews )</a></li></ul></div></li>
                                                </ul>
                                            </div>
                                            <div class="modal-product-brief"><p>Quick view sample content.</p></div>
                                            <div class="ltn__product-details-menu-2 product-cart-wishlist-btn mb-30">
                                                <ul>
                                                    <li><div class="cart-plus-minus"><input type="text" value="1" name="qtybutton" class="cart-plus-minus-box"></div></li>
                                                    <li><a href="#" class="theme-btn-1 btn btn-effect-1 d-add-to-cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal"><span>ADD TO CART</span></a></li>
                                                    <li><a href="#" class="btn btn-effect-1 d-add-to-wishlist" data-bs-toggle="modal" data-bs-target="#liton_wishlist_modal"><i class="icon-heart"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ltn__modal-area ltn__add-to-cart-modal-area">
        <div class="modal fade" id="add_to_cart_modal" tabindex="-1">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                         <div class="ltn__quick-view-modal-inner">
                             <div class="modal-product-item">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="modal-add-to-cart-content clearfix">
                                            <div class="modal-product-img"><img src="img/product/1.png" alt="#"></div>
                                            <div class="modal-product-info">
                                                <h5><a href="product-details.php">Heart's Desire</a></h5>
                                                <p class="added-cart"><i class="fa fa-check-circle"></i> Successfully added to your Cart</p>
                                                <div class="btn-wrapper">
                                                    <a href="cart.html" class="theme-btn-1 btn btn-effect-1">View Cart</a>
                                                    <a href="checkout.html" class="theme-btn-2 btn btn-effect-2">Checkout</a>
                                                </div>
                                             </div>
                                        </div>
                                        <div class="additional-info d-none--">
                                            <p>Use code <b>fiama10</b> for 10% off your first order.</p>
                                            <div class="payment-method"><img src="img/icons/payment.png" alt="#"></div>
                                         </div>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ltn__modal-area ltn__add-to-cart-modal-area">
        <div class="modal fade" id="liton_wishlist_modal" tabindex="-1">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                         <div class="ltn__quick-view-modal-inner">
                             <div class="modal-product-item">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="modal-product-img"><img src="img/product/7.png" alt="#"></div>
                                        <div class="modal-product-info">
                                            <h5><a href="product-details.php">Brake Conversion Kit</a></h5>
                                            <p class="added-cart"><i class="fa fa-check-circle"></i> Successfully added to your Wishlist</p>
                                            <div class="btn-wrapper"><a href="wishlist.html" class="theme-btn-1 btn btn-effect-1">View Wishlist</a></div>
                                        </div>
                                        <div class="additional-info d-none">
                                            <p>Use your welcome code at checkout.</p>
                                            <div class="payment-method"><img src="img/icons/payment.png" alt="#"></div>
                                         </div>
                                    </div>
                                </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODALS END -->

</div>
<!-- Body main wrapper end -->

<!-- preloader area start -->
<div class="preloader d-none" id="preloader">
    <div class="preloader-inner">
        <div class="spinner">
            <div class="dot1"></div>
            <div class="dot2"></div>
        </div>
    </div>
</div>
<!-- preloader area end -->

<!-- All JS Plugins -->
<script src="js/plugins.js"></script>
<!-- Main JS -->
<script src="js/main.js"></script>

</body>
</html>
