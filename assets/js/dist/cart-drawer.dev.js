"use strict";

/** @format */
(function ($) {
  function ajaxCart(action, data) {
    return $.post(vsCart.ajaxUrl, $.extend({
      action: action,
      nonce: vsCart.nonce
    }, data || {}));
  }

  function openDrawer() {
    $(".js-vs-cart-backdrop").addClass("is-active");
    $(".js-vs-cart-drawer").addClass("is-active").attr("aria-hidden", "false");
    $("body").addClass("is-cart-open");
  }

  function closeDrawer() {
    $(".js-vs-cart-backdrop").removeClass("is-active");
    $(".js-vs-cart-drawer").removeClass("is-active").attr("aria-hidden", "true");
    $("body").removeClass("is-cart-open");
  }

  function replaceDrawerHtml(html) {
    $(".js-vs-cart-content").html(html);
  }

  function updateCartCount(count) {
    $(".js-cart-count").text(count);
  }

  function refreshDrawer(openAfter) {
    ajaxCart("vs_cart_get").done(function (res) {
      if (!res || !res.success) return;
      replaceDrawerHtml(res.data.html);
      updateCartCount(res.data.count);
      if (openAfter) openDrawer();
    });
  }

  function getProductPayload($trigger) {
    var $root = $trigger.closest(".js-vs-single-product");
    if (!$root.length) return null;
    var $form = $root.find(".js-product-form").first();
    return {
      product_id: $form.find('input[name="product_id"]').val() || "",
      color: $form.find(".js-selected-color").val() || "",
      size: $form.find(".js-selected-size").val() || "",
      qty: $form.find(".js-selected-quantity").val() || 1
    };
  }

  $(document).on("click", ".vs-sp__add-cart, .vs-sp-sticky__button, .vs-sp__buy-now", function (e) {
    e.preventDefault();
    var payload = getProductPayload($(this));
    if (!payload || !payload.product_id) return;
    ajaxCart("vs_cart_add", payload).done(function (res) {
      if (!res || !res.success) {
        alert(res && res.data && res.data.message ? res.data.message : "Có lỗi xảy ra.");
        return;
      }

      replaceDrawerHtml(res.data.html);
      updateCartCount(res.data.count);
      openDrawer();
    });
  });
  $(document).on("click", ".js-vs-cart-open", function (e) {
    e.preventDefault();
    refreshDrawer(true);
  });
  $(document).on("click", ".js-vs-cart-close, .js-vs-cart-backdrop", function () {
    closeDrawer();
  });
  $(document).on("click", ".js-vs-cart-remove", function () {
    var key = $(this).data("key");
    if (!key) return;
    ajaxCart("vs_cart_remove", {
      key: key
    }).done(function (res) {
      if (!res || !res.success) return;
      replaceDrawerHtml(res.data.html);
      updateCartCount(res.data.count);
    });
  });
  $(document).on("click", ".js-vs-cart-qty-minus, .js-vs-cart-qty-plus", function () {
    var $item = $(this).closest(".vs-cart-item");
    var key = $(this).data("key");
    var currentQty = parseInt($item.find(".vs-cart-item__qty-value").text(), 10) || 1;
    var nextQty = $(this).hasClass("js-vs-cart-qty-plus") ? currentQty + 1 : currentQty - 1;
    ajaxCart("vs_cart_update_qty", {
      key: key,
      qty: nextQty
    }).done(function (res) {
      if (!res || !res.success) return;
      replaceDrawerHtml(res.data.html);
      updateCartCount(res.data.count);
    });
  });
  $(document).on("keydown", function (e) {
    if (e.key === "Escape") closeDrawer();
  });
  $(function () {
    refreshDrawer(false);
  });
})(jQuery);