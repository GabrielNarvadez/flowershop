<?php
// Dynamic products bootstrap
function fetch_products($limit = 12) {
    // Build absolute URL to products.php so PHP executes it (vs reading raw source)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $src    = $scheme . '://' . $host . ($base ? $base : '') . '/products.php?nocache=' . time();

    $json = false;

    // Prefer file_get_contents if allow_url_fopen is enabled
    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $json = @file_get_contents($src, false, $ctx);
    }

    // Fallback to cURL
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
    return '<span>₱' . $amt . '</span>';
}

$dynamicProducts = fetch_products(12); // adjust if your grid shows more
?>

<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>The Flower Bucket</title>
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
    <!--[if lte IE 9]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</a></p>
    <![endif]-->

<!-- Body main wrapper start -->
<div class="body-wrapper">

<?php include 'partials/nav.php';?>


    <!-- SLIDER AREA START (slider-6) -->
    <div class="ltn__slider-area ltn__slider-3 ltn__slider-6 section-bg-1">
        <div class="ltn__slide-one-active slick-slide-arrow-1 slick-slide-dots-1 arrow-white---">
            <!-- ltn__slide-item  -->
            <div class="ltn__slide-item ltn__slide-item-8 text-color-white---- bg-image bg-overlay-theme-black-80---" data-bs-bg="img/slider/1.jpg">
                <div class="ltn__slide-item-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12 align-self-center">
                                <div class="slide-item-info">
                                    <div class="slide-item-info-inner ltn__slide-animation">
                                        <div class="slide-item-info">
                                            <div class="slide-item-info-inner ltn__slide-animation">
                                                <h1 class="slide-title animated ">The Flower Bucket</h1>
                                                <h6 class="slide-sub-title ltn__body-color slide-title-line animated">Natural & Beautiful Flower</h6>
                                                <div class="slide-brief animated">
                                                    <p>Whether you’re surprising a loved one, celebrating a milestone, or brightening up your home, we handpick only the freshest flowers to create arrangements that speak from the heart.</p>
                                                </div>
                                                <div class="btn-wrapper animated">
                                                    <a href="shop.php" class="theme-btn-1 btn btn-round">Shop Now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="slide-item-img">
                                    <img src="img/slider/41-1.png" alt="#">
                                    <span class="call-to-circle-1"></span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ltn__slide-item  -->
            <div class="ltn__slide-item ltn__slide-item-8 text-color-white---- bg-image bg-overlay-theme-black-80---" data-bs-bg="img/slider/3.jpg">
                <div class="ltn__slide-item-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12 align-self-center">
                                <div class="slide-item-info">
                                    <div class="slide-item-info-inner ltn__slide-animation">
                                        <div class="slide-item-info">
                                            <div class="slide-item-info-inner ltn__slide-animation">
                                                <h1 class="slide-title animated ">The Flower Bucket</h1>
                                                <h6 class="slide-sub-title ltn__body-color slide-title-line animated">Natural & Beautiful Flower</h6>
                                                <div class="slide-brief animated">
                                                    <p>At The Flower Bucket, we believe flowers are more than just gifts, they are messages of love, joy, comfort, and celebration.</p>
                                                </div>
                                                <div class="btn-wrapper animated">
                                                    <a href="service.html" class="theme-btn-1 btn btn-round">Shop Now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="slide-item-img">
                                    <img src="img/slider/41-1.png" alt="#">
                                    <span class="call-to-circle-1"></span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  -->
        </div>
    </div>
    <!-- SLIDER AREA END -->

    <!-- FEATURE AREA START ( Feature - 3) -->
    <div class="ltn__feature-area mt-100 mt--65">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__feature-item-box-wrap ltn__feature-item-box-wrap-2 ltn__border section-bg-6 position-relative">
                        <div class="ltn__feature-item ltn__feature-item-8">
                            <div class="ltn__feature-icon">
                                <img src="img/icons/svg/8-trolley.svg" alt="#">
                            </div>
                            <div class="ltn__feature-info">
                                <h4>Free shipping</h4>
                                <p>On all orders over $49.00</p>
                            </div>
                        </div>
                        <div class="ltn__feature-item ltn__feature-item-8">
                            <div class="ltn__feature-icon">
                                <img src="img/icons/svg/9-money.svg" alt="#">
                            </div>
                            <div class="ltn__feature-info">
                                <h4>15 days returns</h4>
                                <p>Moneyback guarantee</p>
                            </div>
                        </div>
                        <div class="ltn__feature-item ltn__feature-item-8">
                            <div class="ltn__feature-icon">
                                <img src="img/icons/svg/10-credit-card.svg" alt="#">
                            </div>
                            <div class="ltn__feature-info">
                                <h4>Secure checkout</h4>
                                <p>Protected by Paypal</p>
                            </div>
                        </div>
                        <div class="ltn__feature-item ltn__feature-item-8">
                            <div class="ltn__feature-icon">
                                <img src="img/icons/svg/11-gift-card.svg" alt="#">
                            </div>
                            <div class="ltn__feature-info">
                                <h4>Offer & gift here</h4>
                                <p>On all orders over</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BANNER AREA START -->
    <div class="ltn__banner-area  mt-80">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/1.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/2.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/3.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BANNER AREA END -->

    <!-- PRODUCT AREA START -->
    <div class="ltn__product-area ltn__product-gutter  pt-65 pb-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area text-center">
                        <h1 class="section-title section-title-border">new arrival items</h1>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <?php if (!empty($dynamicProducts)): ?>
                    <?php foreach ($dynamicProducts as $p): 
                        $id   = htmlspecialchars($p['id'] ?? '');
                        $name = htmlspecialchars($p['name'] ?? 'Product');
                        $img  = htmlspecialchars(($p['image_url'] ?? '') ?: 'img/product/placeholder.png');
                        $priceSpan = price_html($p['price'] ?? null);
                        $detailsHref = 'product-details.php?id=' . urlencode($id);
                    ?>
                    <!-- ltn__product-item -->
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                        <div class="ltn__product-item text-center">
                            <div class="product-img">
                                <a href="<?= $detailsHref; ?>"><img src="<?= $img; ?>" alt="<?= $name; ?>"></a>
                                <div class="product-badge">
                                    <ul>
                                        <!-- Keep badges empty for now to preserve layout -->
                                    </ul>
                                </div>
                                <div class="product-hover-action product-hover-action-2">
                                    <ul>
                                        <li>
                                            <a href="#"
                                               class="quick-view-btn"
                                               title="Quick View"
                                               data-bs-toggle="modal"
                                               data-bs-target="#quick_view_modal"
                                               data-id="<?= $id; ?>"
                                               data-name="<?= $name; ?>"
                                               data-price="<?= htmlspecialchars($p['price']); ?>"
                                               data-description="<?= htmlspecialchars($p['description'] ?? ''); ?>"
                                               data-img="<?= $img; ?>">
                                                <i class="icon-magnifier"></i>
                                            </a>
                                        </li>
                                        <li class="add-to-cart">
                                            <a href="#" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal">
                                                <span class="cart-text d-none d-xl-block">Add to Cart</span>
                                                <span class="d-block d-xl-none"><i class="icon-handbag"></i></span>
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
                                <h2 class="product-title"><a href="<?= $detailsHref; ?>"><?= $name; ?></a></h2>
                                <div class="product-price">
                                    <?= $priceSpan; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ltn__product-item -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">No products available right now.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- PRODUCT SLIDER AREA END -->

    <!-- BANNER AREA START -->
    <div class="ltn__banner-area ">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/6.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/7.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BANNER AREA END -->

    <!-- PRODUCT SLIDER AREA START -->
    <div class="ltn__product-slider-area ltn__product-gutter  pt-60 pb-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area text-center">
                        <h1 class="section-title section-title-border">top products</h1>
                    </div>
                </div>
            </div>
            <div class="row ltn__product-slider-item-four-active slick-arrow-1">
                <?php if (!empty($dynamicProducts)): ?>
                    <?php foreach ($dynamicProducts as $p): 
                        $id   = htmlspecialchars($p['id'] ?? '');
                        $name = htmlspecialchars($p['name'] ?? 'Product');
                        $img  = htmlspecialchars(($p['image_url'] ?? '') ?: 'img/product/placeholder.png');
                        $priceSpan = price_html($p['price'] ?? null);
                        $detailsHref = 'product-details.php?id=' . urlencode($id);
                    ?>
                    <!-- ltn__product-item -->
                    <div class="col-12">
                        <div class="ltn__product-item text-center">
                            <div class="product-img">
                                <a href="<?= $detailsHref; ?>"><img src="<?= $img; ?>" alt="<?= $name; ?>"></a>
                                <div class="product-badge">
                                    <ul><!-- keep empty to preserve spacing --></ul>
                                </div>
                                <div class="product-hover-action product-hover-action-2">
                                    <ul>
                                        <li>
                                            <a href="#"
                                               class="quick-view-btn"
                                               title="Quick View"
                                               data-bs-toggle="modal"
                                               data-bs-target="#quick_view_modal"
                                               data-id="<?= $id; ?>"
                                               data-name="<?= $name; ?>"
                                               data-price="<?= htmlspecialchars($p['price']); ?>"
                                               data-description="<?= htmlspecialchars($p['description'] ?? ''); ?>"
                                               data-img="<?= $img; ?>">
                                                <i class="icon-magnifier"></i>
                                            </a>
                                        </li>
                                        <li class="add-to-cart">
                                            <a href="#" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal">
                                                <span class="cart-text d-none d-xl-block">Add to Cart</span>
                                                <span class="d-block d-xl-none"><i class="icon-handbag"></i></span>
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
                                <h2 class="product-title"><a href="<?= $detailsHref; ?>"><?= $name; ?></a></h2>
                                <div class="product-price">
                                    <?= $priceSpan; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ltn__product-item -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-center">No products available right now.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- PRODUCT SLIDER AREA END -->

    <!-- BANNER AREA START -->
    <div class="ltn__banner-area d-none">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="ltn__banner-item">
                        <div class="ltn__banner-img">
                            <a href="shop.php"><img src="img/banner/10.jpg" alt="Banner Image"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BANNER AREA END -->

    <!-- BRAND LOGO AREA START -->
    <div class="ltn__brand-logo-area  ltn__brand-logo-1 section-bg-1 pt-35 pb-35 plr--5">
        <div class="container-fluid">
            <div class="row ltn__brand-logo-active">
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/1.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/2.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/3.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/4.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/5.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/1.png" alt="Brand Logo">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ltn__brand-logo-item">
                        <img src="img/brand-logo/2.png" alt="Brand Logo">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BRAND LOGO AREA END -->

    <!-- FOOTER AREA START -->
    <?php include 'partials/footer.php';?>
    <!-- FOOTER AREA END -->

    <!-- MODAL AREA START (Quick View Modal) -->
    <div class="ltn__modal-area ltn__quick-view-modal-area">
        <div class="modal fade" id="quick_view_modal" tabindex="-1">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            <!-- <i class="fas fa-times"></i> -->
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
                                                    <li>
                                                        <div class="product-price">
                                                            <span>$49.00</span>
                                                            <del>$65.00</del>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="product-ratting">
                                                            <ul>
                                                                <li><a href="#"><i class="icon-star"></i></a></li>
                                                                <li><a href="#"><i class="icon-star"></i></a></li>
                                                                <li><a href="#"><i class="icon-star"></i></a></li>
                                                                <li><a href="#"><i class="icon-star"></i></a></li>
                                                                <li><a href="#"><i class="icon-star"></i></a></li>
                                                                <li class="review-total"> <a href="#"> ( 95 Reviews )</a></li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="modal-product-brief">
                                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos repellendus repudiandae incidunt quidem pariatur expedita, quo quis modi tempore non.</p>
                                            </div>
                                            <div class="modal-product-meta ltn__product-details-menu-1 mb-20">
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
                                                        <div class="ltn__size-widget clearfix mt-25">
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
                                                            <input type="text" value="02" name="qtybutton" class="cart-plus-minus-box">
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="theme-btn-1 btn btn-effect-1 d-add-to-cart" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#add_to_cart_modal">
                                                            <span>ADD TO CART</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#" class="btn btn-effect-1 d-add-to-wishlist" title="Add to Cart" data-bs-toggle="modal" data-bs-target="#liton_wishlist_modal">
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
                                            <div class="modal-product-meta ltn__product-details-menu-1 mb-30 d-none">
                                                <ul>
                                                    <li><strong>SKU:</strong> <span>12345</span></li>
                                                    <li>
                                                        <strong>Categories:</strong> 
                                                        <span>
                                                            <a href="#">Flower</a>
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <strong>Tags:</strong> 
                                                        <span>
                                                            <a href="#">Love</a>
                                                            <a href="#">Flower</a>
                                                            <a href="#">Heart</a>
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
                                </div>
                             </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL AREA END -->

    <!-- MODAL AREA START (Add To Cart Modal) -->
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
                                            <div class="modal-product-img">
                                                <img src="img/product/1.png" alt="#">
                                            </div>
                                             <div class="modal-product-info">
                                                <h5><a href="product-details.html">Heart's Desire</a></h5>
                                                <p class="added-cart"><i class="fa fa-check-circle"></i>  Successfully added to your Cart</p>
                                                <div class="btn-wrapper">
                                                    <a href="cart-out.php" class="theme-btn-1 btn btn-effect-1">View Cart</a>
                                                    <a href="checkout.html" class="theme-btn-2 btn btn-effect-2">Checkout</a>
                                                </div>
                                             </div>
                                        </div>
                                         <!-- additional-info -->
                                         <div class="additional-info d-none--">
                                            <p>We want to give you <b>10% discount</b> for your first order, <br>  Use (fiama10) discount code at checkout</p>
                                            <div class="payment-method">
                                                <img src="img/icons/payment.png" alt="#">
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
    <!-- MODAL AREA END -->

    <!-- MODAL AREA START (Wishlist Modal) -->
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
                                        <div class="modal-product-img">
                                            <img src="img/product/7.png" alt="#">
                                        </div>
                                         <div class="modal-product-info">
                                            <h5><a href="product-details.html">Brake Conversion Kit</a></h5>
                                            <p class="added-cart"><i class="fa fa-check-circle"></i>  Successfully added to your Wishlist</p>
                                            <div class="btn-wrapper">
                                                <a href="wishlist.html" class="theme-btn-1 btn btn-effect-1">View Wishlist</a>
                                            </div>
                                         </div>
                                         <!-- additional-info -->
                                         <div class="additional-info d-none">
                                            <p>We want to give you <b>10% discount</b> for your first order, <br>  Use discount code at checkout</p>
                                            <div class="payment-method">
                                                <img src="img/icons/payment.png" alt="#">
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
    <!-- MODAL AREA END -->

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
    <script>
