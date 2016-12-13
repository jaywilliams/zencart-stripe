<?php
/*
file includes/languages/english/modules/payment/stripepay.php
/**
 * Stripe Payments payment method class
 *
 * @package paymentMethod
 * @copyright 2012 Blue-Toucan.co.uk
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @Please direct any suggestions/bugs/comments to Blue Toucan
 *
 * Stripe Payments USD/CAN
 * You must have SSL active on your server to use this in live mode
 *
 */


		define('MODULE_PAYMENT_STRIPEPAY_TEXT_TITLE', 'Stripe secure payments');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_DESCRIPTION', 'Stripe secure payments');
		define('MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_OWNER', 'Card Owner');
		define('MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_NUMBER', 'Credit Card Number');
		define('MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_EXPIRES', 'Expiry');
		define('MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_CVC', 'CVV/CVC number');
		define('MODULE_PAYMENT_STRIPEPAY_ERROR_TITLE', 'Stripe Payment Error');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.stripe.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit the Stripe Website (opens in new window)</a>');		
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_CVV_FAILED', 'CVV/CVC number check failed at Stripe Payments');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_CVV_UNCHECKED', 'CVV/CVC number recorded as unchecked at Stripe');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_AVS_FAILED', 'Address Line 1 check failed at Stripe Payments');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_ZIP_FAILED', 'ZIP/postcode check failed at Stripe Payments');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_AVS_UNCHECKED', 'Address Line 1 check recorded as unchecked at Stripe');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_ZIP_UNCHECKED', 'ZIP/postcode check recorded as unchecked at Stripe');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST', 'You have previously placed an order with us using Stripe.');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_CARD', 'Would you like to use your');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_NUMBER', 'card ending in');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_PAY', 'held on record at Stripe to pay for this order?');
        define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_UNTICK', 'Untick to use a different card');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_TICK', 'Tick to use stored card');
		define('MODULE_PAYMENT_STRIPEPAY_CREDIT_TEXT_CARD_SAVE', 'Tick to save your card details with Stripe for future payments at this site');
		define('MODULE_PAYMENT_STRIPEPAY_TEXT_PUBLISHABLE_KEY_MISSING','Unable to continue - no valid Stripe Publishable Key found');
?>