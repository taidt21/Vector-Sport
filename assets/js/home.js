jQuery(function ($) {
  var el = document.querySelector(".js-home-hero");
  if (!el || typeof Swiper === "undefined") return;

  // Swiper vẫn chạy bình thường
  new Swiper(".js-home-hero", {
    loop: true,
    speed: 900,
    // autoplay: { delay: 4500, disableOnInteraction: false },
    effect: "slide",
    fadeEffect: { crossFade: true },
    pagination: { el: ".home_hero__pagination", clickable: true },
    watchSlidesProgress: true,
  });

  // Click thumb -> đổi ảnh nền trong slide tương ứng
  $(document).on("click", ".js-hero-thumb", function (e) {
    e.preventDefault();
    e.stopPropagation(); // tránh Swiper hiểu nhầm là drag/click

    var $btn = $(this);
    var src = $btn.data("src");
    var slideKey = $btn.data("slideKey");
    if (!src) return;

    // update tất cả slide cùng key (real + clone khi loop:true)
    var $slides = $('.home_hero__slide[data-slide-key="' + slideKey + '"]');
    if (!$slides.length) $slides = $btn.closest(".home_hero__slide");

    // active thumb trong cụm đó
    $slides
      .find(".js-hero-thumb")
      .removeClass("is-active")
      .attr("aria-pressed", "false");
    $btn.addClass("is-active").attr("aria-pressed", "true");

    // preload + decode để tránh giật
    var img = new Image();
    img.decoding = "async";
    img.src = src;

    var apply = function () {
      $slides.each(function () {
        var bg = this.querySelector(".home_hero__bg");
        if (!bg) return;

        // nếu đang là ảnh đó rồi thì thôi
        var current = bg.style.backgroundImage || "";
        if (current.indexOf(src) !== -1) return;

        // fade nhẹ (nếu bạn dùng CSS ::after ở trên)
        bg.classList.add("is-fading");

        // đổi background
        bg.style.backgroundImage = "url('" + src + "')";

        // bỏ fade
        window.setTimeout(function () {
          bg.classList.remove("is-fading");
        }, 200);
      });
    };

    if (img.decode) {
      img
        .decode()
        .then(apply)
        .catch(function () {
          img.onload = apply;
        });
    } else {
      img.onload = apply;
    }
  });
    function setFaqPanel($item, isOpen) {
        const $button = $item.find('.js-home-faq-toggle');
        const $panel = $item.find('.home_faq__answer');

        $button.attr('aria-expanded', isOpen ? 'true' : 'false');
        $item.toggleClass('is-active', isOpen);

        if (isOpen) {
            $panel.css('max-height', $panel.prop('scrollHeight') + 'px');
        } else {
            $panel.css('max-height', '0px');
        }
    }

    function initHomeFaq() {
        const $items = $('.home_faq__item');

        if (!$items.length) {
            return;
        }

        $items.each(function () {
            const $item = $(this);
            const isOpen = $item.hasClass('is-active');

            setFaqPanel($item, isOpen);
        });

        $('.js-home-faq-toggle').on('click', function () {
            const $currentItem = $(this).closest('.home_faq__item');
            const isOpen = $currentItem.hasClass('is-active');

            $items.each(function () {
                setFaqPanel($(this), false);
            });

            if (!isOpen) {
                setFaqPanel($currentItem, true);
            }
        });

        $(window).on('resize', function () {
            $('.home_faq__item.is-active').each(function () {
                const $panel = $(this).find('.home_faq__answer');
                $panel.css('max-height', $panel.prop('scrollHeight') + 'px');
            });
        });
    }

    $(document).ready(function () {
        initHomeFaq();
    });
});
