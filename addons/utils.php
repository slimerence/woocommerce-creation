<?php

function calc_product_price($product_id)
{
    $default_rental_options = wcrp_rental_products_default_rental_options();
    $price = 0;
    if (wcrp_rental_products_is_rental_only($product_id) || (wcrp_rental_products_is_rental_purchase($product_id) && true == $rent)) {

        // Checked against '' as a meta value could potentially be 0 which would trigger an empty condition and use the default instead of 0 (e.g. start days can be 0), if get_post_meta is empty or does not exist it returns ''

        $pricing_type = get_post_meta($product_id, '_wcrp_rental_products_pricing_type', true);
        $pricing_type = ('' !== $pricing_type ? $pricing_type : $default_rental_options['_wcrp_rental_products_pricing_type']);

        $pricing_period = get_post_meta($product_id, '_wcrp_rental_products_pricing_period', true);
        $pricing_period = ('' !== $pricing_period ? $pricing_period : $default_rental_options['_wcrp_rental_products_pricing_period']);

        $pricing_period_multiples = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_multiples', true);
        $pricing_period_multiples = ('' !== $pricing_period_multiples ? $pricing_period_multiples : $default_rental_options['_wcrp_rental_products_pricing_period_multiples']);

        $pricing_period_multiples_maximum = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_multiples_maximum', true);
        $pricing_period_multiples_maximum = ('' !== $pricing_period_multiples_maximum ? $pricing_period_multiples_maximum : $default_rental_options['_wcrp_rental_products_pricing_period_multiples_maximum']);

        $pricing_period_additional_selections = get_post_meta($product_id, '_wcrp_rental_products_pricing_period_additional_selections', true);
        $pricing_period_additional_selections = ('' !== $pricing_period_additional_selections ? $pricing_period_additional_selections : $default_rental_options['_wcrp_rental_products_pricing_period_additional_selections']);
        $pricing_period_additional_selections_array = WCRP_Rental_Products_Misc::days_colon_value_pipe_explode($pricing_period_additional_selections, false);

        $pricing_tiers = get_post_meta($product_id, '_wcrp_rental_products_pricing_tiers', true);
        $pricing_tiers = ('' !== $pricing_tiers ? $pricing_tiers : $default_rental_options['_wcrp_rental_products_pricing_tiers']);

        $pricing_tiers_data = get_post_meta($product_id, '_wcrp_rental_products_pricing_tiers_data', true);
        $pricing_tiers_data = ('' !== $pricing_tiers_data ? $pricing_tiers_data : $default_rental_options['_wcrp_rental_products_pricing_tiers_data']);

        $price_additional_periods_percent = get_post_meta($product_id, '_wcrp_rental_products_price_additional_periods_percent', true);
        $price_additional_periods_percent = ('' !== $price_additional_periods_percent ? $price_additional_periods_percent : $default_rental_options['_wcrp_rental_products_price_additional_periods_percent']);

        $price_additional_period_percent = get_post_meta($product_id, '_wcrp_rental_products_price_additional_period_percent', true);
        $price_additional_period_percent = ('' !== $price_additional_period_percent ? $price_additional_period_percent : $default_rental_options['_wcrp_rental_products_price_additional_period_percent']);

        if ('period_selection' == $pricing_type) {

            // Total overrides are not used for the period selection pricing type, this is condition exists because the meta may still exist containing total overrides when product was previously a different pricing type

            $total_overrides = '';
            $total_overrides_json = '[]';
        } else {

            $total_overrides = get_post_meta($product_id, '_wcrp_rental_products_total_overrides', true);
            $total_overrides = ('' !== $total_overrides ? $total_overrides : $default_rental_options['_wcrp_rental_products_total_overrides']);
            $total_overrides_json = WCRP_Rental_Products_Misc::days_colon_value_pipe_explode($total_overrides, true);
        }

        $minimum_days = get_post_meta($product_id, '_wcrp_rental_products_minimum_days', true);
        $minimum_days = ('' !== $minimum_days ? $minimum_days : $default_rental_options['_wcrp_rental_products_minimum_days']);

        $maximum_days = get_post_meta($product_id, '_wcrp_rental_products_maximum_days', true);
        $maximum_days = ('' !== $maximum_days ? $maximum_days : $default_rental_options['_wcrp_rental_products_maximum_days']);

        if ('period_selection' == $pricing_type) {

            if (false !== $rent_period) {

                if (array_key_exists($rent_period, $pricing_period_additional_selections_array)) {

                    $minimum_days = $rent_period;
                    $maximum_days = $rent_period;
                }
            }
        }

        $start_day = get_post_meta($product_id, '_wcrp_rental_products_start_day', true);
        $start_day = ('' !== $start_day ? $start_day : $default_rental_options['_wcrp_rental_products_start_day']);

        $start_days_threshold = get_post_meta($product_id, '_wcrp_rental_products_start_days_threshold', true);
        $start_days_threshold = ('' !== $start_days_threshold ? $start_days_threshold : $default_rental_options['_wcrp_rental_products_start_days_threshold']);

        $return_days_threshold = get_post_meta($product_id, '_wcrp_rental_products_return_days_threshold', true);
        $return_days_threshold = ('' !== $return_days_threshold ? $return_days_threshold : $default_rental_options['_wcrp_rental_products_return_days_threshold']);

        $disable_rental_start_end_dates_global = get_option('wcrp_rental_products_disable_rental_start_end_dates');
        $disable_rental_start_end_dates_product = get_post_meta($product_id, '_wcrp_rental_products_disable_rental_start_end_dates', true);
        $disable_rental_start_end_dates_product = ('' !== $disable_rental_start_end_dates_product ? $disable_rental_start_end_dates_product : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_dates']);
        $disable_rental_start_end_dates_combined_string = '';

        if (!empty($disable_rental_start_end_dates_global) || !empty($disable_rental_start_end_dates_product)) {

            $disable_rental_start_end_dates_global_array = explode(',', $disable_rental_start_end_dates_global);
            $disable_rental_start_end_dates_product_array = explode(',', $disable_rental_start_end_dates_product);
            $disable_rental_start_end_dates_combined_array = array_merge($disable_rental_start_end_dates_global_array, $disable_rental_start_end_dates_product_array);

            if (!empty($disable_rental_start_end_dates_combined_array)) {

                foreach ($disable_rental_start_end_dates_combined_array as $disable_rental_start_end_dates_combined_array_date) {

                    $disable_rental_start_end_dates_combined_string .= "'" . $disable_rental_start_end_dates_combined_array_date . "',";
                }

                $disable_rental_start_end_dates_combined_string = rtrim($disable_rental_start_end_dates_combined_string, ',');
            }
        }

        $disable_rental_start_end_days = get_post_meta($product_id, '_wcrp_rental_products_disable_rental_start_end_days', true);
        $disable_rental_start_end_days = ('' !== $disable_rental_start_end_days ? $disable_rental_start_end_days : $default_rental_options['_wcrp_rental_products_disable_rental_start_end_days']);

        $months = get_post_meta($product_id, '_wcrp_rental_products_months', true);
        $months = ('' !== $months ? $months : $default_rental_options['_wcrp_rental_products_months']);

        $columns = get_post_meta($product_id, '_wcrp_rental_products_columns', true);
        $columns = ('' !== $columns ? $columns : $default_rental_options['_wcrp_rental_products_columns']);

        $inline = get_post_meta($product_id, '_wcrp_rental_products_inline', true);
        $inline = ('' !== $inline ? $inline : $default_rental_options['_wcrp_rental_products_inline']);
        $inline = ('yes' == $inline ? 'true' : 'false'); // Note the true and false are strings as they end up in the inline js

        $minimum_date = gmdate('d M, Y', strtotime('+' . $start_days_threshold . ' day', time()));

        if ('' !== $start_day) {

            if ('0' == $start_day) {

                $start_day_name = 'sunday';
            } elseif ('1' == $start_day) {

                $start_day_name = 'monday';
            } elseif ('2' == $start_day) {

                $start_day_name = 'tuesday';
            } elseif ('3' == $start_day) {

                $start_day_name = 'wednesday';
            } elseif ('4' == $start_day) {

                $start_day_name = 'thursday';
            } elseif ('5' == $start_day) {

                $start_day_name = 'friday';
            } elseif ('6' == $start_day) {

                $start_day_name = 'saturday';
            }

            $minimum_date = gmdate('d M, Y', strtotime('next ' . $start_day_name, strtotime($minimum_date)));
        }



        <script>
            jQuery(document).ready(function($) {

                let addRentalProductsPopup = false;
                let rentalFormUpdateAjaxRequestTimeout;
                let rentalFormUpdateAjaxRequestDelay = 1000;

                if (window.name.startsWith('wcrp-rental-products-add-rental-products-popup-')) {

                    addRentalProductsPopup = true;
                    addRentalProductsPopupOrderId = window.name.slice(window.name.lastIndexOf('-') + 1);

                }

                function rentalFormAddToStatus(status) {

                    <?php // If adding to cart 
                    ?>

                    if (addRentalProductsPopup == false) {

                        <?php // Opacity gets added/removed below as when choosing different variations standard woo functionality adds an opacity 0.2 with a disabled class but that style is not present for add to cart buttons on simple products so we just set here 
                        ?>

                        if ('enable' == status) {

                            <?php if (in_array('rental_form_add_to_status_enable_delay', $advanced_configuration)) { ?>

                                setTimeout(() => {
                                    $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[type="submit"]').css('opacity', '1').prop('disabled', false);
                                }, '1000');

                            <?php } else { ?>

                                $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[type="submit"]').css('opacity', '1').prop('disabled', false);

                            <?php } ?>

                        } else {

                            if ('disable' == status) {

                                $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[type="submit"]').css('opacity', '0.2').prop('disabled', true);

                            }

                        }

                    } else {

                        <?php // If adding to order 
                        ?>

                        $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[type="submit"]').hide();
                        $('#wcrp-rental-products-add-to-order-<?php echo esc_html($this->rental_form_id); ?>').remove();

                        if ('enable' == status) {

                            $('<a href="#" id="wcrp-rental-products-add-to-order-<?php echo esc_html($this->rental_form_id); ?>" class="wcrp-rental-products-add-to-order single_add_to_cart_button button alt"><?php esc_html_e('Add to order', 'wcrp-rental-products'); ?> <?php esc_html_e('#', 'wcrp-rental-products'); ?>' + addRentalProductsPopupOrderId + '</a>').insertBefore($('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[type="submit"]'));

                        }

                    }

                }

               

                <?php if ('period_selection' == $pricing_type) { ?>

                    function rentalFormUpdateRentalPeriodOptions() {

                        <?php // This runs on page load as rentalFormUpdate() is called which in turn calls rentalFormReset() which calls this, this is also called upon variation selection 
                        ?>

                        $('#wcrp-rental-products-rental-period-select-<?php echo esc_html($this->rental_form_id); ?> option').each(function() {

                            var rentalPeriodOptionUrl = window.location.href.split(/[?#]/)[0]; // URL minus any query parameters and anchors

                            if ('default' == $(this).attr('data-period')) {

                                rentalPeriodOptionUrl += '?<?php echo (true == $rent ? 'rent=1&' : ''); ?>rent_period_qty=' + $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').val();

                            } else {

                                rentalPeriodOptionUrl += '?<?php echo (true == $rent ? 'rent=1&' : ''); ?>rent_period=' + $(this).attr('data-period');
                                rentalPeriodOptionUrl += '&rent_period_qty=' + $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').val();

                            }

                            <?php if ('variable' == $product_type) { ?>

                                $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?> .variations .value select').each(function() {

                                    if ($(this).val() !== '') {

                                        rentalPeriodOptionUrl += '&' + $(this).attr('data-attribute_name') + '=' + $(this).val();

                                    }

                                });

                            <?php } ?>

                            $(this).val(rentalPeriodOptionUrl);

                        });

                    }

                    $('.variations_form').on('woocommerce_variation_select_change', function() {
                        <?php // woocommerce_variation_select_change not show_variation as ensures it can be reset if not a bonafide variation e.g. if variation option not selected 
                        ?>

                        rentalFormUpdateRentalPeriodOptions();

                    });

                    <?php if (false !== $rent_period_qty) { ?>

                        $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').val('<?php echo esc_html($rent_period_qty); ?>');

                <?php

                    }
                }

                ?>

                function rentalFormUpdate() {

                    <?php // This function runs on page load and when other actions are triggered such as quantity field change, variation selection, etc 
                    ?>

                    $('#wcrp-rental-products-spinner-<?php echo esc_html($this->rental_form_id); ?>').fadeIn();

                    rentalFormReset();

                    $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').removeAttr('max');
                    <?php // Ensures qty field doesn't have the max attribute set that core WooCommerce gives it on page load from the standard "purchasable" stock of the product, there isn't really a max with rentals as even though there is a rental stock level the max is different depending on options/dates selected, rentals booked on those dates, etc, the max quantity is determined in the AJAX call later and if the selected options/dates/quantity is not available it shows the maximum quantity avaialble for those in a notice 
                    ?>

                    if (undefined !== rentalFormCalendar) {
                        <?php // On initial page load the rentalFormCalendar won't be instantiated yet so we don't attempt to use it or will cause JS errors, for availability checker auto population on page load these end up populated as the dates get set on rentalFormCalendar render and then the rentalFormCalendar select function triggers this rentalFormUpdate() and at that point rentalFormCalendar is instantiated and these get set 
                        ?>

                        <?php

                        if ('1' == $minimum_days && '1' == $maximum_days) {

                        ?>

                            if (null !== rentalFormCalendar.getStartDate()) {

                                <?php // Only 1 date so set rent from/to to start date 
                                ?>

                                $('#wcrp-rental-products-rent-from-<?php echo esc_html($this->rental_form_id); ?>').val(rentalFormFormatSelectedDate(rentalFormCalendar.getStartDate()));
                                $('#wcrp-rental-products-rent-to-<?php echo esc_html($this->rental_form_id); ?>').val(rentalFormFormatSelectedDate(rentalFormCalendar.getStartDate()));

                            }

                        <?php

                        } else {

                            // The 2 null !== conditions below were previously 1 line as if ( null !== rentalFormCalendar.getStartDate() && null !== rentalFormCalendar.getEndDate() ) { however on some server setups the markup would change && to &#038;&#038; so we've split out the conditions

                        ?>

                            if (null !== rentalFormCalendar.getStartDate()) {

                                if (null !== rentalFormCalendar.getEndDate()) {

                                    <?php // Set rent from/to to start/end date 
                                    ?>

                                    $('#wcrp-rental-products-rent-from-<?php echo esc_html($this->rental_form_id); ?>').val(rentalFormFormatSelectedDate(rentalFormCalendar.getStartDate()));
                                    $('#wcrp-rental-products-rent-to-<?php echo esc_html($this->rental_form_id); ?>').val(rentalFormFormatSelectedDate(rentalFormCalendar.getEndDate()));

                                }

                            }

                        <?php

                        }

                        ?>

                    }

                    function rentalFormUpdateAjaxRequest() {

                        var rentalFormUpdateAjaxRequestData = {
                            'action': 'wcrp_rental_products_rental_form_update',
                            'nonce': '<?php echo esc_html(wp_create_nonce('wcrp_rental_products_rental_form_update')); ?>',
                            'qty': $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').val(),
                            <?php if ('variable' == $product_type) { ?> 'product_id': $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="product_id"]').val(),
                                'variation_id': $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="variation_id"]').val(),
                            <?php } else { ?> 'product_id': $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart button[name="add-to-cart"]').val(),
                            <?php } ?> 'return_days_threshold': $('#wcrp-rental-products-return-days-threshold-<?php echo esc_html($this->rental_form_id); ?>').val(),
                        };

                        var rentalFormUpdateAjaxRequest = jQuery.ajax({
                            'url': '<?php echo esc_html(admin_url('admin-ajax.php')); ?>',
                            'method': 'POST',
                            'data': rentalFormUpdateAjaxRequestData,
                        });

                        rentalFormUpdateAjaxRequest.done(function(response) {

                            $('#wcrp-rental-products-spinner-<?php echo esc_html($this->rental_form_id); ?>').hide();
                            <?php // Hide immediately no fade out so not jumpy 
                            ?>

                            if ('no_product_options_selected' !== response) {

                                if (response.startsWith('unavailable_stock_')) {

                                    <?php // Notices 
                                    ?>

                                    maxQtyAvailable = response.replace('unavailable_stock_', '');

                                    if ('0' == maxQtyAvailable) {
                                        <?php // Just incase product was in stock on page load but stock changed after page loaded and no longer in stock (on refresh or variation change the product would show out of stock) 
                                        ?>

                                        maxQtyText = "<?php esc_html_e('Sorry, this product is now out of stock and unavailable for rental.', 'wcrp-rental-products'); ?>";

                                    } else {

                                        maxQtyText = "<?php esc_html_e('Sorry, the maximum quantity available for rent is', 'wcrp-rental-products'); ?> " + maxQtyAvailable + "<?php esc_html_e('.', 'wcrp-rental-products'); ?> <?php esc_html_e('Please reduce the quantity required.', 'wcrp-rental-products'); ?>";

                                    }

                                    $('<div id="wcrp-rental-products-rental-qty-exceeded-<?php echo esc_html($this->rental_form_id); ?>" class="wcrp-rental-products-rental-qty-exceeded woocommerce-error">' + maxQtyText + '</div>').appendTo('#wcrp-rental-products-rental-form-<?php echo esc_html($this->rental_form_id); ?>');

                                } else {

                                    if (isNaN(rentalPrice)) {
                                        <?php // If no price, this isn't a '' !== condition because rentalPrice already parsed as a float, if empty is NaN, this also ensures that if a price is entered as 0.00 it is still let through to not trigger this no price notice 
                                        ?>

                                        <?php // Simple products with no core WooCommerce price data (whether using for a normal purchase or rental) wouldn't see this as there is no add to cart form and subsequently no rental form, this is shown if it's a variable product and it's rental or purchase based and the purchase price is populated (variation hidden without) AND rental price for a particular variation is not populated 
                                        ?>

                                        $('<div id="wcrp-rental-products-rental-no-price-<?php echo esc_html($this->rental_form_id); ?>" class="wcrp-rental-products-rental-no-price woocommerce-error"><?php esc_html_e('Sorry, this product is currently unavailable for rental as it has no price set, contact us for further information.', 'wcrp-rental-products'); ?></div>').appendTo('#wcrp-rental-products-rental-form-<?php echo esc_html($this->rental_form_id); ?>');

                                    } else {

                                        rentalFormCalendar.setOptions({
                                            lockDays: JSON.parse(response),
                                        });

                                        var rentFromDate = $('#wcrp-rental-products-rent-from-<?php echo esc_html($this->rental_form_id); ?>').val();
                                        var rentToDate = $('#wcrp-rental-products-rent-to-<?php echo esc_html($this->rental_form_id); ?>').val();

                                        <?php // Lock days check, this is needed to check that the dates currently selected do not include any lockDays (unvailable days), this is because a user could select a date range that is available then change the quantity or an option, etc, the dates remain selected but they could now include lockDays and therefore we need to stop the product being allowed to be added to cart and display a notice if the previously selected dates now include the lockDays 
                                        ?>

                                        const lockDaysCheckRentFromDate = rentFromDate;
                                        const lockDaysCheckRentToDate = rentToDate;
                                        const lockDaysCheckRentDateMove = new Date(lockDaysCheckRentFromDate);
                                        let lockDaysCheckRentDate = lockDaysCheckRentFromDate;
                                        let lockDaysCheckPassed = true;

                                        while (lockDaysCheckRentDate < lockDaysCheckRentToDate) {
                                            <?php // Loop all dates including and between rent from and rent to 
                                            ?>

                                            lockDaysCheckRentDate = lockDaysCheckRentDateMove.toISOString().slice(0, 10);

                                            $.map(JSON.parse(response), function(value, index) {

                                                if (lockDaysCheckRentDate == JSON.parse(response)[index]) {

                                                    lockDaysCheckPassed = false;

                                                }

                                            });

                                            lockDaysCheckRentDateMove.setDate(lockDaysCheckRentDateMove.getDate() + 1);

                                            if (false == lockDaysCheckPassed) {

                                                $('<div id="wcrp-rental-products-rental-dates-unavailable-<?php echo esc_html($this->rental_form_id); ?>" class="wcrp-rental-products-rental-dates-unavailable woocommerce-error"><?php esc_html_e('Sorry, the dates you previously selected are unavailable with the quantity/options you have selected.', 'wcrp-rental-products'); ?></div>').appendTo('#wcrp-rental-products-rental-form-<?php echo esc_html($this->rental_form_id); ?>');

                                                break;

                                            }

                                        }

                                        <?php // If lock days check passed 
                                        ?>

                                        if (true == lockDaysCheckPassed) {

                                            <?php // If a rental price, rent from and rent to date set 
                                            ?>

                                            if (!isNaN(rentalPrice) && '' !== rentFromDate && '' !== rentToDate) {

                                                qty = parseInt($('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').val());
                                                <?php // This field is always in the markup, even if the product is set to sold individually - the field is just hidden and it's value is set to 1 by WooCommerce, so a check to see if it exists and set it to 1 is not required here 
                                                ?>

                                                <?php // Date related variables below use timestamps based off 12 noon to ensure calculated correctly when daylight savings time changes during dates 
                                                ?>

                                                var rentFromDateSplit = rentFromDate.split('-');
                                                var rentFromDateTimestamp = new Date(rentFromDateSplit[0], rentFromDateSplit[1] - 1, rentFromDateSplit[2], 12, 0, 0, 0).getTime();
                                                <?php // -1 as months argument start from 0 
                                                ?>

                                                var rentToDateSplit = rentToDate.split('-');
                                                var rentToDateTimestamp = new Date(rentToDateSplit[0], rentToDateSplit[1] - 1, rentToDateSplit[2], 12, 0, 0, 0).getTime();
                                                <?php // -1 as months argument start from 0 
                                                ?>

                                                rentedDays = Math.round(Math.abs((rentFromDateTimestamp - rentToDateTimestamp) / (24 * 60 * 60 * 1000))) + 1;
                                                pricingPeriod = <?php echo esc_html($pricing_period); ?>;
                                                pricingTiersData = '<?php echo wp_json_encode($pricing_tiers_data); ?>';
                                                pricingTiersData = JSON.parse(pricingTiersData);
                                                pricingTierPercent = 0; // If there aren't any matching days greater than the rental days then % is 0 (no change)
                                                pricingTierHighest = 0;
                                                priceAdditionalPeriodPercent = <?php echo (!empty($price_additional_period_percent) ? esc_html($price_additional_period_percent) : 0); ?>;

                                                <?php if (!empty($pricing_tiers_data)) { // Stops JS .length undefined if pricing tiers data is empty for any reason, it shouldn't be due to upgrade function but some installs may not 
                                                ?>

                                                    for (var i = 0; i < pricingTiersData.days.length; i++) {

                                                        // Highest used as days maybe unordered e.g. 1 is 10%, 5 is 20%, 3 is 15% so we want to use the highest

                                                        if (parseInt(pricingTiersData.days[i]) > pricingTierHighest) { // parseInt as days

                                                            if (rentedDays > parseInt(pricingTiersData.days[i])) {

                                                                pricingTierHighest = parseInt(pricingTiersData.days[i]); // parseInt as days
                                                                pricingTierPercent = parseFloat(pricingTiersData.percent[i]); // parseFloat as can be multiple decimal places

                                                            }

                                                        }

                                                    }

                                                <?php } ?>

                                                if (Math.sign(pricingTierPercent) == 1) {
                                                    <?php // If positive 
                                                    ?>

                                                    percentMultiplier = 1 + (pricingTierPercent / 100);

                                                } else { // If negative

                                                    percentMultiplier = 1 - (Math.abs(pricingTierPercent) / 100);

                                                }

                                                <?php // Set total/cart item price 
                                                ?>

                                                if (totalOverrides.hasOwnProperty(rentedDays)) {

                                                    totalOverridesPrice = totalOverrides[rentedDays];

                                                    <?php

                                                    // totalOverridesPrice is amended below as not come through a wc_get_price_to_display

                                                    if ('yes' == $taxes_enabled) {

                                                        if ('taxable' == $product_tax_status) {

                                                            if ('no' == $prices_include_tax && 'incl' == $tax_display_shop) {

                                                    ?>

                                                                totalOverridesPrice = totalOverridesPrice * (1 + (<?php echo esc_html($product_tax_rate); ?> / 100));

                                                            <?php

                                                            } elseif ('yes' == $prices_include_tax && 'excl' == $tax_display_shop) {

                                                            ?>

                                                                totalOverridesPrice = totalOverridesPrice / (1 + (<?php echo esc_html($product_tax_rate); ?> / 100));

                                                    <?php

                                                            }
                                                        }
                                                    }

                                                    ?>

                                                    $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text((parseFloat(totalOverridesPrice) * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                    $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(totalOverridesPrice);

                                                } else {

                                                    <?php

                                                    if ('fixed' == $pricing_type) {

                                                        if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {

                                                    ?>

                                                            $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(((rentalPrice) * qty) * percentMultiplier).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                            $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice * percentMultiplier);

                                                        <?php } else { ?>

                                                            $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                            $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice);

                                                            <?php

                                                        }
                                                    } elseif ('period' == $pricing_type) {

                                                        if ('1' !== $pricing_period) {

                                                            if ('yes' == $pricing_period_multiples) {

                                                                if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {

                                                                    if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {

                                                            ?>

                                                                        $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * ((rentedDays / pricingPeriod) - 1))) * qty * percentMultiplier).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                        $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * ((rentedDays / pricingPeriod) - 1))) * percentMultiplier);

                                                                    <?php } else { ?>

                                                                        $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * (rentedDays / pricingPeriod) * qty * percentMultiplier).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                        $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice * (rentedDays / pricingPeriod) * percentMultiplier);

                                                                    <?php

                                                                    }
                                                                } else {

                                                                    if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {

                                                                    ?>

                                                                        $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * ((rentedDays / pricingPeriod) - 1))) * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                        $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * ((rentedDays / pricingPeriod) - 1)));

                                                                    <?php } else { ?>

                                                                        $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * (rentedDays / pricingPeriod) * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                        $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice * (rentedDays / pricingPeriod));

                                                                <?php

                                                                    }
                                                                }
                                                            } else {

                                                                ?>

                                                                $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice);

                                                                <?php

                                                            }
                                                        } else {

                                                            if ('yes' == $pricing_tiers && !empty($pricing_tiers_data)) {

                                                                if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {

                                                                ?>

                                                                    $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * (rentedDays - 1))) * qty * percentMultiplier).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                    $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * (rentedDays - 1))) * percentMultiplier);

                                                                <?php } else { ?>

                                                                    $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * rentedDays * qty * percentMultiplier).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                    $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice * rentedDays * percentMultiplier);

                                                                <?php

                                                                }
                                                            } else {

                                                                if ('yes' == $price_additional_periods_percent && (float) $price_additional_period_percent > 0) {

                                                                ?>

                                                                    $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat((rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * (rentedDays - 1))) * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                    $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice + (((rentalPrice * priceAdditionalPeriodPercent) / 100) * (rentedDays - 1)));

                                                                <?php } else { ?>

                                                                    $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(rentalPrice * rentedDays * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                                    $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(rentalPrice * rentedDays);

                                                        <?php

                                                                }
                                                            }
                                                        }
                                                    } elseif ('period_selection' == $pricing_type) {

                                                        ?>

                                                        periodSelectionPrice = rentalPrice;

                                                        <?php

                                                        // periodSelectionPrice is amended below, depending on whether rentalPrice has come through a wc_get_price_to_display or not

                                                        if ('variable' == $product_type && isset($_GET['rent_period'])) {

                                                            if ('yes' == $taxes_enabled) {

                                                                if ('taxable' == $product_tax_status) {

                                                                    if ('no' == $prices_include_tax && 'incl' == $tax_display_shop) {

                                                        ?>

                                                                        periodSelectionPrice = periodSelectionPrice * (1 + (<?php echo esc_html($product_tax_rate); ?> / 100));

                                                                    <?php

                                                                    } elseif ('yes' == $prices_include_tax && 'excl' == $tax_display_shop) {

                                                                    ?>

                                                                        periodSelectionPrice = periodSelectionPrice / (1 + (<?php echo esc_html($product_tax_rate); ?> / 100));

                                                        <?php

                                                                    }
                                                                }
                                                            }
                                                        }

                                                        ?>

                                                        $('#wcrp-rental-products-total-price-<?php echo esc_html($this->rental_form_id); ?>').text(parseFloat(periodSelectionPrice * qty).toFixed(<?php echo esc_html($price_decimals); ?>).replace('.', '<?php echo wp_kses_post($price_decimal_separator); ?>'));
                                                        $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val(periodSelectionPrice);

                                                    <?php

                                                    }

                                                    ?>

                                                }

                                                <?php

                                                // Total days, return within, addons, etc

                                                if ('period_selection' !== $pricing_type) {

                                                    // Total days element isn't there for period selection, so not needed if that pricing type

                                                ?>

                                                    $('#wcrp-rental-products-total-days-<?php echo esc_html($this->rental_form_id); ?>').text(rentedDays);

                                                <?php

                                                }

                                                if ($return_days_threshold > 0) {

                                                ?>

                                                    $('#wcrp-rental-products-rental-return-within-<?php echo esc_html($this->rental_form_id); ?>').show();

                                                <?php

                                                } else {

                                                ?>

                                                    $('#wcrp-rental-products-rental-return-within-<?php echo esc_html($this->rental_form_id); ?>').hide();

                                                <?php

                                                }

                                                ?>

                                                $('#wcrp-rental-products-rental-return-within-days-<?php echo esc_html($this->rental_form_id); ?>').text(<?php echo esc_html($return_days_threshold); ?>);
                                                $('#wcrp-rental-products-rental-totals-<?php echo esc_html($this->rental_form_id); ?>').attr('style', 'display: block !important;') <?php // Not .show() as !important required (see related public CSS for info) 
                                                                                                                                                                                        ?>

                                                if ($('#product-addons-total').length !== 0) {
                                                    <?php // If WooCommerce Product Add-Ons totals element is on page then we know add-ons could be used so we show the excludes add-ons info 
                                                    ?>

                                                    $('#wcrp-rental-products-excludes-addons-<?php echo esc_html($this->rental_form_id); ?>').show();

                                                }

                                                <?php // Enable add to cart 
                                                ?>

                                                rentalFormAddToStatus('enable');

                                            }

                                        }

                                    }

                                }

                            }

                            <?php // Validation to reduce risk of hidden field modification, see related functionality in WCRP_Rental_Products_Cart_Checks::check_rental_cart_items() 
                            ?>

                            cartItemValidationString = $('#wcrp-rental-products-cart-item-timestamp-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString += $('#wcrp-rental-products-cart-item-price-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString += $('#wcrp-rental-products-rent-from-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString += $('#wcrp-rental-products-rent-to-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString += $('#wcrp-rental-products-start-days-threshold-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString += $('#wcrp-rental-products-return-days-threshold-<?php echo esc_html($this->rental_form_id); ?>').val();
                            cartItemValidationString = btoa(cartItemValidationString);

                            $('#wcrp-rental-products-cart-item-validation-<?php echo esc_html($this->rental_form_id); ?>').val(cartItemValidationString);

                        });

                    }

                    <?php // Ensure rentalFormUpdateAjaxRequest only gets run after the set timeouts, this effectively makes multiple rentalFormUpdate() calls run one by one without stopping the user interacting with the elements (e.g. fields) within the page (the alternative of setting the ajax requests to async: false would cause this). Without the timeout if changes occur to quantity/options, etc quickly the rentalFormUpdateAjaxRequest would fire immediately and the slower requests (e.g. ones that conditionally check lockDays) would end up loading and setting notices/add to cart button status last, so if for example, a user has the quantity set to 1 and could see a total price/add to cart button but then clicked the up arrow on the quantity field multiple times to get to a quantity of 10 and the product was unavailable at that stock level, the previous 9 requests would show the rental totals/add to cart button enabled for a brief period until the rentalFormUpdateAjaxRequest with the 10 qty (which would return unavailable) catches up and therefore in that period of time it would possible for a user to add 10 to cart (this would be caught on the cart/checkout pages by the cart checks, but it's best to catch this within the product page with the below) 
                    ?>

                    if (rentalFormUpdateAjaxRequestTimeout) {

                        clearTimeout(rentalFormUpdateAjaxRequestTimeout);

                    }

                    rentalFormUpdateAjaxRequestTimeout = setTimeout(rentalFormUpdateAjaxRequest, rentalFormUpdateAjaxRequestDelay);

                }

                $(document).on('change', '#wcrp-rental-products-rental-period-select-<?php echo esc_html($this->rental_form_id); ?>', function(e) {

                    window.location = $(this).val();

                });

              
                <?php

                $maybe_use_pricing_period_additional_selections_price = false;

                if (wcrp_rental_products_is_rental_purchase($product_id) && true == $rent) {

                    if ('' !== get_post_meta($product_id, '_wcrp_rental_products_rental_purchase_price', true)) {

                        // wc_get_price_to_display with passed rental purchase price means its converted to inc/exc tax as per settings, str_replace happens incase the store uses a different decimal seperator, sets it to . character, otherwise the calculations for the rental total would be NaN, also this occurs in JS upon variation change, see later in JS

                ?>

                        let rentalPrice = parseFloat("<?php echo esc_html(wc_get_price_to_display($product, array('price' => str_replace($price_decimal_separator, '.', get_post_meta($product_id, '_wcrp_rental_products_rental_purchase_price', true))))); ?>");

                        <?php

                        if ('period_selection' == $pricing_type) {

                            $maybe_use_pricing_period_additional_selections_price = true;
                        }
                    } else {

                        ?>

                        let rentalPrice = NaN;
                        <?php // No price set, will show as unavailable, see related isNaN conditions, rentalPrice also gets set to this where else rentalPrice is being defined if empty due to the parseFloats 
                        ?>

                    <?php } ?>

                <?php } else { ?>

                    let rentalPrice = parseFloat("<?php echo esc_html($product_price); ?>");
                    <?php // Doesn't matter that this is converted to a float, as if price is empty the rental form (inc this code) doesn't get used (no price set) 
                    ?>

                    <?php

                    if ('period_selection' == $pricing_type) {

                        $maybe_use_pricing_period_additional_selections_price = true;
                    }
                }

                if (true == $maybe_use_pricing_period_additional_selections_price) {

                    if (false !== $rent_period) {

                        if (array_key_exists($rent_period, $pricing_period_additional_selections_array)) {

                    ?>

                            rentalPrice = parseFloat("<?php echo esc_html(wc_get_price_to_display($product, array('price' => str_replace($price_decimal_separator, '.', $pricing_period_additional_selections_array[$rent_period])))); ?>");

                <?php

                        }
                    }
                }

                ?>

                let totalOverrides = JSON.parse('<?php echo wp_kses_post($total_overrides_json); ?>');

                $('#wcrp-rental-products-rental-form-wrap-<?php echo esc_html($this->rental_form_id); ?>').find('.cart input[name="quantity"]').on('change', function() {

                    rentalFormUpdate();

                });

                <?php if ('variable' == $product_type) { ?>

                    let rentalProductVariations = JSON.parse($('.variations_form').attr('data-product_variations'));

                    <?php // On page load (covers default varitation options) 
                    ?>

                    updateVariablesToVariationData();

                    $('.variations_form').on('show_variation', function() {
                        <?php // show_variation as when a combination of options selected which triggers the variation id to be populated 
                        ?>

                        updateVariablesToVariationData();

                    });

                    $('.variations .reset_variations').on('click', function() {

                        rentalFormReset();

                    });

                    function updateVariablesToVariationData() {

                        Object.keys(rentalProductVariations).forEach(function(k) {

                            if ($('.variations_form input[name="variation_id"]').val() == rentalProductVariations[k]['variation_id']) {

                                <?php

                                // Rental price

                                if (wcrp_rental_products_is_rental_purchase($product_id) && true == $rent) {

                                ?>

                                    rentalPrice = parseFloat(rentalProductVariations[k]['wcrp_rental_products_rental_purchase_price'].replace('<?php echo wp_kses_post($price_decimal_separator); ?>', '.'));
                                    <?php // Change the price entered with store decimal separator to use . as needed for calculations (display is changed to use the price decimal separator on display) 
                                    ?>

                                <?php } else { ?>

                                    rentalPrice = parseFloat(rentalProductVariations[k]['display_price']);
                                    <?php // Replace not required here as display price returns in normal format not using the decimal separator 
                                    ?>

                                <?php

                                }

                                if (false !== $rent_period) {

                                    // If it is a pricing period additional selection

                                ?>

                                    if (rentalProductVariations[k]['wcrp_rental_products_pricing_period_additional_selections'] !== '[]') {

                                        rentalProductVariationsPricingPeriodAdditionalSelections = JSON.parse(rentalProductVariations[k]['wcrp_rental_products_pricing_period_additional_selections']);

                                        if (rentalProductVariationsPricingPeriodAdditionalSelections.hasOwnProperty('<?php echo esc_html($rent_period); ?>')) {

                                            rentalPrice = parseFloat(rentalProductVariationsPricingPeriodAdditionalSelections[<?php echo esc_html($rent_period); ?>].replace('<?php echo wp_kses_post($price_decimal_separator); ?>', '.'));
                                            <?php // Change the price entered with store decimal separator to use . as needed for calculations (display is changed to use the price decimal separator on display) 
                                            ?>

                                        }

                                    }

                                <?php

                                }

                                // Total overrides

                                if ('period_selection' !== $pricing_type) {

                                ?>

                                    if (rentalProductVariations[k]['wcrp_rental_products_total_overrides'] !== '[]') {

                                        totalOverrides = JSON.parse(rentalProductVariations[k]['wcrp_rental_products_total_overrides']);

                                    }

                                <?php

                                }

                                ?>

                            }

                        });

                        rentalFormUpdate();

                    }

                <?php

                }

                // Rental form calendar

                ?>

                var availabilityCheckerPopulationInitial = true;

    }
    return $price;
}