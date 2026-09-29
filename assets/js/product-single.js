(function ($) {
  function ProductSingle($root) {
    this.$root = $root;
    this.productId = parseInt($root.data("product-id"), 10) || 0;
    this.galleryData = $root.data("gallery") || [];
    this.currentColorIndex = 0;
    this.currentImageIndex = 0;
    this.quantity = 1;
    this.mainSwiper = null;
    this.thumbsSwiper = null;
    this.init();
  }

  ProductSingle.prototype.init = function () {
    this.bindColors();
    this.bindSizes();
    this.bindQuantity();
    this.bindAccordion();
    this.bindReadMore();
    this.bindStickyBar();
    this.saveRecentlyViewed();
    this.syncAll();
    this.renderGallery();
  };

  ProductSingle.prototype.bindColors = function () {
    var self = this;

    self.$root.on("click", ".js-color-swatch", function () {
      var colorIndex = parseInt($(this).data("color-index"), 10) || 0;
      self.changeColor(colorIndex);
    });

    self.$root.on("change", ".js-sticky-color", function () {
      var colorIndex = parseInt($(this).val(), 10) || 0;
      self.changeColor(colorIndex);
    });
  };

  ProductSingle.prototype.bindSizes = function () {
    var self = this;

    self.$root.on("click", ".js-size-item", function () {
      var size = $(this).data("size") || "";
      self.changeSize(size);
    });

    self.$root.on("change", ".js-sticky-size", function () {
      self.changeSize($(this).val() || "");
    });
  };

  ProductSingle.prototype.bindQuantity = function () {
    var self = this;

    self.$root.on("click", ".js-qty-minus", function () {
      self.setQuantity(self.quantity - 1);
    });

    self.$root.on("click", ".js-qty-plus", function () {
      self.setQuantity(self.quantity + 1);
    });
  };

  ProductSingle.prototype.bindAccordion = function () {
    var $wrap = this.$root.find(".vs-sp__detail-main");

    if (!$wrap.length) return;

    var $items = $wrap.find(".js-accordion");

    $items.removeClass("is-open");
    $items.find(".js-accordion-body").hide();

    var $first = $items.first();
    $first.addClass("is-open");
    $first.find(".js-accordion-body").show();

    this.$root.on("click", ".js-accordion-head", function () {
      var $currentAccordion = $(this).closest(".js-accordion");
      var $currentBody = $currentAccordion.find(".js-accordion-body");
      var $parentWrap = $currentAccordion.closest(".vs-sp__detail-main");
      var isOpen = $currentAccordion.hasClass("is-open");

      if (isOpen) {
        $currentAccordion.removeClass("is-open");
        $currentBody.stop(true, true).slideUp();
        return;
      }

      $parentWrap.find(".js-accordion").removeClass("is-open");
      $parentWrap.find(".js-accordion-body").stop(true, true).slideUp();

      $currentAccordion.addClass("is-open");
      $currentBody.stop(true, true).slideDown();
    });
  };

  ProductSingle.prototype.bindReadMore = function () {
    var self = this;
    var $content = self.$root.find(".js-readmore-content");
    var $button = self.$root.find(".js-readmore-toggle");

    if (!$content.length || !$button.length) return;

    function updateReadMoreButton() {
      if (!$content.length || !$content[0]) return;

      if (
        $content.hasClass("is-expanded") ||
        $content[0].scrollHeight <= $content.outerHeight() + 10
      ) {
        $button.hide();
      } else {
        $button.show();
      }
    }

    setTimeout(updateReadMoreButton, 50);
    $(window).on("resize", updateReadMoreButton);

    $button.on("click", function () {
      $content.addClass("is-expanded");
      $button.hide();
    });
  };

  ProductSingle.prototype.bindStickyBar = function () {
    var self = this;
    var $sticky = self.$root.find(".js-product-sticky");
    var $top = self.$root.find(".vs-sp__top");

    if (!$sticky.length || !$top.length) return;

    function toggleSticky() {
      if (window.innerWidth < 1024) {
        $sticky.removeClass("is-visible");
        return;
      }

      var rect = $top[0].getBoundingClientRect();

      var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      var windowHeight = window.innerHeight;
      var docHeight = Math.max(
        document.body.scrollHeight,
        document.documentElement.scrollHeight,
      );

      var isNearBottom = scrollTop + windowHeight >= docHeight - 20;

      if (rect.bottom < 180 && !isNearBottom) {
        $sticky.addClass("is-visible");
      } else {
        $sticky.removeClass("is-visible");
      }
    }

    toggleSticky();
    $(window).on("scroll resize", toggleSticky);
  };

  ProductSingle.prototype.getCurrentImages = function () {
    if (
      !Array.isArray(this.galleryData) ||
      !this.galleryData[this.currentColorIndex]
    ) {
      return [];
    }

    return this.galleryData[this.currentColorIndex].images || [];
  };

  ProductSingle.prototype.escapeAttr = function (value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/"/g, "&quot;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  };

  ProductSingle.prototype.destroyGallery = function () {
    if (this.mainSwiper && typeof this.mainSwiper.destroy === "function") {
      this.mainSwiper.destroy(true, true);
      this.mainSwiper = null;
    }

    if (this.thumbsSwiper && typeof this.thumbsSwiper.destroy === "function") {
      this.thumbsSwiper.destroy(true, true);
      this.thumbsSwiper = null;
    }
  };

  ProductSingle.prototype.buildThumbSlides = function (images) {
    var self = this;

    return $.map(images, function (image) {
      var thumbSrc = image.thumb || image.full || "";
      var altText = self.escapeAttr(image.alt || "");

      return [
        '<div class="vs-sp__thumb swiper-slide">',
        '<img src="' + thumbSrc + '" alt="' + altText + '">',
        "</div>",
      ].join("");
    }).join("");
  };

  ProductSingle.prototype.buildMainSlides = function (images) {
    var self = this;

    return $.map(images, function (image) {
      var fullSrc = image.full || image.thumb || "";
      var altText = self.escapeAttr(image.alt || "");

      return [
        '<div class="vs-sp__main-slide swiper-slide">',
        '<img src="' + fullSrc + '" alt="' + altText + '">',
        "</div>",
      ].join("");
    }).join("");
  };

  ProductSingle.prototype.initGallery = function () {
    var self = this;
    var $thumbs = self.$root.find(".js-gallery-thumbs-swiper");
    var $main = self.$root.find(".js-gallery-main-swiper");
    var $dots = self.$root.find(".js-gallery-dots");
    var $next = self.$root.find(".js-gallery-next");
    var $prev = self.$root.find(".js-gallery-prev");
    var images = self.getCurrentImages();

    if (
      !$thumbs.length ||
      !$main.length ||
      typeof window.Swiper !== "function"
    ) {
      return;
    }

    self.destroyGallery();

    self.thumbsSwiper = new window.Swiper($thumbs[0], {
      slidesPerView: "auto",
      spaceBetween: 10,
      watchSlidesProgress: true,
      slideToClickedSlide: true,
      observer: true,
      observeParents: true,
      direction: "horizontal",
      breakpoints: {
        1024: {
          direction: "vertical",
          slidesPerView: 4,
          spaceBetween: 14,
        },
      },
    });

    self.mainSwiper = new window.Swiper($main[0], {
      slidesPerView: 1,
      speed: 450,
      observer: true,
      observeParents: true,
      autoHeight: false,
      navigation:
        $next.length && $prev.length && images.length > 1
          ? {
              nextEl: $next[0],
              prevEl: $prev[0],
            }
          : undefined,
      pagination:
        images.length > 1 && $dots.length
          ? {
              el: $dots[0],
              clickable: true,
              bulletClass: "vs-sp__dot",
              bulletActiveClass: "is-active",
            }
          : undefined,
      thumbs: {
        swiper: self.thumbsSwiper,
      },
      on: {
        init: function () {
          self.currentImageIndex = 0;
          self.updateStickyImage(0);
        },
        slideChange: function () {
          self.currentImageIndex = this.activeIndex || 0;
          self.updateStickyImage(this.activeIndex || 0);
        },
      },
    });

    setTimeout(function () {
      if (self.thumbsSwiper) self.thumbsSwiper.update();
      if (self.mainSwiper) {
        self.mainSwiper.update();
        self.mainSwiper.slideTo(0, 0);
      }
    }, 30);
  };

  ProductSingle.prototype.renderGallery = function () {
    var self = this;
    var images = self.getCurrentImages();
    var $thumbWrapper = self.$root.find(
      ".js-gallery-thumbs-swiper .swiper-wrapper",
    );
    var $mainWrapper = self.$root.find(
      ".js-gallery-main-swiper .swiper-wrapper",
    );
    var $dots = self.$root.find(".js-gallery-dots");
    var $next = self.$root.find(".js-gallery-next");
    var $prev = self.$root.find(".js-gallery-prev");

    self.currentImageIndex = 0;
    self.destroyGallery();

    if (!$thumbWrapper.length || !$mainWrapper.length) {
      return;
    }

    if (!images.length) {
      $thumbWrapper.empty();
      $mainWrapper.empty();
      $dots.hide().empty();
      $next.hide();
      $prev.hide();
      return;
    }

    $thumbWrapper.html(self.buildThumbSlides(images));
    $mainWrapper.html(self.buildMainSlides(images));

    if (images.length > 1) {
      $dots.show().empty();
      $next.show();
      $prev.show();
    } else {
      $dots.hide().empty();
      $next.hide();
      $prev.hide();
    }

    self.initGallery();
  };

  ProductSingle.prototype.changeColor = function (colorIndex) {
    if (!this.galleryData[colorIndex]) return;

    this.currentColorIndex = colorIndex;
    this.currentImageIndex = 0;

    var colorName = this.galleryData[colorIndex].label || "";
    this.$root.find(".js-color-name").text(colorName);
    this.$root.find(".js-selected-color").val(colorName);
    this.$root.find(".js-sticky-color").val(String(colorIndex));
    this.$root.find(".js-color-swatch").removeClass("is-active");
    this.$root
      .find('.js-color-swatch[data-color-index="' + colorIndex + '"]')
      .addClass("is-active");

    this.renderGallery();
    this.updateStickyImage(0);
  };

  ProductSingle.prototype.changeSize = function (size) {
    this.$root.find(".js-size-item").removeClass("is-active");
    this.$root
      .find('.js-size-item[data-size="' + size + '"]')
      .addClass("is-active");
    this.$root.find(".js-size-name").text(size);
    this.$root.find(".js-selected-size").val(size);
    this.$root.find(".js-sticky-size").val(size);
  };

  ProductSingle.prototype.setQuantity = function (quantity) {
    this.quantity = quantity < 1 ? 1 : quantity;
    this.$root.find(".js-qty-value").text(this.quantity);
    this.$root.find(".js-selected-quantity").val(this.quantity);
  };

  ProductSingle.prototype.syncAll = function () {
    var firstColorName = this.galleryData[0]
      ? this.galleryData[0].label || ""
      : "";
    var firstSize = this.$root.find(".js-size-item").first().data("size") || "";

    if (firstColorName) {
      this.$root.find(".js-color-name").text(firstColorName);
      this.$root.find(".js-selected-color").val(firstColorName);
      this.$root.find(".js-sticky-color").val("0");
    }

    if (firstSize) {
      this.changeSize(firstSize);
    }

    this.setQuantity(1);
  };

  ProductSingle.prototype.updateStickyImage = function (index) {
    var images = this.getCurrentImages();
    var image = images[index] || images[0] || null;
    var $stickyImage = this.$root.find(".js-sticky-image");

    if (!$stickyImage.length || !image) return;

    $stickyImage.attr("src", image.thumb || image.full || "");
  };

  ProductSingle.prototype.saveRecentlyViewed = function () {
    if (!this.productId) return;

    var cookieName = "vs_recent_products";
    var maxItems = 5;
    var raw = "";
    var match = document.cookie.match(
      new RegExp("(?:^|; )" + cookieName + "=([^;]*)"),
    );

    if (match) {
      raw = decodeURIComponent(match[1] || "");
    }

    var ids = raw ? raw.split(",") : [];
    ids = ids.filter(
      function (item) {
        return item && item !== String(this.productId);
      }.bind(this),
    );

    ids.unshift(String(this.productId));
    ids = ids.slice(0, maxItems);

    document.cookie =
      cookieName +
      "=" +
      encodeURIComponent(ids.join(",")) +
      "; path=/; max-age=" +
      60 * 60 * 24 * 30 +
      "; SameSite=Lax";
  };

  $(function () {
    $(".js-vs-single-product").each(function () {
      new ProductSingle($(this));
    });
  });
})(jQuery);
