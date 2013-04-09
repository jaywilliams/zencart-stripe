<?php
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
class stripepay extends base
{
    var $code, $title, $description, $enabled;
    // class constructor
    function stripepay()
    {
        global $order,$messageStack;;
        $this->code            = 'stripepay';
        $this->api_version     = 'Stripe Payments v 1.3 for ZenCart';
        $this->title           = MODULE_PAYMENT_STRIPEPAY_TEXT_TITLE;
        $this->description     = MODULE_PAYMENT_STRIPEPAY_TEXT_DESCRIPTION;
        $this->sort_order      = MODULE_PAYMENT_STRIPEPAY_SORT_ORDER;
        $this->enabled         = ((MODULE_PAYMENT_STRIPEPAY_STATUS == 'True') ? true : false);
        $this->form_action_url = '';
		//admin title info
		  if (IS_ADMIN_FLAG === true) {
		  	        if (!function_exists('curl_init')) $this->title .= '<strong><span class="alert"> CURL NOT FOUND. Cannot Use.</span></strong>';
					if ( MODULE_PAYMENT_STRIPEPAY_TESTING_SECRET_KEY == '' ||
          				 MODULE_PAYMENT_STRIPEPAY_TESTING_PUBLISHABLE_KEY == '' ||
                         MODULE_PAYMENT_STRIPEPAY_MERCHANT_LIVE_SECRET_KEY == '' ||
                         MODULE_PAYMENT_STRIPEPAY_LIVE_PUBLISHABLE_KEY == ''){
						 $this->title .= '<strong><span class="alert"> One of your Stripe API keys is missing.</span></strong>';
						 }
					if (ENABLE_SSL_CATALOG!=='true'){
						$this->title .= '<strong><span class="alert"> Catalog SSL appears to be missing. Live payments are not possible</span></strong>';
					  }
		  }else{//client side
		  $this ->title.='<noscript><br><span style="color:red">Javascript is not enabled in your browser - you cannot checkout using Stripe</span></noscript>';
		  }
		
        if ((int) MODULE_PAYMENT_STRIPEPAY_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_STRIPEPAY_ORDER_STATUS_ID;
        } //(int) MODULE_PAYMENT_STRIPEPAY_ORDER_STATUS_ID > 0
        //  $this->form_action_url = '';
        if (is_object($order))
            $this->update_status();
    }
    // class methods
    function update_status()
    {
        global $order, $db;
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_STRIPEPAY_ZONE > 0)) {
            $check_flag = false;
            $check      = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                } //$check['zone_id'] == $order->delivery['zone_id']
                $check->MoveNext();
            }
            if ($check_flag == false) {
                $this->enabled = false;
            } //$check_flag == false
        } //($this->enabled == true) && ((int) MODULE_PAYMENT_STRIPEPAY_ZONE > 0)
    }
    function javascript_validation()
    {
        return false;
    }
    function selection()
    {
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }
    function pre_confirmation_check()
    {
        return false;
    }
    function confirmation()
    {
        global $order,  $db;
        //Stripe get the test/production state
        $publishable_key = ((MODULE_PAYMENT_STRIPEPAY_TESTMODE == 'Test') ? MODULE_PAYMENT_STRIPEPAY_TESTING_PUBLISHABLE_KEY : MODULE_PAYMENT_STRIPEPAY_LIVE_PUBLISHABLE_KEY);
        if ($publishable_key == '') {
?>
       <script type="text/javascript">
        alert('No Stripe Publishable Key found - unable to procede.');
        </script>
        <?php
        }
        //Stripe - check the stripe-id table for a stripe customer number
        $prev_customer = false;
        if (table_exists('stripe_data')) {
            //this will get the most recent order by this customer only - will ignore previous orders    
            $check_customer = $db->Execute("select customers_id, stripe_customer, stripe_fingerprint, stripe_last4, stripe_type from stripe_data where customers_id = '" .$_SESSION['customer_id'] . "' order by stripe_id DESC LIMIT 1");
            if ($check_customer->RecordCount() > 0 && zen_not_null($check_customer->fields['stripe_customer'])) {
                $prev_customer = true;
            }
        }
?>

        <?php 
		##################### displays month name in drop down ###############
        for ($i = 1; $i < 13; $i++) {
            $expires_month[] = array(
                'id' => sprintf('%02d', $i),
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000))
            );
        } //$i = 1; $i < 13; $i++
		##################### displays month name in drop down ###############
		##################### displays month name and number in brackets  in drop down ###############
