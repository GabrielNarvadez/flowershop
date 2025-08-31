<!doctype html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Fiama - Flower Shop eCommerce HTML Template</title>
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
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->

    <!-- Add your site or application content here -->

<!-- Body main wrapper start -->
<div class="body-wrapper">

    <!-- HEADER AREA START (header-3) -->
    <?php include 'partials/nav.php';?>

    <!-- WISHLIST AREA START -->
    <div class="ltn__checkout-area mb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__checkout-inner">
                        <div class="ltn__checkout-single-content ltn__returning-customer-wrap d-none">
                            <h5>Returning customer? <a class="ltn__secondary-color" href="#ltn__returning-customer-login" data-bs-toggle="collapse">Click here to login</a></h5>
                            <div id="ltn__returning-customer-login" class="collapse ltn__checkout-single-content-info">
                                <div class="ltn_coupon-code-form ltn__form-box">
                                    <p>Please login your accont.</p>
                                    <form action="#" >
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="input-item input-item-name ltn__custom-icon">
                                                    <input type="text" name="ltn__name" placeholder="Enter your name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="input-item input-item-email ltn__custom-icon">
                                                    <input type="email" name="ltn__email" placeholder="Enter email address">
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn theme-btn-1 btn-effect-1 text-uppercase">Login</button>
                                        <label class="input-info-save mb-0"><input type="checkbox" name="agree"> Remember me</label>
                                        <p class="mt-30"><a href="register.html">Lost your password?</a></p>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="ltn__checkout-single-content ltn__coupon-code-wrap d-none">
                            <h5>Have a coupon? <a class="ltn__secondary-color" href="#ltn__coupon-code" data-bs-toggle="collapse">Click here to enter your code</a></h5>
                            <div id="ltn__coupon-code" class="collapse ltn__checkout-single-content-info">
                                <div class="ltn__coupon-code-form">
                                    <p>If you have a coupon code, please apply it below.</p>
                                    <form action="#" >
                                        <input type="text" name="coupon-code" placeholder="Coupon code">
                                        <button class="btn theme-btn-2 btn-effect-2 text-uppercase">Apply Coupon</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="ltn__checkout-single-content mt-50">
                            <h4 class="title-2">Billing Details</h4>
                            <div class="ltn__checkout-single-content-info">
                                <form action="#" >
                                    <h6>Personal Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-item input-item-name ltn__custom-icon">
                                                <input type="text" name="ltn__name" placeholder="First name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-name ltn__custom-icon">
                                                <input type="text" name="ltn__lastname" placeholder="Last name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-email ltn__custom-icon">
                                                <input type="email" name="ltn__email" placeholder="email address">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-phone ltn__custom-icon">
                                                <input type="text" name="ltn__phone" placeholder="phone number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-website ltn__custom-icon">
                                                <input type="text" name="ltn__company" placeholder="Company name (optional)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-item input-item-website ltn__custom-icon">
                                                <input type="text" name="ltn__phone" placeholder="Company address (optional)">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Country</h6>
                                            <div class="input-item">
                                                <select class="nice-select">
                                                    <option>Select Country</option>
                                                    <option>Australia</option>
                                                    <option>Canada</option>
                                                    <option>China</option>
                                                    <option>Morocco</option>
                                                    <option>Saudi Arabia</option>
                                                    <option>United Kingdom (UK)</option>
                                                    <option>United States (US)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-12">
                                            <h6>Address</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="input-item">
                                                        <input type="text" placeholder="House number and street name">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="input-item">
                                                        <input type="text" placeholder="Apartment, suite, unit etc. (optional)">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Town / City</h6>
                                            <div class="input-item">
                                                <input type="text" placeholder="City">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>State </h6>
                                            <div class="input-item">
                                                <input type="text" placeholder="State">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <h6>Zip</h6>
                                            <div class="input-item">
                                                <input type="text" placeholder="Zip">
                                            </div>
                                        </div>
                                    </div>
                                    <p><label class="input-info-save mb-0"><input type="checkbox" name="agree"> Create an account?</label></p>
                                    <h6>Order Notes (optional)</h6>
                                    <div class="input-item input-item-textarea ltn__custom-icon">
                                        <textarea name="ltn__message" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="ltn__checkout-payment-method mt-50">
                        <h4 class="title-2">Payment Method</h4>
                        <div id="checkout_accordion_1">
                            <!-- card -->
                            <div class="card">
                                <h5 class="collapsed ltn__card-title" data-bs-toggle="collapse" data-bs-target="#faq-item-2-1" aria-expanded="false">
                                    Check payments
                                </h5>
                                <div id="faq-item-2-1" class="collapse" data-bs-parent="#checkout_accordion_1">
                                    <div class="card-body">
                                        <p>Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- card -->
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
                            <!-- card -->
                            <div class="card">
                                <h5 class="collapsed ltn__card-title" data-bs-toggle="collapse" data-bs-target="#faq-item-2-3" aria-expanded="false" >
                                    Card <img src="img/icons/payment-3.png" alt="#">
                                </h5>
                                <div id="faq-item-2-3" class="collapse" data-bs-parent="#checkout_accordion_1">
                                    <div class="card-body">
                                        <p>Pay via Xendit; you can pay with your credit card if you don’t have a PayPal account.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ltn__payment-note mt-30 mb-30">
                            <p>Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.</p>
                        </div>
                        <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">Place order</button>
                    </div>
                </div>
                <div class="col-lg-6">
                <div class="shoping-cart-total mt-50" id="order-summary">
    <h4 class="title-2">Order Summary</h4>

    <!-- Product picker -->
    <div class="mb-20" style="position:relative;">
        <div class="row g-2">
            <div class="col-12 col-md-8" style="position:relative;">
                <input id="product-search" type="text" class="form-control" placeholder="Search products..." autocomplete="off">
                <!-- suggestions -->
                <div id="product-suggestions" class="box-shadow" style="display:none; position:absolute; z-index:9999; background:#fff; width:100%; max-height:260px; overflow:auto; border:1px solid #eee; top:100%; left:0;">
                    <!-- filled by JS -->
                </div>
            </div>
            <div class="col-6 col-md-2">
                <input id="product-qty" type="number" min="1" value="1" class="form-control" aria-label="Quantity">
            </div>
            <div class="col-6 col-md-2">
                <button id="add-to-order" type="button" class="theme-btn-1 btn btn-effect-1 w-100" disabled>Add</button>
            </div>
        </div>
        <small id="picker-hint" class="text-muted d-block mt-1">Type to search, click a suggestion, set qty, then Add.</small>
    </div>

    <table class="table">
        <tbody id="order-lines">
            <!-- rows and totals injected by JS to preserve layout -->
        </tbody>
    </table>

    <input type="hidden" name="order_json" id="order-json" value="[]">
