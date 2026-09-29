(function ($) {
    var addressCache = {};

    function ajaxRequest(action, data) {
        return $.post(vsShop.ajaxUrl, $.extend({
            action: action,
            nonce: vsShop.nonce
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
        $(".js-vs-cart-content").html(html || "");
    }

    function updateCartCount(count) {
        $(".js-cart-count").text(count || 0);
    }

    function getProductPayload($trigger) {
        var $root = $trigger.closest(".js-vs-single-product");
        if (!$root.length) return null;

        var $form = $root.find(".js-product-form").first();
        if (!$form.length) return null;

        return {
            product_id: $form.find('input[name="product_id"]').val() || "",
            color: $form.find(".js-selected-color").val() || "",
            size: $form.find(".js-selected-size").val() || "",
            qty: $form.find(".js-selected-quantity").val() || 1
        };
    }

    function getCheckoutForm() {
        return $(".js-vs-checkout-form").first();
    }

    function getFormStatusBox($form) {
        return $form.find(".js-checkout-form-status").first();
    }

    function setFormStatus($form, type, message) {
        var $status = getFormStatusBox($form);
        if (!$status.length) return;

        if (!message) {
            $status.removeClass("is-error is-success is-active").empty();
            return;
        }

        $status
            .removeClass("is-error is-success")
            .addClass("is-active " + (type === "success" ? "is-success" : "is-error"))
            .text(message);
    }

    function getFieldWrapper($field) {
        return $field.closest(".js-checkout-field");
    }

    function ensureFieldError($wrapper) {
        var $error = $wrapper.find(".vs-checkout-form__error").first();
        if (!$error.length) {
            $error = $('<div class="vs-checkout-form__error" aria-live="polite"></div>');
            $wrapper.append($error);
        }
        return $error;
    }

    function showFieldError($field, message) {
        var $wrapper = getFieldWrapper($field);
        if (!$wrapper.length) return;

        $wrapper.addClass("is-invalid");
        ensureFieldError($wrapper).text(message || "");
        $field.attr("aria-invalid", "true");
    }

    function clearFieldError($field) {
        var $wrapper = getFieldWrapper($field);
        if (!$wrapper.length) return;

        $wrapper.removeClass("is-invalid");
        $wrapper.find(".vs-checkout-form__error").remove();
        $field.removeAttr("aria-invalid");
    }

    function clearAllFieldErrors($form) {
        $form.find(".js-checkout-field").removeClass("is-invalid").find(".vs-checkout-form__error").remove();
        $form.find("[aria-invalid='true']").removeAttr("aria-invalid");
    }

    function scrollToField($field) {
        if (!$field || !$field.length) return;

        var top = getFieldWrapper($field).offset();
        if (!top) return;

        $("html, body").stop().animate({
            scrollTop: Math.max(top.top - 120, 0)
        }, 250);
    }

    function getTrimmedValue($form, name) {
        return $.trim($form.find('[name="' + name + '"]').val() || "");
    }

    function isCartEmptyResponse(count) {
        return parseInt(count, 10) <= 0;
    }

    function setCheckoutSubmitState(count) {
        var $form = getCheckoutForm();
        if (!$form.length) return;

        var isEmpty = isCartEmptyResponse(count);
        var $buttons = $form.find(".js-checkout-submit");
        $buttons.prop("disabled", isEmpty);
        $form.attr("data-cart-count", parseInt(count, 10) || 0);

        var $hint = $form.find(".vs-checkout-summary__hint");
        if (!$hint.length && isEmpty) {
            $form.find(".vs-checkout-summary-card").append('<p class="vs-checkout-summary__hint">Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng.</p>');
        } else if ($hint.length && !isEmpty) {
            $hint.remove();
        }
    }

    function updateCheckoutFromCartResponse(res) {
        if (!res || !res.success || !res.data) return;

        replaceDrawerHtml(res.data.html);
        updateCartCount(res.data.count);

        if ($(".js-vs-checkout").length) {
            $(".js-checkout-cart-list").html(res.data.checkout_cart_html || "");
            $(".js-checkout-subtotal").text(res.data.subtotal_html || "0đ");
            $(".js-checkout-grand-total, .js-checkout-mobile-total").text(res.data.grand_total_html || "0đ");
            setCheckoutSubmitState(res.data.count || 0);
        }
    }

    function syncEverything(openAfter) {
        return ajaxRequest("vs_cart_get").done(function (res) {
            if (!res || !res.success) return;
            updateCheckoutFromCartResponse(res);
            if (openAfter) openDrawer();
        });
    }

    function addressApiBase(mode) {
        var api = (window.vsShop && window.vsShop.addressApi) ? window.vsShop.addressApi : {};
        return mode === "old" ? (api.oldBase || "") : (api.newBase || "");
    }

    function normalizeAddressItems(list) {
        var items = Array.isArray(list) ? list : [];
        return $.map(items, function (item) {
            return {
                code: item.code || item.codename || item.id || "",
                name: item.name || item.full_name || item.fullName || ""
            };
        }).filter(function (item) {
            return item.code !== "" && item.name !== "";
        });
    }

    function setSelectOptions($select, items, placeholder) {
        var html = ['<option value="">' + placeholder + '</option>'];
        $.each(items || [], function (_, item) {
            html.push('<option value="' + item.code + '">' + item.name + '</option>');
        });
        $select.html(html.join(""));
        $select.prop("disabled", !items || !items.length);
    }

    function updateAddressHidden($form) {
        var mode = $form.find('[name="address_mode"]:checked').val() || "new";
        var provinceName = $form.find(".js-address-province option:selected").text() || "";
        var districtName = $form.find(".js-address-district option:selected").text() || "";
        var wardName = $form.find(".js-address-ward option:selected").text() || "";

        $form.find('[name="province"]').val($form.find(".js-address-province").val() ? provinceName : "");
        $form.find('[name="district"]').val(mode === "old" && $form.find(".js-address-district").val() ? districtName : "");
        $form.find('[name="ward"]').val($form.find(".js-address-ward").val() ? wardName : "");
    }

    function fetchAddressJson(url) {
        if (!url) {
            return $.Deferred().reject().promise();
        }

        if (addressCache[url]) {
            return $.Deferred().resolve(addressCache[url]).promise();
        }

        return $.ajax({
            url: url,
            method: "GET",
            dataType: "json"
        }).then(function (res) {
            addressCache[url] = res;
            return res;
        });
    }

    function toggleAddressModeUI($form, mode) {
        var isOld = mode === "old";
        var $districtField = $form.find(".js-address-district-field");
        var $districtSelect = $form.find(".js-address-district");
        var $districtLabel = $form.find(".js-address-district-label");
        var $wardLabel = $form.find(".js-address-ward-label");

        if (isOld) {
            $districtField.show();
            $districtSelect.prop("disabled", true);
            $districtLabel.text("Quận / Huyện");
            $wardLabel.text("Phường / Xã");
        } else {
            $districtField.hide();
            $districtSelect.val("").html('<option value="">Không áp dụng</option>').prop("disabled", true);
            $districtLabel.text("Không áp dụng");
            $wardLabel.text("Phường / Xã / Đặc khu");
            clearFieldError($districtSelect);
        }
    }

    function loadProvinces($form) {
        var mode = $form.find('[name="address_mode"]:checked').val() || "new";
        var base = addressApiBase(mode);
        var $province = $form.find(".js-address-province");
        var $district = $form.find(".js-address-district");
        var $ward = $form.find(".js-address-ward");

        toggleAddressModeUI($form, mode);
        setSelectOptions($province, [], "Đang tải Tỉnh / Thành phố...");
        setSelectOptions($district, [], mode === "old" ? "Chọn Quận / Huyện" : "Không áp dụng");
        setSelectOptions($ward, [], mode === "old" ? "Chọn Phường / Xã" : "Chọn Phường / Xã / Đặc khu");
        updateAddressHidden($form);

        if (!base) {
            setSelectOptions($province, [], "Chưa cấu hình API địa chỉ");
            return;
        }

        fetchAddressJson(base)
            .done(function (res) {
                setSelectOptions($province, normalizeAddressItems(res), "Chọn Tỉnh / Thành phố");
            })
            .fail(function () {
                setSelectOptions($province, [], "Không tải được dữ liệu Tỉnh / Thành phố");
            });
    }

    function loadDistricts($form, provinceCode) {
        var base = addressApiBase("old");
        var $district = $form.find(".js-address-district");
        var $ward = $form.find(".js-address-ward");

        setSelectOptions($district, [], provinceCode ? "Đang tải Quận / Huyện..." : "Chọn Quận / Huyện");
        setSelectOptions($ward, [], "Chọn Phường / Xã");
        updateAddressHidden($form);

        if (!provinceCode || !base) {
            return;
        }

        fetchAddressJson(base + "p/" + provinceCode + "?depth=2")
            .done(function (res) {
                setSelectOptions($district, normalizeAddressItems(res.districts || []), "Chọn Quận / Huyện");
            })
            .fail(function () {
                setSelectOptions($district, [], "Không tải được Quận / Huyện");
            });
    }

    function loadWardsByDistrict($form, districtCode) {
        var base = addressApiBase("old");
        var $ward = $form.find(".js-address-ward");

        setSelectOptions($ward, [], districtCode ? "Đang tải Phường / Xã..." : "Chọn Phường / Xã");
        updateAddressHidden($form);

        if (!districtCode || !base) {
            return;
        }

        fetchAddressJson(base + "d/" + districtCode + "?depth=2")
            .done(function (res) {
                setSelectOptions($ward, normalizeAddressItems(res.wards || []), "Chọn Phường / Xã");
            })
            .fail(function () {
                setSelectOptions($ward, [], "Không tải được Phường / Xã");
            });
    }

    function loadWardsByProvince($form, provinceCode) {
        var base = addressApiBase("new");
        var $ward = $form.find(".js-address-ward");

        setSelectOptions($ward, [], provinceCode ? "Đang tải Phường / Xã / Đặc khu..." : "Chọn Phường / Xã / Đặc khu");
        updateAddressHidden($form);

        if (!provinceCode || !base) {
            return;
        }

        fetchAddressJson(base + "w/?province=" + encodeURIComponent(provinceCode))
            .done(function (res) {
                setSelectOptions($ward, normalizeAddressItems(res), "Chọn Phường / Xã / Đặc khu");
            })
            .fail(function () {
                setSelectOptions($ward, [], "Không tải được Phường / Xã / Đặc khu");
            });
    }

    function validateEmail(value) {
        if (!value) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function normalizePhone(value) {
        return (value || "").replace(/\s+/g, "").replace(/[^0-9+]/g, "");
    }

    function validateCheckoutField($field, $form) {
        var name = $field.attr("name") || "";
        var value = $.trim($field.val() || "");
        var mode = $form.find('[name="address_mode"]:checked').val() || "new";
        var label = $field.data("label") || "Trường thông tin";

        clearFieldError($field);

        if (name === "customer_name") {
            if (value.length < 2) {
                showFieldError($field, "Vui lòng nhập họ và tên đầy đủ.");
                return false;
            }
            return true;
        }

        if (name === "customer_phone") {
            var phone = normalizePhone(value);
            if (!phone) {
                showFieldError($field, "Vui lòng nhập số điện thoại.");
                return false;
            }
            if (!/^(0|\+?84)(3|5|7|8|9)\d{8}$/.test(phone)) {
                showFieldError($field, "Số điện thoại chưa đúng định dạng.");
                return false;
            }
            $field.val(phone);
            return true;
        }

        if (name === "customer_email") {
            if (!validateEmail(value)) {
                showFieldError($field, "Email chưa đúng định dạng.");
                return false;
            }
            return true;
        }

        if (name === "province_code") {
            if (!value) {
                showFieldError($field, "Vui lòng chọn " + label.toLowerCase() + ".");
                return false;
            }
            return true;
        }

        if (name === "district_code") {
            if (mode === "old" && !value) {
                showFieldError($field, "Vui lòng chọn quận / huyện.");
                return false;
            }
            return true;
        }

        if (name === "ward_code") {
            if (!value) {
                showFieldError($field, "Vui lòng chọn " + label.toLowerCase() + ".");
                return false;
            }
            return true;
        }

        if (name === "address_line") {
            if (value.length < 5) {
                showFieldError($field, "Vui lòng nhập địa chỉ cụ thể rõ hơn.");
                return false;
            }
            return true;
        }

        return true;
    }

    function validateCheckoutForm($form) {
        var selectors = [
            '[name="customer_name"]',
            '[name="customer_phone"]',
            '[name="customer_email"]',
            '[name="province_code"]',
            '[name="ward_code"]',
            '[name="address_line"]'
        ];
        var mode = $form.find('[name="address_mode"]:checked').val() || "new";
        if (mode === "old") {
            selectors.splice(4, 0, '[name="district_code"]');
        }

        var firstInvalid = null;
        var isValid = true;

        clearAllFieldErrors($form);
        setFormStatus($form, "", "");
        updateAddressHidden($form);

        $.each(selectors, function (_, selector) {
            var $field = $form.find(selector).first();
            if (!$field.length) return;

            if (!validateCheckoutField($field, $form)) {
                if (!firstInvalid) firstInvalid = $field;
                isValid = false;
            }
        });

        if (!isValid) {
            setFormStatus($form, "error", "Vui lòng kiểm tra lại thông tin giao hàng.");
            scrollToField(firstInvalid);
        }

        return isValid;
    }

    function collectCheckoutPayload($form) {
        return {
            customer_name: getTrimmedValue($form, "customer_name"),
            customer_phone: normalizePhone(getTrimmedValue($form, "customer_phone")),
            customer_email: getTrimmedValue($form, "customer_email"),
            address_mode: $form.find('[name="address_mode"]:checked').val() || "new",
            province_code: $form.find('[name="province_code"]').val() || "",
            district_code: $form.find('[name="district_code"]').val() || "",
            ward_code: $form.find('[name="ward_code"]').val() || "",
            province: $form.find('[name="province"]').val() || "",
            district: $form.find('[name="district"]').val() || "",
            ward: $form.find('[name="ward"]').val() || "",
            address_line: getTrimmedValue($form, "address_line"),
            note: $.trim($form.find('[name="note"]').val() || ""),
            shipping_method: $form.find('[name="shipping_method"]:checked').val() || "standard",
            payment_method: $form.find('[name="payment_method"]:checked').val() || "cod",
            payment_bank_code: $form.find('[name="payment_bank_code"]').val() || "",
            coupon_code: getTrimmedValue($form, "coupon_code")
        };
    }

    function initCheckoutAddress() {
        var $form = getCheckoutForm();
        if (!$form.length) return;

        loadProvinces($form);

        $(document).on("change", ".js-address-mode-group input[type='radio']", function () {
            var $group = $(this).closest(".js-address-mode-group");
            $group.find(".vs-checkout-address-mode__item").removeClass("is-active");
            $(this).closest(".vs-checkout-address-mode__item").addClass("is-active");
            loadProvinces($form);
            setFormStatus($form, "", "");
        });

        $(document).on("change", ".js-address-province", function () {
            var mode = $form.find('[name="address_mode"]:checked').val() || "new";
            var provinceCode = $(this).val() || "";
            clearFieldError($(this));
            updateAddressHidden($form);

            if (mode === "old") {
                loadDistricts($form, provinceCode);
            } else {
                loadWardsByProvince($form, provinceCode);
            }
        });

        $(document).on("change", ".js-address-district", function () {
            clearFieldError($(this));
            updateAddressHidden($form);
            loadWardsByDistrict($form, $(this).val() || "");
        });

        $(document).on("change", ".js-address-ward", function () {
            clearFieldError($(this));
            updateAddressHidden($form);
        });
    }

    $(document).on("click", ".js-vs-cart-open", function (e) {
        e.preventDefault();
        syncEverything(true);
    });

    $(document).on("click", ".js-vs-cart-close, .js-vs-cart-backdrop", function () {
        closeDrawer();
    });

    $(document).on("click", ".vs-sp__add-cart, .vs-sp-sticky__button", function (e) {
        e.preventDefault();
        var payload = getProductPayload($(this));
        if (!payload || !payload.product_id) return;

        ajaxRequest("vs_cart_add", payload).done(function (res) {
            if (!res || !res.success) {
                alert(res && res.data && res.data.message ? res.data.message : "Có lỗi xảy ra.");
                return;
            }
            replaceDrawerHtml(res.data.html);
            updateCartCount(res.data.count);
            openDrawer();
        });
    });

    $(document).on("click", ".vs-sp__buy-now", function (e) {
        e.preventDefault();
        var $button = $(this);
        var payload = getProductPayload($button);
        if (!payload || !payload.product_id || $button.prop("disabled")) return;

        $button.prop("disabled", true);
        ajaxRequest("vs_cart_add", payload).done(function (res) {
            if (!res || !res.success) {
                alert(res && res.data && res.data.message ? res.data.message : "Có lỗi xảy ra.");
                return;
            }
            window.location.href = vsShop.checkoutUrl;
        }).always(function () {
            $button.prop("disabled", false);
        });
    });

    $(document).on("click", ".js-vs-cart-remove", function () {
        var key = $(this).data("key");
        if (!key) return;
        ajaxRequest("vs_cart_remove", { key: key }).done(function () {
            syncEverything(false);
        });
    });

    $(document).on("click", ".js-vs-cart-qty-minus, .js-vs-cart-qty-plus", function () {
        var $item = $(this).closest(".vs-cart-item");
        var key = $(this).data("key");
        var currentQty = parseInt($item.find(".vs-cart-item__qty-value").text(), 10) || 1;
        var nextQty = $(this).hasClass("js-vs-cart-qty-plus") ? currentQty + 1 : currentQty - 1;
        ajaxRequest("vs_cart_update_qty", { key: key, qty: nextQty }).done(function () {
            syncEverything(false);
        });
    });

    $(document).on("click", ".js-checkout-remove", function () {
        var key = $(this).data("key");
        if (!key) return;
        ajaxRequest("vs_cart_remove", { key: key }).done(function () {
            syncEverything(false);
        });
    });

    $(document).on("click", ".js-checkout-qty-minus, .js-checkout-qty-plus", function () {
        var $item = $(this).closest(".vs-checkout-cart-item");
        var key = $(this).data("key");
        var currentQty = parseInt($item.find(".vs-checkout-cart-item__qty-value").text(), 10) || 1;
        var nextQty = $(this).hasClass("js-checkout-qty-plus") ? currentQty + 1 : currentQty - 1;
        ajaxRequest("vs_cart_update_qty", { key: key, qty: nextQty }).done(function () {
            syncEverything(false);
        });
    });

    $(document).on("change", ".js-payment-group input[type='radio'], .js-shipping-group input[type='radio']", function () {
        var $group = $(this).closest(".js-payment-group, .js-shipping-group");
        $group.find(".vs-checkout-payment__item").removeClass("is-active");
        $(this).closest(".vs-checkout-payment__item").addClass("is-active");
    });

    $(document).on("input blur change", ".js-vs-checkout-form [name='customer_name'], .js-vs-checkout-form [name='customer_phone'], .js-vs-checkout-form [name='customer_email'], .js-vs-checkout-form [name='address_line']", function () {
        var $form = getCheckoutForm();
        if (!$form.length) return;
        validateCheckoutField($(this), $form);
    });

    $(document).on("change", ".js-vs-checkout-form select", function () {
        var $form = getCheckoutForm();
        if (!$form.length) return;
        validateCheckoutField($(this), $form);
    });

    $(document).on("submit", ".js-vs-checkout-form", function (e) {
        e.preventDefault();

        var $form = $(this);
        var cartCount = parseInt($form.attr("data-cart-count") || $form.data("initial-cart-count") || 0, 10);
        if (isCartEmptyResponse(cartCount)) {
            setFormStatus($form, "error", "Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi đặt hàng.");
            return;
        }

        if (!validateCheckoutForm($form)) {
            return;
        }

        var payload = collectCheckoutPayload($form);
        var $btns = $form.find(".js-checkout-submit");

        $btns.prop("disabled", true).addClass("is-loading");
        setFormStatus($form, "", "");

        ajaxRequest("vs_checkout_place_order", payload)
            .done(function (res) {
                if (!res || !res.success || !res.data || !res.data.redirect_url) {
                    setFormStatus($form, "error", res && res.data && res.data.message ? res.data.message : "Không thể đặt hàng.");
                    return;
                }
                window.location.href = res.data.redirect_url;
            })
            .fail(function (xhr) {
                var message = "Không thể đặt hàng.";
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                setFormStatus($form, "error", message);
            })
            .always(function () {
                setCheckoutSubmitState(parseInt($form.attr("data-cart-count") || cartCount, 10));
                $btns.removeClass("is-loading");
            });
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape") closeDrawer();
    });

    $(function () {
        var $form = getCheckoutForm();
        if ($form.length) {
            setCheckoutSubmitState(parseInt($form.data("initial-cart-count") || 0, 10));
        }
        initCheckoutAddress();
    });
})(jQuery);