/*		    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
    }
        $today = getdate();
        for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
            $expires_year[] = array(
                'id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)),
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        } */
		##################### displays month name and number in brackets  in drop down ###############
		##################### displays month  number  in drop down ###############
/*				    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%m',mktime(0,0,0,$i,1,2000)));
    }
        $today = getdate();
        for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
            $expires_year[] = array(
                'id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)),
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        } */
		##################### displays month  number  in drop down ###############
        $confirmation           = array();
        $confirmation['fields'] = array();
        //prev customer
        if ($prev_customer == true ) { //previous customer with a stripe id
		
            $confirmation['fields'][] = array(
                'title' => '<div class="back">' .zen_draw_checkbox_field('', 'use_me', $checked = true, 'class="existing_stripe" style="display:inline"'). MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_CARD . ' ' . $check_customer->fields['stripe_type'] . ' ' . MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_NUMBER . ' ' . $check_customer->fields['stripe_last4'] . ' ' . MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_PAY  . '<span id="stripe_tick">' . MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_UNTICK . '</span>'.'</div>',
                'field' => ''
            );
            //pass the customer id
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('StripeCustomerID', $check_customer->fields['stripe_customer'])
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('StripeToken', 'NONE')
            );
        }
        //cc FIELDS
        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_OWNER . '</span>' . zen_draw_input_field('', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'class="card-name card_hide"'),
            'field' => ''
        );
        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_NUMBER . '</span>'. zen_draw_input_field('', '', 'style="display:inline-block; padding-right:10px" class="card_number card_hide"'),
            'field' => ''
        );
        $confirmation['fields'][] = array(
            'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_EXPIRES . '</span>'.zen_draw_pull_down_menu('', $expires_month, '', 'class="card_expiry_month card_hide"') . '&nbsp;' . zen_draw_pull_down_menu('', $expires_year, '', 'class="card-expiry-year  card_hide"'),
            'field' => ''
        );
        //now for the extra things like CVV
        if (MODULE_PAYMENT_STRIPEPAY_CVV == 'True') {
            $confirmation['fields'][] = array(
                'title' => '<span class="card_hide" style="margin-right:10px">' . MODULE_PAYMENT_STRIPEPAY_CREDIT_CARD_CVC . '</span>'.zen_draw_input_field('', '', 'size="5" maxlength="4" class="card_cvc card_hide"'),
                'field' => ''
            );
        } //MODULE_PAYMENT_STRIPEPAY_CVV == 'True'
        //AVS                
        if (MODULE_PAYMENT_STRIPEPAY_AVS == 'True') {
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['street_address'], 'class="address_line1"')
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['suburb'], 'class="address_line2"')
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['state'], 'class="address_state"')
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['postcode'], 'class="address_zip"')
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['city'], 'class="address_city"')
            );
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('', $order->billing['country']['title'], 'class="address_country"')
            );
        } //MODULE_PAYMENT_STRIPEPAY_AVS == 'True'
        //Now add in a 'save my details at Stripe
        //is the option to be allowed
		if(MODULE_PAYMENT_STRIPEPAY_SAVE_CARD=='True' && MODULE_PAYMENT_STRIPEPAY_CREATE_OBJECT=='True'){
            if (MODULE_PAYMENT_STRIPEPAY_SAVE_CARD_CHECK == 'Checked') {
                $box_tick = '$checked=true';
            } else {
                $box_tick = '';
            }
            $confirmation['fields'][] = array(
                'title' => '<div style="margin:10px 0px"><span class="card_hide ">' . MODULE_PAYMENT_STRIPEPAY_CREDIT_TEXT_CARD_SAVE . '</span>'. zen_draw_checkbox_field('', 'save_me', $box_tick, 'class="new_stripe card_hide"').'</div>',
                'field' =>''
            );
        } else {
            //not allowing the opt out - card details will always be saved with customer
            $confirmation['fields'][] = array(
                'title' => '',
                'field' => zen_draw_hidden_field('StripeSaveCard', 'YES')
            );
        }
