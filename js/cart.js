// cart.js — vanilla JS glue for Add to Cart (works with your existing markup)
(function () {
    function peso(n) {
      var x = parseFloat(n);
      if (isNaN(x)) x = 0;
      return "₱" + x.toFixed(2);
    }
  
    function api(action, data) {
      var body = new URLSearchParams(Object.assign({ action: action }, data || {}));
      return fetch("cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: body.toString(),
        credentials: "same-origin",
      }).then(function (r) { return r.json(); });
    }
  
    function renderMiniCartItems(state) {
      var wrap = document.querySelector("#ltn__utilize-cart-menu .mini-cart-product-area");
      if (!wrap) return;
  
      if (!state || !state.items || !state.items.length) {
        wrap.innerHTML = '<div class="mini-cart-item clearfix"><div class="mini-cart-info"><h6>Your cart is empty</h6></div></div>';
        return;
      }
  
      var html = state.items.map(function (it) {
        return (
          '<div class="mini-cart-item clearfix">' +
            '<div class="mini-cart-img">' +
              '<a href="product-details.php?id=' + encodeURIComponent(it.id) + '"><img src="' + (it.image_url || 'img/product/placeholder.png') + '" alt=""></a>' +
              '<span class="mini-cart-item-delete" data-id="' + String(it.id) + '"><i class="icon-trash"></i></span>' +
            '</div>' +
            '<div class="mini-cart-info">' +
              '<h6><a href="product-details.php?id=' + encodeURIComponent(it.id) + '">' + (it.name || 'Product') + '</a></h6>' +
              '<span class="mini-cart-quantity">' + String(it.qty) + ' x ' + peso(it.price) + '</span>' +
            '</div>' +
          '</div>'
        );
      }).join("");
      wrap.innerHTML = html;
    }
  
    function refreshMiniCart() {
      api("get").then(function (res) {
        if (!res || !res.ok) return;
        var cart = res.cart || { count: 0, subtotal: 0, items: [] };
  
        // Update icon count & subtotal text in header (keeps your existing markup)
        var sup = document.querySelector(".mini-cart-icon sup");
        if (sup) sup.textContent = String(cart.count);
  
        var totalEl = document.querySelector(".mini-cart-icon h6 .ltn__secondary-color");
        if (totalEl) totalEl.textContent = peso(cart.subtotal);
  
        // Render items inside utilize cart menu
        renderMiniCartItems(cart);
      })["catch"](function () {});
    }
  
    function findProductContext(el) {
        var id = null, qty = 1;
      
        // Prefer an explicit data-id on the clicked button/anchor
        if (el && el.dataset && el.dataset.id) id = el.dataset.id;
      
        // Nearest quantity input
        var scope = el.closest(".ltn__product-item, .shop-details-info, .modal-product-info") || document;
        var qtyInput = scope.querySelector(".cart-plus-minus-box");
        if (qtyInput && qtyInput.value) {
          var q = parseInt(qtyInput.value, 10);
          if (!isNaN(q) && q > 0) qty = q;
        }
      
        // In Quick View modal? fallback to modal-stashed id
        if (!id) {
          var qvModal = document.getElementById("quick_view_modal");
          if (qvModal && qvModal.contains(el)) {
            id = qvModal.dataset.productId || id;
          }
        }
      
        // On a product card? read sibling .quick-view-btn data-id (your cards already have this)
        if (!id && scope) {
          var qv = scope.querySelector(".quick-view-btn");
          if (qv && qv.dataset && qv.dataset.id) id = qv.dataset.id;
        }
      
        // Last resort: parse ?id= from a product-details link or the URL
        if (!id && scope) {
          var link = scope.querySelector('a[href*="product-details.php?id="]');
          if (link) {
            try { id = new URL(link.href, location.href).searchParams.get("id"); } catch (e) {}
          }
        }
        if (!id) {
          try { id = new URL(location.href).searchParams.get("id") || id; } catch (e) {}
        }
      
        return { id: id, qty: qty };
      }
      
  
    // Add to Cart clicks (works for both product cards and product details)
    document.addEventListener("click", function (e) {
      var addBtn = e.target.closest(".add-to-cart a, .d-add-to-cart");
      if (!addBtn) return;
  
      // Call API; do NOT prevent default so your Bootstrap modal still opens
      var ctx = findProductContext(addBtn);
      if (!ctx.id) return;
  
      api("add", { id: ctx.id, qty: ctx.qty }).then(function (res) {
        if (res && res.ok) refreshMiniCart();
      })["catch"](function () {});
    });
  
    // Remove from mini cart (trash icon)
    document.addEventListener("click", function (e) {
      var del = e.target.closest(".mini-cart-item-delete");
      if (!del) return;
      var id = del.getAttribute("data-id");
      if (!id) return;
      e.preventDefault();
      api("remove", { id: id }).then(function (res) {
        if (res && res.ok) refreshMiniCart();
      })["catch"](function () {});
    });
  
    // Init on page load
    document.addEventListener("DOMContentLoaded", refreshMiniCart);
  })();
  