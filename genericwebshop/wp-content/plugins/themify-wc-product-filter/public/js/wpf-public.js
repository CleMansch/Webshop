(function ($) {
    'use strict';
    var in_scroll = false;
    function triggerEvent(a, b) {
        var c;
        document.createEvent ? (c = document.createEvent("HTMLEvents"), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
    }
    var InitSlider = function () {
        if ($.fn.slider && $('.wpf_slider').length > 0) {
            $('.wpf_slider').each(function () {
                var $wrap = $(this).closest('.wpf_item'),
                        $min = $wrap.find('.wpf_price_from'),
                        $max = $wrap.find('.wpf_price_to'),
                        $min_val = parseInt($(this).data('min')),
                        $max_val = parseInt($(this).data('max')),
                        step = parseFloat($(this).data('step')),
                        $price_format = $wrap.find('.wpf_price_format').html(),
                        $form = $wrap.closest('form'),
                        $v1 = parseInt($min.val()),
                        $v2 = parseInt($max.val());
                // check for valid numbers in data-min and data-max
                if ($min_val === parseInt($min_val, 10) && $max_val === parseInt($max_val, 10)) {
                    $v1 = $v1 ? $v1 : $min_val;
                    $v2 = $v2 ? $v2 : $max_val;
                    $(this).slider({
                        range: true,
                        min: $min_val,
                        step: isNaN(step) || step <= 0 ? 1 : step,
                        max: $max_val,
                        values: [$v1, $v2],
                        slide: function (event, ui) {
                            $(ui.handle).find('.wpf_tooltip_amount').html(ui.value);
                        },
                        stop: function (event, ui) {
                            $min.val(ui.values[ 0 ]);
                            $max.val(ui.values[ 1 ]);
                            if ($form.hasClass('wpf_submit_on_change')) {
                                $form.submit();
                            }
                        },
                        create: function (event, ui) {
                            $min.val($v1);
                            $max.val($v2);
                            var tooltip = '<span class="wpf-slider-tooltip"><span class="wpf-slider-tooltip-inner">' + $price_format.replace('0', '<span class="wpf_tooltip_amount">' + $v1 + '</span>') + '</span><span class="wpf-slider-tooltip-arrow"></span></span>',
                                    $slider = $(this).children('.ui-slider-handle');
                            $slider.first().html(tooltip);
                            $slider.last().html(tooltip.replace($v1, $v2));
                        }
                    });
                    $(this).slider().bind('slidechange',function(event,ui){
                        $(ui.handle).find('.wpf_tooltip_amount').html(ui.value);
                    });
                }
            });
        }
    };

    var InitGroupToggle = function () {
        $('body').on( 'click','.wpf_items_grouped:not(.wpf_layout_horizontal) .wpf_item_name', function (e) {

            var $wrap = $(this).next('.wpf_items_group'),
                    $this = $(this);
            if ($this.closest('.wpf_item_onsale,.wpf_item_instock').length > 0) {
                return true;
            }
            e.preventDefault();
            if ($wrap.is(':visible')) {
                $wrap.slideUp(function () {
                    $this.addClass('wpf_grouped_close');
                });
            }
            else {
                $wrap.slideDown(function () {
                    $this.removeClass('wpf_grouped_close');
                });
            }
        });
		$('.wpf_items_grouped:not(.wpf_layout_horizontal) .wpf_item_name').trigger('click');
    };

    var InitSubmit = function () {
        var masonryData, isMasonry;

        $('body').on( 'submit','.wpf_form', function (e) {
            e.preventDefault();
            var $form = $(this),
                    $container = $('.wpf-search-container').first(),
                    data = $form.serializeArray(),
                    result = {};
            if ($container.length === 0) {
                $container = $('.wc-products.loops-wrapper');
                if ($container.length === 0) {
                    $container = $('.woocommerce ul.products').parent();
                    if ($container.length === 0) {
                        $container = $('.post').first();
                    }
                }
            }
            if($container.length>0 && !$container.hasClass('wpf-search-container')){
                $container.addClass('wpf-search-container');
            }
            for (var i in data) {
                if ($.trim(data[i].value)) {
                    var name = data[i].name.replace('[]', '');
					
                    if (!result[name]) {
                        result[name] = data[i].value;
                    }
                    else {
                        result[name] += ',' + data[i].value;
                    }
                }
            }
            if (in_scroll) {
               result['append'] = 1;
            }
            $form.find('input[name="wpf_page"]').val('');
            if (!$form.hasClass('wpf_form_ajax')) {
                for (var $name in result) {
                    var input = $form.find('input[name="' + $name + '[]"]');
                    if (input.length > 0) {
                        input.prop({'disabled': true, 'name': ''}).filter(':checked').prop({'disabled': false, 'name': $name}).val(result[$name]);
                    }
                }
                $('body').off('submit','.wpf_form');
                $form.submit();
                return false;
            }

            // Save isotope data if masonry is enabled
            masonryData = masonryData || $( '.products', $container ).data( 'isotope' );
            isMasonry = isMasonry || typeof masonryData === 'object' && 'options' in masonryData;

            $.ajax({
                url: $form.prop('action'),
                type: 'POST',
                data: $.param(result),
                beforeSend: function () {
                    $form.addClass('wpf-search-submit');
                    $container.addClass('wpf-container-wait');
                },
                complete: function () {
                    $form.removeClass('wpf-search-submit');
                    $container.removeClass('wpf-container-wait');
                },
                success: function (resp) {
                    if (resp) {
                        var scrollTo = $container,
                            products=null,
                            containerClass = $('.products', $container).attr('class'),
                            $resp = $('<div>'+resp+'</div>');//Wrap the resp string to make sure the .find command works
                        $container.data('slug', $form.data('slug'));
                        $.event.trigger('wpf_ajax_before_replace');
                        if (in_scroll){
                            products = $resp.find('.product');
                            products.addClass('wpf_transient_product');
                            $('.products',$container).first().append(products);
                            var scroll = $resp.find('.wpf_infinity a');

                            if(scroll.length > 0){
                                $('.wpf_infinity a',$container).data({
                                    current:scroll.data('current'),
                                    max:scroll.data('max')
                                });
                            }

                            $container.removeClass('wpf-infnitiy-scroll');
                            scrollTo = products.first();
                            delete result['append'];
                            setTimeout(function(){
                                in_scroll = false;
                            },200);
                        }else if($('body').hasClass('woocommerce-page') || $container.find('.product')[0].nodeName !== 'LI'){ // Replace All HTML content if page is shop or product HTML tag type is not li
                            $('.woocommerce-ordering,.woocommerce-result-count,.woocommerce-pagination,.woocommerce-notices-wrapper,#load-more').remove();
                            if($resp.find('.page-title').length>0){
                                $('.page-title').remove();
                            }
                            $container.html(resp);
                            InitPagination();
                            InitOrder();
                        }else{ // Otherwise just find the products from result and append them
                            products = $resp.find( '.product' );
							var $productsContainer = $container.hasClass( 'products' ) ? $container : $( '.products', $container );
							$productsContainer.empty().append( products );
                            var els = ['.woocommerce-ordering','.woocommerce-result-count','.woocommerce-notices-wrapper','#load-more','.wpf-pagination'],
                                addPagination = false;
                            for(var i = els.length-1;i>=0;i--){
                                var $item = $resp.find(els[i]).first();
                                if($item.length && $resp.find('.wpf-pagination '+els[i]).length===0){
                                    var $rep = $container.find(els[i]);
                                    if($rep.length){
                                        $rep.replaceWith( $item );
                                    }else if('.wpf-pagination'===els[i]){
                                        $container.find('.woocommerce-pagination').remove();
                                        addPagination = true;
                                    }
                                }else{
                                    $(els[i]).remove();
                                }
                            }
                            if(addPagination){
                                $container.append($resp.find('.wpf-pagination'));
                            }
							InitPagination();
                            InitOrder();
                        }
                        
                        if( isMasonry && $.fn.isotope) {
                            var productsContainer = $( '.products', $container );
                            if( ! productsContainer.find( '.grid-sizer, .gutter-sizer' ).length ) {
                                        productsContainer.prepend('<div class="grid-sizer"></div><div class="gutter-sizer"></div>');
                                }
                            productsContainer.imagesLoaded().always( function ( instance ) {
                                var p = $( instance.elements[0] );
                                p.addClass( containerClass + ' masonry-done' );
                                if ( products !== null ) {
                                    p.isotope( 'destroy' );
                                    products.addClass( 'wpf_transient_end_product' );
                                }
                                if ( $form.hasClass( 'wpf_form_scroll' ) ) {
                                    ToScroll( scrollTo );
                                }
								p.isotope( masonryData.options );
                            } );
                        }
                        else if ($form.hasClass('wpf_form_scroll')) {
                            ToScroll(scrollTo);
                            if(products!==null){
                                    products.addClass('wpf_transient_end_product');
                            }
							
                        }
                        history.replaceState({}, null, '?' + decodeURIComponent($.param(result)));
                        if (typeof window.wp.mediaelement != 'undefined') {
                            window.wp.mediaelement.initialize();
                        }
                        $.event.trigger('wpf_ajax_success');
                        triggerEvent(window, 'resize');
                    }
                }
            });
        });
    };

    var ToScroll = function ($container) {
        if ($container.length > 0) {
            $('html,body').animate({
                scrollTop: $container.offset().top - $('#wpadminbar').outerHeight(true) - 10
            }, 1000);
        }
    };

    var infinity = function (e, click) {
        if (!in_scroll && (click || ($(this).scrollTop() + $(this).height() + 100) > $(document).height())) {
            var container = $('.wpf-search-container'),
                    scroll = $('.wpf_infinity a', container),
                    $form = $('.wpf_form_' + container.data('slug'));
            if ($form.length > 0) {
                var current = scroll.data('current');
                if (current <= scroll.data('max')) {
                    $form.find('input[name="wpf_page"]').val(current);
                    in_scroll = true;
                    if (!click) {
                        container.addClass('wpf-infnitiy-scroll');
                    }
                    $form.submit();
                    if (((current + 1) > scroll.data('max'))) {
                        $('.wpf_infinity').remove();
                        if (!click) {
                            $(this).off('scroll', infinity);
                        }
                    }
                }
            }
        }
    };


    var InitPagination = function () {
        function find_page_number(element) {
            var $page = parseInt(element.text());
            if ( ! $page ) {
                $page = parseInt(element.closest('.woocommerce-pagination,.pagenav').find('.current').text());
                if (element.hasClass('next')) {
                    ++$page;
                } else {
                    --$page;
                }
                var pattern = new RegExp( '(?<=paged=)[^\b\s\=]+' );
                if( ! $page && pattern.test( element.attr( 'href' ) ) ) {
                        $page = element.attr( 'href' ).match( pattern )[0];
                }
            }

            return $page;
        }
        if ($('.wpf_infinity_auto').length > 0) {
            $('#load-more').remove();
            $(window).off('scroll', infinity).on('scroll', infinity);
        }
        else if ($('.wpf_infinity').length > 0) {
            $('.wpf_infinity').closest('.wpf-hide-pagination').removeClass('wpf-hide-pagination');
            $('#load-more').remove();
            $('.wpf-search-container').off('click').on('click', '.wpf_infinity a', function (e) {
                e.preventDefault();
                e.stopPropagation();
                infinity(e, 1);
            });
        }
        else {
            $('.wpf-search-container').off('click').on('click', '.wpf-pagination a,.woocommerce-pagination a', function (e) {
                if("1" == new URL(window.location.href).searchParams.get("wpf")){
                    var $slug = $(this).closest('.wpf-search-container').data('slug'),
                        $form = $('.wpf_form_' + $slug);
                    if ($form.length > 0 && $form.find('input[name="wpf_page"]').length > 0) {
                        e.preventDefault();
                        $form.find('input[name="wpf_page"]').val(find_page_number($(this)));
                        $form.submit();
                    }
                }
            });
        }
    };

    var InitAutoComplete = function () {
        var cache = [];
        $('.wpf_autocomplete input').each(function () {
            var $this = $(this),
                    $key = $this.closest('.wpf_item_sku').length > 0 ? 'sku' : 'title',
                    $spinner = $this.next('.wpf-search-wait'),
                    $form = $this.closest('form'),
                    $submit = $form.hasClass('wpf_submit_on_change');
            cache[$key] = [];
            $(this).autocomplete({
                minLength: 0,
                classes: {
                    "ui-autocomplete": "highlight"
                },
                source: function (request, response) {
                    var term = $.trim(request.term);
                    if ($submit && term.length === 0 && request.term.length === 0) {
                        $form.submit();
                    }
                    if (term.length < 1) {
                        return;
                    }
                    request.term = term;
                    term = term.toLowerCase();
                    if (term in cache[$key]) {
                        response(cache[$key][ term ]);
                        return;
                    }
                    $spinner.show();
                    request.key = $key;
                    request.action = 'wpf_autocomplete';
                    $.post(
                            wpf.ajaxurl,
                            request,
                            function (data, status, xhr) {
                                $spinner.hide();
                                cache[$key][ term ] = data;
                                response(data);
                        },'json');

                },
                select: function (event, ui) {
                    $this.val(ui.item.value);
                    if ($submit) {
                        $form.submit();
                    }
                    return false;
                }
            })
            .focus(function () {
                if ($.trim($this.val()).length > 0) {
                    $(this).autocomplete("search");
                }

            })
            .autocomplete("widget").addClass("wpf_ui_autocomplete");
            ;
        });
    };

    var InitOrder = function () {
        function Order(val, obj) {
            var $slug = obj.closest('.wpf-search-container').data('slug'),
                    $form = $('.wpf_form_' + $slug);
            if ($form.length > 0 && $form.find('input[name="orderby"]').length > 0) {
                $form.find('input[name="orderby"]').val(val);
                $form.submit();
            }
        }
        $('.wpf-search-container').delegate('form.woocommerce-ordering', 'submit', function (e) {
            e.preventDefault();
            Order($(this).find('select').val(), $(this));

        }).delegate('select.orderby', 'change', function (e) {
            Order($(this).val(), $(this));
        });
        if (!$('.wpf-search-container').data('slug')) {
            $('.wpf-search-container').data('slug', $('.wpf_form').last().data('slug'));
        }
    };

    var InitChange = function () {
        if ($('.wpf_submit_on_change').length > 0) {
            $('.wpf_submit_on_change').each(function () {
                var $form = $(this);
                $form.find('input[type!="text"],select').change(function (e) {
                    if( $(this).attr("name") == 'price' && $(this).is(":checked") ) {
                        $(".wpf_price_range label").removeClass("active");
						$(this).next("label").addClass("active");
                    }
                    $form.submit();
                });

                $form.find('.wpf_pa_link').click(function (e) {
                    e.preventDefault();
                    $(this).find('input').prop('checked', true).trigger('change');
                });
            });
        }
    };

    var InitTabs = function () {
        var $horizontal = $('.wpf_layout_horizontal');
        if ($horizontal.length > 0) {
            InitTabsWidth($horizontal);
            $horizontal.find('.wpf_item:not(.wpf_item_onsale):not(.wpf_item_instock)').hover(
                function () {
                    $(this).children('.wpf_items_group').stop().fadeIn();
                },
                function () {
                    var $this = $(this);
                    if ($this.closest('.wpf-search-submit').length === 0) {
                        var hover = true;
                        $('.wpf_ui_autocomplete').each(function () {
                            if ($(this).is(':hover')) {
                                hover = false;
                                return false;
                            }
                        });
                        if (hover) {
                            $this.children('.wpf_items_group').stop().fadeOut();
                        }
                    }
                }
            );
            if(navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/)){
                $horizontal.find('.wpf_item:not(.wpf_item_onsale):not(.wpf_item_instock) .wpf_item_name').click(function(){
                    var $parent = $(this).parent(),
                        isVisible = $parent.children('.wpf_items_group').is(':visible'),
                        touched = $parent.hasClass('wpf_touched');
                    if(isVisible && !touched){
                        $parent.addClass('wpf_touch_tap');
                        $parent.trigger('mouseleave');
                    }else if(!isVisible && !touched){
                        $parent.removeClass('wpf_touch_tap');
                        $parent.trigger('mouseenter');
                        $parent.removeClass('wpf_touched');
                    }else if(touched){
                        $parent.removeClass('wpf_touched');
                    }
                });
            }
            var interval;
            $(window).resize(function (e) {
                if (!e.isTrigger) {
                    clearTimeout(interval);
                    interval = setTimeout(function () {
                        InitTabsWidth($horizontal);
                    }, 500);
                }

            });
        }
    };

    var InitTabsWidth = function ($groups) {
        $groups.each(function () {
            var $items = $(this).find('.wpf_items_group'),
                    $middle = Math.ceil($items.length / 2),
                    last = $items.last().closest('.wpf_item'),
                    max = last.offset().left;
            $items.each(function () {
                var p = $(this).closest('.wpf_item');
                if (max < p.offset().left) {
                    last = p;
                    max = p.offset().left;
                }
            });
            var $firstPos = $items.first().closest('.wpf_item').offset().left - 2,
                    $lastPos = max + last.outerWidth(true);
            last = null;
            max = null;
            $items.each(function (i) {
                var parent_item = $(this).closest('.wpf_item'),
                        left = parent_item.offset().left;
                if (i + 1 >= $middle) {
                    $(this).removeClass('wpf_left_tab').addClass('wpf_right_tab').outerWidth(Math.round(left + parent_item.width() - $firstPos));
                }
                else {
                    $(this).removeClass('wpf_right_tab').addClass('wpf_left_tab').outerWidth(Math.round($lastPos - left));
                }
            });

        });
    };
    
    var initSelect = function(){
       
        function clear(el,selected){
            var text = el.find('[value="'+selected+'"]').text();
            el.next('.select2').find('[title="'+text+'"]').addClass('wpf_disabled');
        }
        $('.wpf_form').find('select').each(function(){
            var el = $(this),
                is_multi = el.prop('multiple'),
                selected =  is_multi?el.data('selected'):false;
            el.select2({
                dir: wpf.rtl ? 'rtl' : 'ltr',
                minimumResultsForSearch: 10,
                dropdownCssClass: 'wpf_selectbox',
                allowClear: !selected && is_multi,
                placeholder:is_multi?'':false
            });

            if(selected && is_multi){
                clear(el,selected);
                el.on('change',function(e){
                    clear(el,selected);
                });
            }
        });
               
             
    };

    var initReset = function(){
        var btns = document.querySelectorAll('.wpf_reset_btn input');
        for(var i = btns.length-1;i>=0;i--){
            btns[i].addEventListener('click',function (e) {
                e.preventDefault();
                var target = e.target,
                    area = target.closest('.wpf_item');
                area = null === area ? target.closest('.wpf_form'):area;
                var inputs = area.querySelectorAll('input,select');
                for (var k = inputs.length-1; k>=0; k--) {
                    if(inputs[k].tagName === 'INPUT'){
                        switch (inputs[k].type) {
                            case 'text':
                                inputs[k].value = '';
                                break;
                            case 'radio':
                            case 'checkbox':
                                inputs[k].checked = false;
                        }
                    }else{
                        inputs[k].selectedIndex = 0;
                        $(inputs[k]).val(null).trigger('change');
                    }
                }
                $(area).find('.wpf_slider').each(function () {
                    var $slider = $(this),
                        min = $slider.data('min'),
                        max = $slider.data('max');
                    $slider.siblings( ".wpf_price_from" ).val(min);
                    $slider.siblings( ".wpf_price_to" ).val(max);
                    $slider.slider("values", 0, min);
                    $slider.slider("values", 1, max);
                });
                $(target.closest('.wpf_form')).submit();
            })
        }
    };

    $(document).ready(function () {
        if ($('.wpf_form').length > 0) {
            $('body').addClass('woocommerce');
            // Check for compatibility with Divi & Elementor
            var grid,
                diviConatainer = document.querySelector('.et_pb_module.et_pb_shop'),
                elementorConatainer = document.querySelector('.elementor-element.elementor-wc-products');
            if(null !== diviConatainer){
                diviConatainer.className += ' wpf-search-container';
                // Set Divi column
                grid = diviConatainer.querySelector('ul.products').className.match(/columns-(\d+)/);
            }else if(null !== elementorConatainer){
                elementorConatainer.className += ' wpf-search-container';
                // Set elementor column
                grid = elementorConatainer.querySelector('ul.products').className.match(/columns-(\d+)/);
            }else{
                // Try to get wc-products grid
                var container = document.querySelector('.wc-products');
                if(null!==container){
                    grid = container.className.match(/grid(\d+)/);
                }else{
                    container = document.querySelector('.woocommerce > .products');
                    if(null!==container){
                        grid = container.className.match(/columns-(\d+)/);
                        grid = null !== grid ? grid : container.parentElement.className.match(/columns-(\d+)/);
                    }
                }
            }
            if(null !== grid && undefined != grid){
                document.querySelector('[name="wpf_cols"]').value = grid[1];
            }
            InitTabs();
            InitGroupToggle();
            
            if ($.fn.select2) {
                initSelect();
            }
            InitSlider();
            InitPagination();
            InitOrder();
            InitChange();
            InitAutoComplete();
            InitSubmit();
            initReset();
        }
    });


}(jQuery));
