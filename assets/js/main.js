(function ($) {
  "use strict";

function initSiteHeader() {
  var $window = $(window);
  var $body = $("body");
  var $header = $(".js-site-header");
  var $headerMain = $header.find(".site-header__main");
  var $drawer = $("#mobileDrawer");
  var $promo = $(".js-header-promo");
  var $searchPanel = $(".js-header-search");
  var $searchToggles = $(".js-search-toggle");
  var promoStorageKey = "site_header_promo_closed_until";

  if (!$header.length || !$headerMain.length) return;

  var headerMainTop = 0;
  var ticking = false;

  function measureHeaderMainTop() {
    $header.css("padding-bottom", "");
    $headerMain.removeClass("is-fixed is-scrolled");
    headerMainTop = $headerMain.offset().top || 0;
  }

  function syncScrolledState() {
    var scrollTop = $window.scrollTop();
    var mainHeight = $headerMain.outerHeight() || 0;
    var shouldFix = scrollTop >= headerMainTop;

    $header.css("padding-bottom", shouldFix ? mainHeight + "px" : "");

    $headerMain
      .toggleClass("is-fixed", shouldFix)
      .toggleClass("is-scrolled", shouldFix);
  }

  function requestSync() {
    if (ticking) return;

    ticking = true;

    window.requestAnimationFrame(function () {
      syncScrolledState();
      ticking = false;
    });
  }

  function refreshHeaderStickyState() {
    measureHeaderMainTop();
    syncScrolledState();
  }

  function openDrawer() {
    if (!$drawer.length) return;
    $drawer.addClass("is-open").attr("aria-hidden", "false");
    $body.addClass("header-drawer-open");
    $(".js-mobile-menu-trigger").attr("aria-expanded", "true");
  }

  function closeDrawer() {
    if (!$drawer.length) return;
    $drawer.removeClass("is-open").attr("aria-hidden", "true");
    $body.removeClass("header-drawer-open");
    $(".js-mobile-menu-trigger").attr("aria-expanded", "false");
  }

  function openSearch() {
    if (!$searchPanel.length) return;

    $searchPanel.addClass("is-open").attr("aria-hidden", "false");
    $searchToggles.attr("aria-expanded", "true");

    if (window.innerWidth < 1024) {
      $body.addClass("header-search-open");
    }

    window.setTimeout(function () {
      $searchPanel.find('input[type="text"]').trigger("focus");
      refreshHeaderStickyState();
    }, 60);
  }

  function closeSearch() {
    if (!$searchPanel.length) return;

    $searchPanel.removeClass("is-open").attr("aria-hidden", "true");
    $searchToggles.attr("aria-expanded", "false");
    $body.removeClass("header-search-open");
    refreshHeaderStickyState();
  }

  function toggleSearch() {
    if ($searchPanel.hasClass("is-open")) {
      closeSearch();
    } else {
      openSearch();
    }
  }
$(document).on("click",function(e){
  const $target = $(e.target);
  if(!$target.closest('#siteHeaderSearch').length && !$target.closest('.js-search-toggle').length){
    closeSearch();
  }
});
  if ($promo.length) {
    var promoExpireMs = 3 * 24 * 60 * 60 * 1000;
    var now = Date.now();

    try {
      var promoClosedUntil = parseInt(
        window.sessionStorage.getItem(promoStorageKey) || "0",
        10
      );

      if (!Number.isNaN(promoClosedUntil) && promoClosedUntil > now) {
        $promo.hide();
      } else {
        window.sessionStorage.removeItem(promoStorageKey);
      }
    } catch (error) {}

    $(document).on("click", ".js-header-promo-close", function () {
      $promo.slideUp(180, function () {
        refreshHeaderStickyState();
      });

      try {
        window.sessionStorage.setItem(
          promoStorageKey,
          String(Date.now() + promoExpireMs)
        );
      } catch (error) {}
    });
  }

  $(document).on("click", ".js-mobile-menu-trigger", function () {
    openDrawer();
    closeSearch();
  });

  $(document).on("click", ".js-mobile-close", function () {
    closeDrawer();
  });

  $(document).on("click", ".js-search-toggle", function (e) {
    e.preventDefault();
    closeDrawer();
    toggleSearch();
  });

  $(document).on("click", function (e) {
    var $target = $(e.target);
    var clickedInsideSearch = $target.closest(
      ".js-header-search, .js-search-toggle"
    ).length;

    if (
      !clickedInsideSearch &&
      $searchPanel.hasClass("is-open") &&
      window.innerWidth >= 1024
    ) {
      closeSearch();
    }
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      closeDrawer();
      closeSearch();
    }
  });

  $window.on("scroll", requestSync);

  $window.on("load resize orientationchange", function () {
    if (window.innerWidth >= 1024) {
      closeDrawer();
      $body.removeClass("header-search-open");
    }

    refreshHeaderStickyState();
  });

  refreshHeaderStickyState();
}

  function initPasswordToggle() {
    $(document).on("click", "[data-toggle-password]", function (e) {
      e.preventDefault();

      var $button = $(this);
      var targetSelector = $button.attr("data-toggle-password");
      if (!targetSelector) return;

      var $input = $(targetSelector);
      if (!$input.length) return;

      var isPassword = $input.attr("type") === "password";
      $input.attr("type", isPassword ? "text" : "password");
      $button.toggleClass("is-visible", isPassword);
      $button.attr(
        "aria-label",
        isPassword ? "Ẩn mật khẩu" : "Hiện hoặc ẩn mật khẩu",
      );
    });

    $(document).on("submit", ".vs-auth form", function () {
      var $form = $(this);
      var $submitButton = $form.find('button[type="submit"]');

      if ($submitButton.length) {
        $submitButton.prop("disabled", true).addClass("is-loading");
      }
    });
  }

  $(function () {
    initSiteHeader();
    initPasswordToggle();
  });
})(jQuery);