</div>

<script>
(function () {
    const CURRENCY = "₱";
    const VAT_RATE = 0.12;
    const SHIPPING_FLAT = 0;
    const SUGGESTION_LIMIT = 8;

    const els = {
        search: document.getElementById("product-search"),
        suggestions: document.getElementById("product-suggestions"),
        qty: document.getElementById("product-qty"),
        addBtn: document.getElementById("add-to-order"),
        lines: document.getElementById("order-lines"),
        orderJson: document.getElementById("order-json")
    };

    const state = {
        products: [],
        selectedProduct: null,
        cart: []
    };

    // --- Utilities
    const money = n => CURRENCY + Number(n || 0).toFixed(2);
    const safe = s => (s ?? "").toString();
    const pickable = p => p && !p.deleted && p.is_active && (p.price !== null);

    function withTimeout(ms, promise) {
        const ctrl = new AbortController();
        const t = setTimeout(() => ctrl.abort(), ms);
        return promise(ctrl.signal).finally(() => clearTimeout(t));
    }

    // Build several candidate URLs so it works whether the page lives in / or in a subfolder
    function candidateUrls() {
        const ts = "nocache=" + Date.now();
        const here = new URL(window.location.href);
        const sameDir = new URL("products.php?" + ts, here);
        const root = new URL("/products.php?" + ts, window.location.origin);
        // If there is a <base> tag, respect it
        const baseEl = document.querySelector("base[href]");
        const fromBase = baseEl ? new URL("products.php?" + ts, baseEl.href) : null;

        const urls = [sameDir, root];
        if (fromBase) urls.unshift(fromBase);
        // remove duplicates
        const seen = new Set();
        return urls.filter(u => {
            const k = u.toString();
            if (seen.has(k)) return false;
            seen.add(k);
            return true;
        });
    }

    async function fetchProducts() {
        const urls = candidateUrls();
        for (const u of urls) {
            try {
                const data = await withTimeout(7000, (signal) =>
                    fetch(u.toString(), { credentials: "same-origin", signal }).then(r => r.json())
                );
                if (data && data.ok && Array.isArray(data.items)) {
                    return data.items.filter(pickable);
                }
            } catch (e) {
                // try next candidate
            }
        }
        return [];
    }

    function searchProducts(q) {
        q = q.trim().toLowerCase();
        if (!q) return [];
        return state.products.filter(p => {
            const hay = [
                safe(p.name),
                safe(p.description),
                safe(p.category_name),
                safe(p.sku)
            ].join(" ").toLowerCase();
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
            const name = safe(p.name);
            return `
                <button type="button" class="w-100 text-start suggestion-item" data-id="${p.id}"
                        style="display:flex; gap:10px; align-items:center; padding:8px 10px; background:#fff; border:0; border-bottom:1px solid #f1f1f1; cursor:pointer;">
                    <img src="${img}" alt="${name}" style="width:38px;height:38px;object-fit:cover;border-radius:4px;">
                    <span style="flex:1 1 auto; font-size:14px; line-height:1.2;">${name}</span>
                    <span style="white-space:nowrap; font-weight:600;">${price}</span>
                </button>
            `;
        }).join("");
        els.suggestions.innerHTML = items;
        els.suggestions.style.display = "block";
    }

    function selectById(id) {
        const p = state.products.find(x => String(x.id) === String(id));
        if (!p) return;
        state.selectedProduct = p;
        els.search.value = p.name;
        els.addBtn.disabled = false;
        els.suggestions.style.display = "none";
    }

    function addSelectedToCart() {
        const p = state.selectedProduct;
        const qty = Math.max(1, parseInt(els.qty.value, 10) || 1);
        if (!p) return;
        const existing = state.cart.find(i => String(i.id) === String(p.id));
        if (existing) existing.qty += qty;
        else state.cart.push({ id: p.id, name: p.name, price: Number(p.price || 0), qty });
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
                </tr>
            `);
        });

        const vat = subtotal * VAT_RATE;
        const shipping = state.cart.length ? SHIPPING_FLAT : 0;
        const total = subtotal + vat + shipping;

        rows.push(`
            <tr>
                <td>Shipping and Handling</td>
                <td>${money(shipping)}</td>
            </tr>
            <tr>
                <td>VAT</td>
                <td>${money(vat)}</td>
            </tr>
            <tr>
                <td><strong>Order Total</strong></td>
                <td><strong>${money(total)}</strong></td>
            </tr>
        `);

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
        debounce = setTimeout(() => {
            const list = searchProducts(q);
            renderSuggestions(list);
        }, 120);
    });

    // Use mousedown so the click still registers if the input loses focus
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

    // --- Init
    // Show a small loading hint in the suggestions area if user starts typing early
    els.search.addEventListener("focus", function () {
        if (!state.products.length && els.search.value.trim().length) {
            els.suggestions.innerHTML = '<div style="padding:10px;">Loading products...</div>';
            els.suggestions.style.display = "block";
        }
    });

    fetchProducts().then(items => {
        state.products = items;
        // If the user already typed something, refresh suggestions now
        if (els.search.value.trim().length) {
            renderSuggestions(searchProducts(els.search.value));
        }
    });
})();
</script>

                </div>
            </div>
        </div>
    </div>
    <!-- WISHLIST AREA START -->

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
</div>
<!-- Body main wrapper end -->

    <!-- All JS Plugins -->
    <script src="js/plugins.js"></script>
    <!-- Main JS -->
    <script src="js/main.js"></script>
  
</body>
</html>