document.addEventListener("DOMContentLoaded", function () {
  function peso(amount) {
    var n = parseFloat(amount);
    if (isNaN(n)) return "";
    return "₱" + n.toFixed(2);
  }

  document.body.addEventListener("click", function (e) {
    var btn = e.target.closest(".quick-view-btn");
    if (!btn) return;

    // Extract data from the clicked product
    var id    = btn.getAttribute("data-id") || "";
    var img   = btn.getAttribute("data-img") || "img/product/placeholder.png";
    var name  = btn.getAttribute("data-name") || "Product";
    var price = btn.getAttribute("data-price") || "";
    var desc  = btn.getAttribute("data-description") || "No description available.";

    var modal = document.getElementById("quick_view_modal");
    if (!modal) return;

    // Populate modal UI
    var imgEl   = modal.querySelector(".modal-product-img img");
    var nameEl  = modal.querySelector(".modal-product-info h3");
    var priceEl = modal.querySelector(".product-price span");
    var descEl  = modal.querySelector(".modal-product-brief p");

    if (imgEl)   imgEl.src = img;
    if (nameEl)  nameEl.textContent = name;
    if (priceEl) priceEl.textContent = peso(price) || priceEl.textContent;
    if (descEl)  descEl.textContent = desc;

    // 🔑 Stash the ID so the modal's "ADD TO CART" knows what to add
    modal.dataset.productId = id;
    var addBtn = modal.querySelector(".d-add-to-cart");
    if (addBtn) addBtn.setAttribute("data-id", id);
  });
});
</script>

  
  <script src="js/cart.js"></script>

</body>
</html>