(function ($) {
    function initNewsletterForm() {
        const $form = $('.js-newsletter-form');

        if (!$form.length) {
            return;
        }

        $form.on('submit', function (e) {
            e.preventDefault();

            const $currentForm = $(this);
            const $message = $('.js-newsletter-message');
            const $button = $currentForm.find('button[type="submit"]');
            const email = $.trim($currentForm.find('input[name="email"]').val());

            $message.removeClass('is-success is-error').text('');

            if (!email) {
                $message.addClass('is-error').text('Vui lòng nhập email.');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $message.addClass('is-error').text('Email chưa đúng định dạng.');
                return;
            }

            $.ajax({
                url: window.vsNewsletter ? window.vsNewsletter.ajaxUrl : '/wp-admin/admin-ajax.php',
                type: 'POST',
                dataType: 'json',
                data: $currentForm.serialize(),
                beforeSend: function () {
                    $button.prop('disabled', true);
                    $currentForm.addClass('is-loading');
                    $message.text('Đang gửi...');
                },
                success: function (response) {
                    const message = response && response.data && response.data.message
                        ? response.data.message
                        : 'Đã xử lý yêu cầu.';

                    if (response.success) {
                        $message.removeClass('is-error').addClass('is-success').text(message);
                        $currentForm.find('input[name="email"]').val('');
                    } else {
                        $message.removeClass('is-success').addClass('is-error').text(message);
                    }
                },
                error: function () {
                    $message
                        .removeClass('is-success')
                        .addClass('is-error')
                        .text('Có lỗi xảy ra. Vui lòng thử lại sau.');
                },
                complete: function () {
                    $button.prop('disabled', false);
                    $currentForm.removeClass('is-loading');
                }
            });
        });
    }

    $(document).ready(function () {
        initNewsletterForm();
    });
     function initNewsletterAutoScroll() {
        const params = new URLSearchParams(window.location.search);
        const newsletterStatus = params.get('newsletter');

        if (newsletterStatus !== 'unsubscribed') {
            return;
        }

        $(window).on('load', function () {
            const $target = $('.subscribe-form').length
                ? $('.subscribe-form')
                : $('body');

            $('html, body').animate({
                scrollTop: $target.offset().top
            }, 600);
        });
    }

    $(document).ready(function () {
        initNewsletterAutoScroll();
    });
})(jQuery);