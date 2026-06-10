/**
 * HKT Fashion — Homepage Enhancements JS
 *
 * Features:
 *  1. Sticky Header Shrink on Scroll
 *  2. Ajax Live Search Dropdown (3 chars threshold)
 *  3. Bento Voucher Copy Button
 */
(function ($) {
    'use strict';

    /* ----------------------------------------------------------------
     * 1. STICKY HEADER SHRINK
     * ---------------------------------------------------------------- */
    var $header = $('#header');
    var scrollThreshold = 80;

    function handleHeaderScroll() {
        if ($(window).scrollTop() > scrollThreshold) {
            $header.addClass('header-shrunk');
        } else {
            $header.removeClass('header-shrunk');
        }
    }

    $(window).on('scroll.hktHeader', handleHeaderScroll);
    handleHeaderScroll(); // run once on load


    /* ----------------------------------------------------------------
     * 2. AJAX LIVE SEARCH DROPDOWN
     * ---------------------------------------------------------------- */
    var $searchInputs = $('.header-bottom .search-field, .header-bottom input[type="search"]');
    var searchTimer;
    var minChars = 3;

    // Inject dropdown container after each search input wrapper
    $searchInputs.each(function () {
        var $wrapper = $(this).closest('.searchform-wrapper, .searchform, form');
        if ($wrapper.length && !$wrapper.find('.hkt-search-dropdown').length) {
            $wrapper.css('position', 'relative');
            $wrapper.append('<div class="hkt-search-dropdown" role="listbox" aria-label="Gợi ý tìm kiếm"></div>');
        }
    });

    function getDropdown($input) {
        return $input.closest('.searchform-wrapper, .searchform, form').find('.hkt-search-dropdown');
    }

    function closeAllDropdowns() {
        $('.hkt-search-dropdown').removeClass('open').empty();
    }

    function renderResults(results, $dropdown, query) {
        $dropdown.empty();

        if (!results || results.length === 0) {
            $dropdown.html('<div class="hkt-search-empty">Không tìm thấy sản phẩm nào cho "<strong>' + hktEscapeHtml(query) + '</strong>"</div>');
            $dropdown.addClass('open');
            return;
        }

        var html = '';
        $.each(results, function (i, item) {
            var imgTag = item.image
                ? '<img src="' + item.image + '" alt="' + hktEscapeHtml(item.title) + '" class="hkt-search-result-img" loading="lazy">'
                : '<div class="hkt-search-result-img" style="background:#f5f5f5;"></div>';

            html += '<a href="' + item.url + '" class="hkt-search-result-item" role="option">' +
                imgTag +
                '<div class="hkt-search-result-info">' +
                    '<span class="hkt-search-result-name">' + hktEscapeHtml(item.title) + '</span>' +
                    '<span class="hkt-search-result-price">' + item.price + '</span>' +
                '</div>' +
            '</a>';
        });

        $dropdown.html(html).addClass('open');
    }

    function doSearch($input) {
        var query = $.trim($input.val());
        var $dropdown = getDropdown($input);

        if (query.length < minChars) {
            closeAllDropdowns();
            return;
        }

        $dropdown.html('<div class="hkt-search-loading">⏳ Đang tìm kiếm...</div>').addClass('open');

        $.ajax({
            url: hktHomepageData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'hkt_live_search',
                query: query,
                nonce: hktHomepageData.nonce
            },
            success: function (response) {
                if (response.success && response.data) {
                    renderResults(response.data, $dropdown, query);
                } else {
                    renderResults([], $dropdown, query);
                }
            },
            error: function () {
                $dropdown.empty().removeClass('open');
            }
        });
    }

    $searchInputs.on('input.hktSearch', function () {
        var $input = $(this);
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            doSearch($input);
        }, 280); // debounce 280ms
    });

    // Close on click outside
    $(document).on('click.hktSearch', function (e) {
        if (!$(e.target).closest('.searchform-wrapper, .searchform, .hkt-search-dropdown').length) {
            closeAllDropdowns();
        }
    });

    // Close on Escape
    $(document).on('keydown.hktSearch', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    // Prevent form submit when dropdown is open (let user click a result)
    $searchInputs.on('keydown.hktSearch', function (e) {
        if (e.key === 'Enter') {
            var $dropdown = getDropdown($(this));
            if ($dropdown.hasClass('open') && $dropdown.find('.hkt-search-result-item').length) {
                // Allow Enter to submit form naturally; dropdown will close
            }
        }
    });

    /* Helper: safe HTML escape */
    function hktEscapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }


    /* ----------------------------------------------------------------
     * 3. BENTO VOUCHER COPY BUTTON
     * ---------------------------------------------------------------- */
    $(document).on('click.hktCopy', '.hkt-bento-copy-btn', function () {
        var code = $(this).data('code');
        var $btn = $(this);

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                $btn.text('ĐÃ CHÉP ✓').addClass('copied');
                setTimeout(function () {
                    $btn.text('SAO CHÉP').removeClass('copied');
                }, 2500);
            });
        } else {
            // Fallback for older browsers
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(code).select();
            document.execCommand('copy');
            $temp.remove();
            $btn.text('ĐÃ CHÉP ✓').addClass('copied');
            setTimeout(function () {
                $btn.text('SAO CHÉP').removeClass('copied');
            }, 2500);
        }
    });


    /* ----------------------------------------------------------------
     * 4. PRODUCT IMAGE HOVER SWAP (Secondary Image)
     * Load secondary gallery image lazily into product card
     * ---------------------------------------------------------------- */
    function initImageSwap() {
        $('.product-small .box-image').each(function () {
            var $box = $(this);
            if ($box.data('swapInit')) return;
            $box.data('swapInit', true);

            // Check if there's a secondary image stored in data attr by PHP
            var secondarySrc = $box.closest('.product-small').data('secondary-image');
            if (secondarySrc) {
                var $secondaryImg = $('<img>', {
                    src: secondarySrc,
                    alt: '',
                    class: 'secondary-image',
                    loading: 'lazy',
                    'aria-hidden': 'true'
                });
                $box.append($secondaryImg);
            }
        });
    }

    // Run on page load and after any AJAX product loads (Flatsome sliders)
    initImageSwap();
    $(document).on('flatsome:after_ajax', initImageSwap);

}(jQuery));