?>

        <?php
        $confirmation['title'] = '<style type="text/css">.back{width:'.trim(MODULE_PAYMENT_STRIPEPAY_WIDTH).'px;}</style>';
        $confirmation['title'] .= '<script type="text/javascript">    if (typeof jQuery == "undefined") {//no jquery
        
                                document.write("<script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js\">");
                                document.write("<\/script>");} </script>';
        if ($prev_customer == true) { //previous customer with a stripe id          
            $confirmation['title'] .= '  <script type="text/javascript">
                                            $(document).ready(function() {                                        
                                            $(".card_hide").hide();
                                            $(".existing_stripe").change(function() {                                        
                                                                                    
                                            $(".card_hide").toggle("slow");                                        
                                             if ( $(".existing_stripe:checked").length > 0) {
                                             $("#stripe_tick").html("' . MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_UNTICK . '");
                                             }else{
                                             $("#stripe_tick").html("' . MODULE_PAYMENT_STRIPEPAY_TEXT_PREV_CUST_TICK . '");
                                             }                    
                                        
                                        
                                            });                                    
                                        
                                        
                                            });                                        
                                           </script>';
        }
        if (MODULE_PAYMENT_STRIPEPAY_TESTMODE == 'Test') {
            $confirmation['title'] .= '<div class="back" style="background-color: #E5F2FF; margin:10px 0px;">Stripe Payments Test Mode
                                 <br>Use test card number 4242424242424242 or see https://stripe.com/docs/testing
                                <br>Use any expiry date in the future';
            if (MODULE_PAYMENT_STRIPEPAY_CVV == 'True') {
                $confirmation['title'] .= '<br>Use any CVV number<br>';
            } //MODULE_PAYMENT_STRIPEPAY_CVV == 'True'
            $confirmation['title'] .= '</div>';
        } //MODULE_PAYMENT_STRIPEPAY_TESTMODE == 'Test'
        $confirmation['title'] .= '<script type="text/javascript" src="https://js.stripe.com/v1/"></script>';
        $confirmation['title'] .= '<script type="text/javascript">Stripe.setPublishableKey(\'' . $publishable_key . '\');</script>';
        $confirmation['title'] .= '<script type="text/javascript">

                                                                
                                 $(document).ready(function() {
                                      $("form[name=checkout_confirmation]").submit(function(event) {
                                if ( $(\'.existing_stripe\').attr(\'checked\')) {                                
                            var form$ = $("form[name=checkout_confirmation]");
                            form$.attr(\'action\', \'index.php?main_page=checkout_process\'); 
                            //hide button
                             $("#tdb5").hide();
                            

                            // and submit
                            form$.get(0).submit();
                                                              
                                
                                }else{      
                                        Stripe.createToken({
                                            name: $(\'.card-name\').val(),                    
                                            number: $(\'.card_number\').val(),';
        if (MODULE_PAYMENT_STRIPEPAY_CVV == 'True') {
            $confirmation['title'] .= 'cvc: $(\'.card_cvc\').val(),';
        } //MODULE_PAYMENT_STRIPEPAY_CVV == 'True'
        if (MODULE_PAYMENT_STRIPEPAY_AVS == 'True') {
            $confirmation['title'] .= 'address_line1: $(\'.address_line1\').val(),';
            $confirmation['title'] .= 'address_line2: $(\'.address_line2\').val(),';
            $confirmation['title'] .= 'address_state: $(\'.address_state\').val(),';
            $confirmation['title'] .= 'address_zip: $(\'.address_zip\').val(),';
            $confirmation['title'] .= 'address_city: $(\'.address_city\').val(),';
            $confirmation['title'] .= 'address_country: $(\'.address_country\').val(),';
        } //MODULE_PAYMENT_STRIPEPAY_AVS == 'True'
        $confirmation['title'] .= 'exp_month: $(\'.card_expiry_month\').val(),
                                            exp_year: $(\'.card-expiry-year\').val()
                                        }, stripeResponseHandler);
                                        }

                                        return false;
                                      });
                                    });
                                    
                                </script>';
        $confirmation['title'] .= '<script type="text/javascript">
                                function stripeResponseHandler(status, response) {
                        if (response.error) {
                            alert(response.error.message);
                            //$(".payment-errors").text(response.error.message);
                        } else {
                            var form$ = $("form[name=checkout_confirmation]");
                            var token = response[\'id\'];
                            form$.append("<input type=\'hidden\' name=\'StripeToken\' value=\'" + token + "\'/>");
                             if ( $(\'.new_stripe\').attr(\'checked\')) { 
                             form$.append("<input type=\'hidden\' name=\'StripeSaveCard\' value=\'YES\'/>");
                             }
                            form$.attr(\'action\', \'index.php?main_page=checkout_process\'); 
                            //hide button
                             $("#tdb5").hide();
                            

                            // and submit
                            form$.get(0).submit();
                        }
                    }
                    </script>';
        return $confirmation;
    }
    function process_button()
    {
        return false;
    }

    function before_process()
    {
        global $_POST,  $order, $sendto, $currency, $charge,$db, $messageStack;
        require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/stripepay/Stripe.php');
        //Stripe get the test/production state
        $secret_key = ((MODULE_PAYMENT_STRIPEPAY_TESTMODE == 'Test') ? MODULE_PAYMENT_STRIPEPAY_TESTING_SECRET_KEY : MODULE_PAYMENT_STRIPEPAY_MERCHANT_LIVE_SECRET_KEY);
        Stripe::setApiKey($secret_key);
        $error = '';
        // get the credit card details submitted by the form
        $token = $_POST['StripeToken'];
        //existing customer 
        if (zen_not_null($_POST['StripeCustomerID'])) {
            if ($token == 'NONE') {
                //charge the customer on existing card
                try {
                    $charge = Stripe_Charge::create(array(
                        "amount" => ($order->info['total']) * 100, // amount in cents
                        "currency" => MODULE_PAYMENT_STRIPEPAY_CURRENCY,
                        "customer" => $_POST['StripeCustomerID']
                    ));
                }
                catch (Exception $e) {
                    $error = $e->getMessage();
                    $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
                    zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
                }
            } //end use existing card
            //start new card
            //new card for the customer and he wants to save it (or we are not allowing the option do StripesaveCard==YES
            elseif (zen_not_null($_POST['StripeSaveCard']) && ($_POST['StripeSaveCard'] == 'YES')) {
                try {
                    //update the card for the customer
                    $cu       = Stripe_Customer::retrieve($_POST['StripeCustomerID']);
                    $cu->card = $token;
                    $cu->save();
                    //charge the customer
                    $charge = Stripe_Charge::create(array(
                        "amount" => ($order->info['total']) * 100, // amount in cents
                        "currency" => MODULE_PAYMENT_STRIPEPAY_CURRENCY,
                        "customer" => $_POST['StripeCustomerID']
                    ));
                }
                catch (Exception $e) {
                $error = $e->getMessage();
                $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
            }
            } //end save card
            else {
                //a saved customer has entered new card details but does NOT want them saved. Currently (Nov 2012) Stripe does not allow you to remove a card object so you'll have to charge the card and not the customer
                try {
                    // create the charge on Stripe's servers - this will charge the user's card no customer object
                    $charge = Stripe_Charge::create(array(
                        "amount" => ($order->info['total']) * 100, // amount in cents
                        "currency" => MODULE_PAYMENT_STRIPEPAY_CURRENCY,
                        "card" => $token,
                        "description" => $order->customer['email_address']
                    ));
                }
                catch (Exception $e) {
                $error = $e->getMessage();
                $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
            }
            }
        } //end existing customer
        //new customer wants to save card details
        elseif (zen_not_null($_POST['StripeSaveCard']) && ($_POST['StripeSaveCard'] == 'YES')) {
            //new customer create the object
            try {
                // create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => $order->customer['email_address']
                ));
                // charge the Customer instead of the card
                $charge   = Stripe_Charge::create(array(
                    "amount" => ($order->info['total']) * 100, // amount in cents
                    "currency" => MODULE_PAYMENT_STRIPEPAY_CURRENCY,
                    "customer" => $customer->id
                ));
            }              

            catch (Exception $e) {
                $error = $e->getMessage();
                $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
            }
        }
        //   not a customer token
        else {
            try {
                // create the charge on Stripe's servers - this will charge the user's card no customer object
                $charge = Stripe_Charge::create(array(
                    "amount" => ($order->info['total']) * 100, // amount in cents
                    "currency" => MODULE_PAYMENT_STRIPEPAY_CURRENCY,
                    "card" => $token,
                    "description" => $order->customer['email_address']
                ));
            }
            catch (Exception $e) {
                $error = $e->getMessage();
                $messageStack->add_session('checkout_confirmation', $error . '<!-- [' . $this->code . '] -->', 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL', true, false));
            }
        } //end not a customer token
        //    die ( $charge);
        return false;
    }
	
	
	
	
    function after_process()
    {
        global $charge, $insert_id,  $db;
	        //let's update the stripe_id table
        if (table_exists('stripe_data')) {
            $sql_data_array = array(
                'orders_id' => zen_db_prepare_input($insert_id),
                'stripe_charge_id' => zen_db_prepare_input($charge->id),
                'customers_id' => zen_db_prepare_input($_SESSION['customer_id']),
                'stripe_amount' => zen_db_prepare_input($charge->amount),
                'stripe_amount_refunded' => zen_db_prepare_input($charge->amount_refunded),
                'stripe_currency' => strtoupper(zen_db_prepare_input($charge->currency)),
                'stripe_customer' => zen_db_prepare_input($charge->customer),
                'stripe_description' => zen_db_prepare_input($charge->description),
                'stripe_disputed' => zen_db_prepare_input($charge->disputed),
                'stripe_fee' => zen_db_prepare_input($charge->fee),
                'stripe_invoice' => zen_db_prepare_input($charge->invoice),
                //  'stripe_object' => zen_db_prepare_input($charge->object);
                'stripe_paid' => zen_db_prepare_input($charge->paid),
                'stripe_address_city' => zen_db_prepare_input($charge->card->address_city),
                'stripe_address_country' => zen_db_prepare_input($charge->card->address_country),
                'stripe_address_line1' => zen_db_prepare_input($charge->card->address_line1),
                'stripe_address_line1_check' => zen_db_prepare_input($charge->card->address_line1_check),
                'stripe_address_line2' => zen_db_prepare_input($charge->card->address_line2),
                'stripe_address_zip' => zen_db_prepare_input($charge->card->address_zip),
                'stripe_address_zip_check' => zen_db_prepare_input($charge->card->address_zip_check),
                'stripe_country' => zen_db_prepare_input($charge->card->country),
                'stripe_fingerprint' => zen_db_prepare_input($charge->card->fingerprint),
                'stripe_cvc_check' => zen_db_prepare_input($charge->card->cvc_check),
                'stripe_name' => zen_db_prepare_input($charge->card->name),
                'stripe_last4' => zen_db_prepare_input($charge->card->last4),
                'stripe_exp_month' => zen_db_prepare_input($charge->card->exp_month),
                'stripe_exp_year' => zen_db_prepare_input($charge->card->exp_year),
                'stripe_type' => zen_db_prepare_input($charge->card->type)
            );
            zen_db_perform('stripe_data', $sql_data_array);
        }
        //now let's update the orders table
        $db->Execute("update " . TABLE_ORDERS . " set 
                               cc_type = '" . zen_db_prepare_input($charge->card->type) . "',
                               cc_owner='" . zen_db_prepare_input($charge->card->name) . "',
                               cc_expires='" . zen_db_prepare_input($charge->card->exp_month) . "/" . zen_db_prepare_input($charge->card->exp_year) . "',
                               cc_number='XXXX-XXXX-XXXX-" . zen_db_prepare_input($charge->card->last4) . "'
                                    where
                                orders_id = '" . $insert_id . "' ");
        //AVS checking
        if (MODULE_PAYMENT_STRIPEPAY_AVS == 'True' && ($charge->card->address_line1_check !== 'pass' || $charge->card->address_zip_check !== 'pass')) {
            $error = '';
            if ($charge->card->address_line1_check == 'fail') {
                $error .= MODULE_PAYMENT_STRIPEPAY_TEXT_AVS_FAILED . '. ';
            } //$charge->card->address_line1_check == 'fail'
            if ($charge->card->address_zip_check == 'fail') {
                $error .= MODULE_PAYMENT_STRIPEPAY_TEXT_ZIP_FAILED . '. ';
            } //$charge->card->address_zip_check == 'fail'
            if ($charge->card->address_line1_check == 'unchecked') {
                $error .= MODULE_PAYMENT_STRIPEPAY_TEXT_AVS_UNCHECKED . '. ';
            } //$charge->card->address_line1_check == 'unchecked'
            if ($charge->card->address_zip_check == 'unchecked') {
                $error .= MODULE_PAYMENT_STRIPEPAY_TEXT_ZIP_UNCHECKED;
            } //$charge->card->address_zip_check == 'unchecked'
            $sql_data_array2 = array(
                'orders_status' => MODULE_PAYMENT_STRIPEPAY_AVS_FAILED
            );
            zen_db_perform(TABLE_ORDERS, $sql_data_array2, "update", "orders_id='" .  $insert_id . "'");
            //// also change status  in order history
            $sql_data_array3 = array(
                'orders_id' =>  $insert_id,
                'orders_status_id' => MODULE_PAYMENT_STRIPEPAY_AVS_FAILED,
                'date_added' => 'now()',
                'customer_notified' => 0,
                'comments' => $error
            );
            zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array3);
        } //MODULE_PAYMENT_STRIPEPAY_AVS == 'True' && ($charge->card->address_line1_check !== 'pass' || $charge->card->address_zip_check !== 'pass')
        //CVV checking
        if (MODULE_PAYMENT_STRIPEPAY_CVV == 'True' && $charge->card->cvc_check !== 'pass') {
            $cvv_error = '';
            if ($charge->card->cvc_check == 'fail') {
                $cvv_error .= MODULE_PAYMENT_STRIPEPAY_TEXT_CVV_FAILED . '. ';
            } //$charge->card->cvc_check == 'fail'
            elseif ($charge->card->cvc_check == 'unchecked') {
                $cvv_error .= MODULE_PAYMENT_STRIPEPAY_TEXT_CVV_UNCHECKED . '. ';
            } //$charge->card->cvc_check == 'unchecked'
            $sql_data_array4 = array(
                'orders_status' => MODULE_PAYMENT_STRIPEPAY_CVV_FAILED
            );
            zen_db_perform(TABLE_ORDERS, $sql_data_array4, "update", "orders_id='" .  $insert_id . "'");
            //// also change status  in order history
            $sql_data_array5 = array(
                'orders_id' =>  $insert_id,
                'orders_status_id' => MODULE_PAYMENT_STRIPEPAY_CVV_FAILED,
                'date_added' => 'now()',
                'customer_notified' => 0,
                'comments' => $cvv_error
            );
            zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array5);
        } //MODULE_PAYMENT_STRIPEPAY_CVV == 'True' && $charge->card->cvc_check !== 'pass'
        return false;
    }
    function get_error()
    {
        global $_GET;
        $error = array(
            'title' => MODULE_PAYMENT_STRIPEPAY_ERROR_TITLE,
            'error' => stripslashes($_GET['error'])
        );
        return $error;
    }
    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query  = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_STRIPEPAY_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }
    function install()
    {
        global $db, $messageStack;
        if (defined('MODULE_PAYMENT_STRIPEPAY_STATUS')) {
            $messageStack->add_session('Stripe Payment module already installed.', 'error');
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=stripepay', 'NONSSL'));
            return 'failed';
        }
        //send cheeky cURL to Blue Toucan
        cheeky_curl('installed');
        //
		
        // OK lets add in a new order statuses  Stripe - failures
        $check_query = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Stripe - CVV Failure' limit 1");
        if ($check_query->RecordCount() < 1) {
            $status    = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status_id = $status->fields['status_id'] + 1;
            $languages = zen_get_languages();
            foreach ($languages as $lang) {
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Stripe - CVV Failure')");
            } //$languages as $lang
        } else {
            $status_id = $check_query->fields['orders_status_id'];
        }
        $check_query2 = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Stripe - AVS Failure' limit 1");
        if ($check_query2->RecordCount() < 1) {
            $status2    = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status_id2 = $status2->fields['status_id'] + 1;
            $languages  = zen_get_languages();
            foreach ($languages as $lang) {
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id2 . "', '" . $lang['id'] . "', 'Stripe - AVS Failure')");
            } //$languages as $lang
        } else {
              $status_id2 = $check_query2->fields['orders_status_id'];
        }
        //Now for 'unchecked
        // OK lets add in a new order statuses  Stripe - failures
        $check_query3 = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Stripe - CVV unchecked' limit 1");
        if ($check_query3->RecordCount() < 1) {
            $status3    = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status_id3 = $status3->fields['status_id'] + 1;
            $languages  = zen_get_languages();
            foreach ($languages as $lang) {
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id3 . "', '" . $lang['id'] . "', 'Stripe - CVV unchecked')");
            } //$languages as $lang
        } else {
             $status_id3 = $check_query3->fields['orders_status_id'];
        }
        $check_query4 = $db->Execute("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Stripe - AVS unchecked' limit 1");
        if ($check_query4->RecordCount() < 1) {
            $status4    = $db->Execute("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status_id4 = $status4->fields['status_id'] + 1;
            $languages  = zen_get_languages();
            foreach ($languages as $lang) {
                $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id4 . "', '" . $lang['id'] . "', 'Stripe - AVS unchecked')");
            } //$languages as $lang
        } else {
             $status_id4 = $check_query4->fields['orders_status_id'];
        }

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Stripe Payments', 'MODULE_PAYMENT_STRIPEPAY_STATUS', 'True', 'Do you want to accept Stripe payments?', '6', '10', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_STRIPEPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '20', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_STRIPEPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '30', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_STRIPEPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '40', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        //extra payment statuses
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('CVV failure order status', 'MODULE_PAYMENT_STRIPEPAY_CVV_FAILED', '" . $status_id . "', 'If CVV checking is activated what order status do you want to apply to CVV check failures?', '6', '69', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('AVS failure order status', 'MODULE_PAYMENT_STRIPEPAY_AVS_FAILED', '" . $status_id2 . "', 'If AVS checking is activated what order status do you want to apply to AVS check failures?', '6', '71', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('CVV unchecked order status', 'MODULE_PAYMENT_STRIPEPAY_CVV_UNCHECKED', '" . $status_id3 . "', 'If CVV checking is activated what order status do you want to apply to cases where the CVV is returned as unchecked??', '6', '69', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('AVS unchecked order status', 'MODULE_PAYMENT_STRIPEPAY_AVS_UNCHECKED', '" . $status_id4 . "', 'If AVS checking is activated what order status do you want to apply to cases where the AVS is returned as unchecked?', '6', '71', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
        // test or production?
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_STRIPEPAY_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '50', 'zen_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
        //USD or CAN or GBP or EUR
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Stripe Currency', 'MODULE_PAYMENT_STRIPEPAY_CURRENCY', 'USD', 'The currency that your Stripe account is setup to handle - currently only a choice between USD and CAD - <b>make sure that your store is operating in the same currency!!</b>', '6', '50', 'zen_cfg_select_option(array(\'USD\', \'CAD\', \'EUR\',\'GBP\'), ', now())");
        //API keys
        //Testing Secret Key
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Testing Secret Key', 'MODULE_PAYMENT_STRIPEPAY_TESTING_SECRET_KEY', '', 'Testing Secret Key - obtainable in your Strip dashboard.', '6', '60', now())");
        //Testing Publishable Key
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Testing Publishable Key', 'MODULE_PAYMENT_STRIPEPAY_TESTING_PUBLISHABLE_KEY', '', 'Testing Publishable Key  - obtainable in your Strip dashboard.', '6', '62', now())");
        //Live Secret key
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Live Secret key', 'MODULE_PAYMENT_STRIPEPAY_MERCHANT_LIVE_SECRET_KEY', '', 'Live Secret key  - obtainable in your Strip dashboard.', '6', '64', now())");
        //Live Publishable key    
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Live Publishable key', 'MODULE_PAYMENT_STRIPEPAY_LIVE_PUBLISHABLE_KEY', '', 'Live Publishable key  - obtainable in your Strip dashboard.', '6', '66', now())");
        //CVV - defaults to True
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable CVV/CVC checking', 'MODULE_PAYMENT_STRIPEPAY_CVV', 'True', 'Do you want to enable CVV/CVC checking at Stripe? <b>Highly recommended</b>', '6', '68', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        //AVS - defaults to False
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable AVS check', 'MODULE_PAYMENT_STRIPEPAY_AVS', 'False', 'Do you want to enable Address Verification System checking at Stripe?', '6', '70', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		//create customer object?
		 $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Create a Customer Object at Stripe?', 'MODULE_PAYMENT_STRIPEPAY_CREATE_OBJECT', 'True', 'Do you want to create Customer Objects at Stripe (True) or just charge the card every time (False)? ', '6', '72', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        //save card for customer
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow customers option not to save their card details?', 'MODULE_PAYMENT_STRIPEPAY_SAVE_CARD', 'True', 'Do you want to allow customers the option of not saving their card token with Stripe?', '6', '75', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Above option checked or unchecked?', 'MODULE_PAYMENT_STRIPEPAY_SAVE_CARD_CHECK', 'Checked', 'If the above is set to <b>True</b> do you want the option of saving to be checked or unchecked?', '6', '76', 'zen_cfg_select_option(array(\'Checked\', \'Unchecked\'), ', now())");
		        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Width of checkout payment fields', 'MODULE_PAYMENT_STRIPEPAY_WIDTH', '300', 'Depending on yuor template you may wish to make the credit card payment area in the checkout field wider or narrower. Insert a numerical value here for the desired width in pixels', '6', '80', now())");
		
		
		
		
        //new database table
        $db->Execute("CREATE TABLE IF NOT EXISTS `stripe_data` (
                  `stripe_id` int(11) NOT NULL auto_increment,
                  `orders_id` int(11) NOT NULL,
                  `customers_id` int(11) NOT NULL,
                  `stripe_charge_id` varchar(25) NOT NULL,
                  `stripe_amount` int(25) NOT NULL,
                  `stripe_amount_refunded` int(25) default NULL,
                  `stripe_currency` varchar(6) NOT NULL,
                  `stripe_customer` varchar(64) default NULL,
                  `stripe_description` varchar(255) default NULL,
                  `stripe_disputed` varchar(64) NOT NULL,
                  `stripe_fee` int(11) NOT NULL,
                  `stripe_invoice` varchar(64) default NULL,
                  `stripe_object` varchar(64) NOT NULL,
                  `stripe_paid` int(11) NOT NULL,
                  `stripe_address_city` varchar(255) NOT NULL,
                  `stripe_address_country` varchar(255) NOT NULL,
                  `stripe_address_line1` varchar(255) NOT NULL,
                  `stripe_address_line1_check` varchar(64) default NULL,
                  `stripe_address_line2` varchar(255) default NULL,
                  `stripe_address_zip` varchar(255) default NULL,
                  `stripe_address_zip_check` varchar(64) default NULL,
                  `stripe_country` varchar(64) NOT NULL,
                  `stripe_fingerprint` varchar(64) NOT NULL,
                  `stripe_cvc_check` varchar(64) default NULL,
                  `stripe_name` varchar(64) NOT NULL,
                  `stripe_last4` int(4) NOT NULL,
                  `stripe_exp_month` int(2) NOT NULL,
                  `stripe_exp_year` int(4) NOT NULL,
                  `stripe_type` varchar(64) NOT NULL,
                  PRIMARY KEY  (`stripe_id`)
                ) TYPE=MyISAM  AUTO_INCREMENT=1 ;");
    }
    function remove()
    {
        global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        cheeky_curl('uninstalled');
    }
    function keys()
    {
        return array(
            'MODULE_PAYMENT_STRIPEPAY_STATUS',
            'MODULE_PAYMENT_STRIPEPAY_ZONE',
            'MODULE_PAYMENT_STRIPEPAY_ORDER_STATUS_ID',
            'MODULE_PAYMENT_STRIPEPAY_SORT_ORDER',
            'MODULE_PAYMENT_STRIPEPAY_TESTMODE',
            'MODULE_PAYMENT_STRIPEPAY_CURRENCY',
            'MODULE_PAYMENT_STRIPEPAY_TESTING_SECRET_KEY',
            'MODULE_PAYMENT_STRIPEPAY_TESTING_PUBLISHABLE_KEY',
            'MODULE_PAYMENT_STRIPEPAY_MERCHANT_LIVE_SECRET_KEY',
            'MODULE_PAYMENT_STRIPEPAY_LIVE_PUBLISHABLE_KEY',
            'MODULE_PAYMENT_STRIPEPAY_CVV',
            'MODULE_PAYMENT_STRIPEPAY_CVV_FAILED',
            'MODULE_PAYMENT_STRIPEPAY_CVV_UNCHECKED',
            'MODULE_PAYMENT_STRIPEPAY_AVS',
			'MODULE_PAYMENT_STRIPEPAY_CREATE_OBJECT',
            'MODULE_PAYMENT_STRIPEPAY_AVS_FAILED',
            'MODULE_PAYMENT_STRIPEPAY_AVS_UNCHECKED',
            'MODULE_PAYMENT_STRIPEPAY_SAVE_CARD',
            'MODULE_PAYMENT_STRIPEPAY_SAVE_CARD_CHECK',
			'MODULE_PAYMENT_STRIPEPAY_WIDTH'
        );
    }
    // format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '')
    {
        global $currencies, $currency;
        if (empty($currency_code) || !$this->is_set($currency_code)) {
            $currency_code = $currency;
        } //empty($currency_code) || !$this->is_set($currency_code)
        if (empty($currency_value) || !is_numeric($currency_value)) {
            $currency_value = $currencies->currencies[$currency_code]['value'];
        } //empty($currency_value) || !is_numeric($currency_value)
        return number_format(zen_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
}
////////////////////////////////////////////////////////////
function table_exists($tablename, $database = false)
{
    global $db;
    $res = $db->Execute("
        SELECT COUNT(*) AS count 
        FROM information_schema.tables 
        WHERE table_schema = '" . DB_DATABASE . "' 
        AND table_name = '$tablename'
    ");
    if ($res->fields['count'] > 0) {
        return true;
    } else {
        return false;
    }
}
//////////////////////////////////////////////////////////////
function cheeky_curl($data)
{}
?>