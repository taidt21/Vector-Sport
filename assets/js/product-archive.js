(function ($) {
  'use strict';

  function initProductArchive() {
    $('.js-product-archive').each(function () {
      var $root = $(this);
      var $filter = $root.find('.js-product-filter');
      var $form = $root.find('.js-product-filter-form');
      var $grid = $root.find('.js-product-grid');
      var $results = $root.find('.js-product-results');
      var $paginationWrap = $root.find('.js-product-pagination-wrap');
      var $summary = $root.find('.js-product-summary');
      var $openBtn = $root.find('.js-product-filter-open');
      var ajaxUrl = $root.data('ajax-url');
      var nonce = $root.data('nonce');
      var postType = $root.data('post-type');
      var categoryTaxonomy = $root.data('category-taxonomy') || '';
      var currentCategoryId = parseInt($root.data('current-category-id') || 0, 10);
      var archiveUrl = $root.data('archive-url') || window.location.pathname;
      var requestXhr = null;

      function openFilter() {
        $filter.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('product-filter-open');
        $openBtn.attr('aria-expanded', 'true');
      }

      function closeFilter() {
        $filter.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('product-filter-open');
        $openBtn.attr('aria-expanded', 'false');
      }

      function getSelectedValues(name) {
        var values = [];
        $form.find('input[name="' + name + '"]:checked').each(function () {
          values.push($(this).val());
        });
        return values;
      }

      function syncFormFromUrl() {
        var params = new URLSearchParams(window.location.search);
        var colors = params.getAll('colors[]');
        var prices = params.getAll('prices[]');
        var search = params.get('s') || '';

        $form.find('input[type="checkbox"]').prop('checked', false);
        $form.find('input[name="s"]').val(search);

        colors.forEach(function (value) {
          $form.find('input[name="colors[]"][value="' + value.replace(/"/g, '\\"') + '"]').prop('checked', true);
        });

        prices.forEach(function (value) {
          $form.find('input[name="prices[]"][value="' + value.replace(/"/g, '\\"') + '"]').prop('checked', true);
        });
      }

      function buildUrl(page) {
        var url = new URL(archiveUrl, window.location.origin);
        var searchValue = ($form.find('input[name="s"]').val() || '').trim();

        getSelectedValues('colors[]').forEach(function (value) {
          url.searchParams.append('colors[]', value);
        });

        getSelectedValues('prices[]').forEach(function (value) {
          url.searchParams.append('prices[]', value);
        });

        if (searchValue) {
          url.searchParams.set('s', searchValue);
        }

        if (page && page > 1) {
          url.searchParams.set('paged', page);
        }

        return url;
      }

      function fetchProducts(page, shouldUpdateHistory) {
        page = page || 1;

        if (requestXhr && requestXhr.readyState !== 4) {
          requestXhr.abort();
        }

        $results.addClass('is-loading');

        requestXhr = $.ajax({
          url: ajaxUrl,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'vs_filter_products',
            nonce: nonce,
            post_type: postType,
            category_taxonomy: categoryTaxonomy,
            current_category_id: currentCategoryId,
            s: ($form.find('input[name="s"]').val() || '').trim(),
            colors: getSelectedValues('colors[]'),
            prices: getSelectedValues('prices[]'),
            paged: page
          }
        });

        requestXhr.done(function (response) {
          if (!response || !response.success || !response.data) {
            return;
          }

          $grid.html(response.data.grid_html || '');
          $paginationWrap.html(response.data.pagination_html || '');
          $summary.text((response.data.found_posts || 0) + ' sản phẩm');

          if (shouldUpdateHistory !== false) {
            window.history.replaceState(
              {
                colors: getSelectedValues('colors[]'),
                prices: getSelectedValues('prices[]'),
                page: page
              },
              '',
              buildUrl(page)
            );
          }
        }).always(function () {
          $results.removeClass('is-loading');
        });
      }

      $root.on('click', '.js-product-filter-open', function () {
        openFilter();
      });

      $root.on('click', '.js-product-filter-close', function () {
        closeFilter();
      });

      $root.on('click', '.js-product-filter-more', function () {
        var $button = $(this);
        var $section = $button.closest('.product_filter__section');
        var isExpanded = $section.toggleClass('is-expanded').hasClass('is-expanded');
        $button.text(isExpanded ? 'Thu gọn' : 'Xem thêm');
      });

      $form.on('change', 'input[type="checkbox"]', function () {
        fetchProducts(1, true);
      });

      $root.on('click', '.js-product-filter-reset', function () {
        $form.find('input[type="checkbox"]').prop('checked', false);
        fetchProducts(1, true);
      });
      $root.on('click', '.js-product-card-color', function (e) {
        
        e.preventDefault();

        var $btn = $(this);
        var $card = $btn.closest('.product_card');
        var $img = $card.find('.js-product-card-image');
        var nextImage = $btn.attr('data-image') || $img.attr('data-default-image');

        $btn
          .addClass('is-active')
          .attr('aria-pressed', 'true')
          .siblings('.js-product-card-color')
          .removeClass('is-active')
          .attr('aria-pressed', 'false');

        if (nextImage) {
          $img.attr('src', nextImage);
        }
      });

      $(document).on('keydown.productArchiveSimple', function (e) {
        if (e.key === 'Escape') {
          closeFilter();
        }
      });

      $(window).on('popstate.productArchiveSimple', function () {
        syncFormFromUrl();
        var params = new URLSearchParams(window.location.search);
        var page = parseInt(params.get('paged') || '1', 10);
        fetchProducts(page, false);
      });

      if (window.innerWidth >= 992) {
        $filter.attr('aria-hidden', 'false');
      }

      syncFormFromUrl();
    });
  }

  $(initProductArchive);
})(jQuery);
