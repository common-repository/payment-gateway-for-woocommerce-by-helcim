<?php


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Helcim Inc Payment Gateway
 *
 * Provides integration with the Helcim Payment Gateway.
 *
 * @class 		WC_Gateway_Helcim
 * @extends 	WC_Payment_Gateway
 * @version 	1.0.7
 * @author 		Helcim Inc.
 */
class WC_Gateway_Helcim extends WC_Payment_Gateway {

	/** @var string Payment page URL */
	var $paypage_url;

	/**
	 * Constructor for the gateway
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		$this->id			= 'helcim';
		$this->method_title	= __('Helcim', 'woocommerce');
		$this->validated 	= 0;
		// Load settings		
		$this->init_settings();

		// Define user set variables
		$this->show_logo  = $this->get_option( 'show_logo' ) == 'yes' ? 1 : 0;
		$this->method	= $this->get_option( 'method' );
		$this->use_paypage = $this->method == 'paypage' ? 1 : 0;
		$this->title		= $this->get_option( 'title' );
		$this->description	= $this->get_option( 'description' );
		// Payment page options
		$this->paypage_url	= $this->get_option( 'paypage_url' );
		// Direct integration options		
		$this->account_id	= str_replace( '-', '', $this->get_option( 'account_id' ) );
		$this->token		= $this->get_option( 'token' );
		$this->gateway_url	= $this->get_option( 'gateway_url' );
		$this->require_cvv	= $this->get_option( 'require_cvv' ) == 'yes' ? 1 : 0;
		$this->params		= array('merchantId' => $this->account_id, 'token' => $this->token, 'type' => 'purchase', 'cvvIndicator' => 4);
		if ($this->require_cvv)
			$this->params['cvvIndicator'] = 1;

		if (!$this->use_paypage)
			$this->has_fields	= true;
		else
			$this->has_fields 	= false;

		if ($this->show_logo)
			$this->icon			= apply_filters( 'woocommerce_helcim_checkout_icon', plugins_url('assets/images/helcim_checkout_logo.png', __FILE__));;
		
		$this->init_form_fields();

		add_action( 'woocommerce_receipt_helcim', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wc_gateway_helcim', array( $this, 'check_response' ) );		
	}

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'Helcim Payment Gateway', 'woocommerce' ); ?></h3>
		<p><?php _e ( 'Accept credit cards in your Woocommerce shop.', 'woocommerce' ); ?></p>

		<table class="form-table">
			<?php
				// Generate the settings form HTML
				$this->generate_settings_html();
			?>
		</table><!--/.form-table-->
		<?php
	}

	/**
	* Initiliase Gateway Settings Form Fields
	*
	* @access public
	* @return void
	*
	*/
	public function init_form_fields() {
		// Methods
		$methods = array( 'paypage' => __('Hosted Payment Page', 'woocommerce'), 'direct' => __('Direct Integration') );
		// Transaction Types
		$tx_types = array( 'sale' => __('Sale', 'woocommerce'), 'preauth' => __('Pre-Auth', 'woocommerce') );
		$this->form_fields = array(
			'enabled' => array(
							'title' => __('Enable/Disable', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Enable Helcim Payment Module', 'woocommerce' ),
							'default' => 'yes'
						),
			'method' => array(
							'title' => __( 'Method', 'woocommerce' ),
							'type' => 'select',
							'options' => $methods,
							'description' => __('Choose between using the Helcim Hosted Payment Page and direct Payment Gateway integration', 'woocommerce' ),
							'default' => 'paypage',
							'desc_tip' => true
						)
			);
		// Direct Integration Options
		if (!$this->use_paypage)
		{
			$this->form_fields['account_id'] = array(
							'title' => __('Account ID', 'woocommerce'),
							'type' => 'text',
							'description' => __('Your Helcim account ID.', 'woocommerce'),
							'default' => '',
							'desc_tip' => true
						);			
			$this->form_fields['token'] = array(
							'title' => __('Token', 'woocommerce' ),
							'type' => 'text',
							'description' => __('Your payment gateway token (generated within the Helcim Virtual Terminal).', 'woocommerce'),
							'default' => '',
							'desc_tip' => true
						);
			$this->form_fields['gateway_url'] = array(
							'title' => __('URL', 'woocommerce'),
							'type' => 'text',
							'description' => __('Use https://gateway.helcim.com/ for production and https://gatewaytest.helcim.com/ for development/testing.'),
							'default' => 'https://gateway.helcim.com/',
							'desc_tip' => true
						);
			$this->form_fields['require_cvv'] = array(
							'title' => __('CVV', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Require CVV', 'woocommerce' ),
							'default' => 'yes'
						);
		} 
		// Hosted payment page options
		else {
			$this->form_fields['paypage_url'] = array(
							'title' => __('Payment Page URL', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'The URL for the Helcim Hosted Payment Page.', 'woocommerce' ),
							'default' => '',
							'desc_tip' => true
						);
		}
		$this->form_fields['title'] = array(
							'title' => __('Title', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This controls the title which the user sees during checkout', 'woocommerce' ),
							'default' => __( 'Credit Card - Helcim', 'woocommerce' ),
							'desc_tip' => true
						);
		$this->form_fields['description'] = array(
							'title' => __('Description', 'woocommerce'),
							'type' => 'textarea',
							'description' => __( 'This controls the description which the user sees during checkout', 'woocommerce' ),
							'default' => __( 'Pay via credit card', 'woocommerce' ),
						);
		$this->form_fields['show_logo'] = array(
							'title' => __('Helcim Logo', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Show Helcim logo on checkout', 'woocommerce' ),
							'default' => 'yes'
						);
	}

	/**
	* Process payment
	*
	* @access public
	* @param int $order_id
	* @return array
	*/
	public function process_payment( $order_id ) {

		global $woocommerce;

		if (!$this->validated)
			return;

		$order = new WC_Order( $order_id );

		if ($this->use_paypage)
		{
			return array(
					'result'	=> 'success',
					'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
				);
		}
		else
		{
			$helcim_args = $this->params;

			$helcim_args = array_merge($helcim_args, $this->get_paypage_args($order));

			$helcim_args_string = '';

			foreach ($helcim_args as $key=>$value)
				$helcim_args_string .= $key.'='.urlencode($value).'&';

			$http_params = array(
					'body' => $helcim_args_string,
					'method' => 'POST',
					'timeout' => 45,
					'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
					'sslverify' => false
				);
			$response_array = wp_remote_post($this->gateway_url, $http_params);
			if(is_array(@$response_array)){ return $this->check_direct_response($response_array['body']); }
		}
	}

	/**
	* Payment Fields
	*
	* @access public
	* @return void
	*/
	public function payment_fields()
	{
		if ($this->use_paypage) {
			_e($this->description, 'woocommerce');
			return;
		}
		$months = '';
		for ($i=1; $i<=12; ++$i){
			if ($i == date('m'))
				$sel = ' selected';
			else
				$sel = '';
			$months .= '<option value="'.sprintf('%02d', $i).'"'.$sel.'">'.sprintf('%02d', $i).'</option>';
		}
		$years = '';
		$year = date('y');
		for ($i=$year; $i<$year+15; ++$i)
		{
			if ($i == date('y'))
				$sel = ' selected';
			else
				$sel = '';
			$years .= '<option value="'.$i.'"'.$sel.'">'.$i.'</option>';
		}
		?>
		<table>
			<tbody>	
				<?php if ($this->description) { ?>
				<tr>
					<td colspan="2"><?php _e($this->description, 'woocommerce'); ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td><?php _e('Card Number', 'woocommerce'); ?> <span class="required">*</span></td>
					<td><input type="text" class="input-text" value="" name="helcim_direct_cardnumber"></td>
				</tr>
				<tr>
					<td><?php _e('Expiry Date', 'woocommerce'); ?> <span class="required">*</span></td>
					<td><select name="helcim_direct_expirymonth"><?php echo $months; ?></select>&nbsp;
						<select name="helcim_direct_expiryyear"><?php echo $years; ?></select></td>
				</tr>
				<tr>
					<td><?php _e('CVV2', 'woocommerce'); ?> <?php if ($this->require_cvv) { ?><span class="required">*</span><?php } ?></td>
					<td><input type="text" class="input-text" size="4" value="" name="helcim_direct_cvv"></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	* Validate payment fields
	*
	* @access public
	* @return void
	*/
	public function validate_fields()
	{

		// SET GLOBAL VARS
		global $woocommerce;

		// CHECK FOR PAYMENT PAGE
		if($this->use_paypage){

			// SET
			$this->validated = 1;
			return;

		}

		// CHECK FOR MISSING FIELDS
		if(empty($_POST['helcim_direct_cardnumber']) || strlen($_POST['helcim_direct_cardnumber']) < 15){

			// SET NOTICE
			wc_add_notice('<b>Card number</b> must be 16 characters long.','error');

		}

		// CHECK FOR MISSING FIELDS
		if(empty($_POST['helcim_direct_expirymonth'])){

			// SET NOTICE
			wc_add_notice('<b>Expiry month</b> is a required field.','error');

		}

		// CHECK FOR MISSING FIELDS
		if(empty($_POST['helcim_direct_expiryyear'])){

			// SET NOTICE
			wc_add_notice('<b>Expiry year</b> is a required field.','error');

		}

		// CEHCK FOR CVV REQUIREMENT
		if($this->require_cvv){

			// CHECK FOR MISSING FIELDS
			if(empty($_POST['helcim_direct_cvv'])){

				// SET NOTICE
				wc_add_notice('<b>CVV2</b> is a required field.','error');

			}

		}

		// CHECK FOR NO ERRORS
		//if(!$woocommerce->error_count()){
		if(!wc_get_notices('error')){

			// SET VALUES
			$this->params['cardNumber'] = $_POST['helcim_direct_cardnumber'];
			$this->params['expiryDate'] = $_POST['helcim_direct_expirymonth'].$_POST['helcim_direct_expiryyear'];
			$this->params['cvv']		= $_POST['helcim_direct_cvv'];
			$this->validated 			= 1;

		}else{

			// NO VALID
			$this->validated = 0;

		}

	}

	/**
	* Receipt page
	*
	* @access public
	* @return void
	*/
	public function receipt_page( $order )
	{
		echo '<p>'.__( 'Click the button below to pay (you will be redirected to a new page).', 'woocommerce' ).'</p>';
		echo $this->generate_form( $order );
	}

	/**
	* Generate payment page
	*
	* @access public
	* @param mixed $order_id
	* @return string
	*/
	public function generate_form( $order_id ) {
		global $woocommerce;

		$order = new WC_Order( $order_id );
		$paypage_url = $this->paypage_url;
		$helcim_args = $this->get_paypage_args($order);
		$helcim_args_array = array();

		// CREATE HIDDEN FIELDS - LOOP
		foreach ($helcim_args as $key=>$value){
			
			// CREATE HIDDEN FIELD
			$helcim_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';

		}

/*

FUNCTION DEPRECATED

		$woocommerce->add_inline_js( '			
			jQuery("body").block({
					message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to a secure payment page powered by Helcim to make payment.', 'woocommerce' ) ) . '",
					baseZ: 99999,
					overlayCSS:
					{
						background: "#fff",
						opacity: 0.6
					},
					css: {
				        padding:        "20px",
				        zindex:         "9999999",
				        textAlign:      "center",
				        color:          "#555",
				        border:         "3px solid #aaa",
				        backgroundColor:"#fff",
				        cursor:         "wait",
				        lineHeight:		"24px",
				    }
				});
			jQuery("#submit_helcim_payment_form").click();
		' );

*/

		// RETURN HTML
		return '<form action="'.esc_url( $paypage_url ).'" method="post" id="helcim_payment_form" target="_top">
				' . implode( '', $helcim_args_array) . '
				<input type="submit" class="button alt" id="submit_helcim_payment_form" value="' . __( 'Pay via '.$this->title, 'woocommerce' ) . '" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__( 'Cancel order', 'woocommerce' ).'</a>
			</form>';

	}

	/**
	 * Get payment page args
	 *
	 * @access public
	 * @param mixed $order
	 * @return array
	 */
	public function get_paypage_args( $order ) {
		global $woocommerce;
		$order_id = $order->id;

		// SET OPTIONAL TRANSACTION FIELDS
		$paypage_args = array(
						'cardholderAddress' => $order->billing_address_1.' '.$order->billing_address_2,
						'cardholderPostalCode' => $order->billing_postcode,
						'billingName' => $order->billing_first_name.' '.$order->billing_last_name,
						'billingAddress' => $order->billing_address_1.' '.$order->billing_address_2,
						'billingCity' => $order->billing_city,
						'billingProvince' => $order->billing_state,
						'billingPostalCode' => $order->billing_postcode,
						'billingCountry' => $order->billing_country,
						'billingPhoneNumber' => $order->billing_phone,
						'billingEmailAddress' => $order->billing_email,
						'shippingName' => $order->shipping_first_name.' '.$order->shipping_last_name,
						'shippingAddress' => $order->shipping_address_1.' '.$order->shipping_adress_2,
						'shippingCity' => $order->shipping_city,
						'shippingProvince' => $order->shipping_state,
						'shippingPostalCode' => $order->shipping_postcode,
						'shippingCountry' => $order->shipping_country,
						'comments' => $order->customer_note,
						'amount' => number_format($order->get_total(),2,'.',''),						
						);

		// get cart items
		$i = 1;
		foreach ($order->get_items() as $item) {
			$product = $order->get_product_from_item( $item );
			if (get_option( 'woocommerce_prices_include_tax' ) == 'yes' ) { 
				$taxed = false;
			} else { 
				$taxed = false;
			}
			$paypage_args['itemId'.$i] = $product->get_sku();
			$paypage_args['itemDescription'.$i] = $item['name'];
			$paypage_args['itemQuantity'.$i] = $item['qty'];
			$paypage_args['itemPrice'.$i] = $order->get_item_total($item, $taxed, false);
			$paypage_args['itemTotal'.$i] = $order->get_line_total($item, $taxed, false);			
		}
		
		// GET SHIPPING AND TAXES
		if(get_option( 'woocommerce_prices_include_tax' ) == 'yes' ){

			// SHIPPING HAS EXTRA TAXES
			$paypage_args['shippingAmount'] = number_format($order->get_total_shipping() + $order->get_shipping_tax(),2,'.','');

		}else{

			// NORMAL SHIPPING
			$paypage_args['shippingAmount'] = number_format($order->get_total_shipping(),2,'.','');
			$paypage_args['taxAmount'] = number_format($order->get_total_tax(),2,'.','');

		}

		// SET ORDER ID
		$paypage_args['orderId'] = $order_id.'-'.rand(10000,99999);

		// RETURN ARGUMENTS
		return $paypage_args;

	}

	/**
	 * Check for paypage response
	 *
	 * @access public
	 * @return void
	 */
	public function check_response(){
		global $woocommerce;		
		//@ob_clean();
		$response = $this->parse_response($_POST);
		if ($response->response){
			// approved
			$order_id = @$response->orderId ? @$response->orderId : '';

			// GET EXTRA ORDER ID NUMBERS
			$extraNum = substr($order_id, strpos($order_id, "-"));

			// SET REAL ORDER ID FOR WOOCOMMERCE
			$order_id = str_replace($extraNum,"", $order_id);


			if ($order_id) {			
				$order = new WC_Order( $order_id );
				$order->add_order_note( __('Helcim payment completed', 'woocommerce') .' (Approval Code: ' . $response->approvalCode . ')' );
				$order->payment_complete();
				$woocommerce->cart->empty_cart();

				$redirect = $this->get_return_url( $order );
			} else {
				// error
			}			
			$redirect = remove_query_arg('wc-api', $redirect);
			wp_redirect($redirect);
		}		
		exit;
	}

	/**
	 * Check for direct response
	 *
	 * @access public
	 * @return void
	 */
	public function check_direct_response($response_string){
		global $woocommerce;		
		@ob_clean();

		// CHANGE ENTERS TO &
		$response_string = str_replace("\r\n", '&', $response_string);


		parse_str($response_string, $response_array);

		$response = $this->parse_response($response_array);

		if ($response->response){
			// approved			

			$order_id = @$response->orderId ? @$response->orderId : '';

			// GET EXTRA ORDER ID NUMBERS
			$extraNum = substr($order_id, strpos($order_id, "-"));

			// SET REAL ORDER ID FOR WOOCOMMERCE
			$order_id = str_replace($extraNum,"", $order_id);

			if ($order_id) {			
				$order = new WC_Order( $order_id );
				$order->add_order_note( __('Helcim payment completed', 'woocommerce') .' (Approval Code: ' . $response->approvalCode . ')' );
				$order->payment_complete();
				$woocommerce->cart->empty_cart();

				$redirect = $this->get_return_url( $order );			
			} else {
				// error
			}
			//wp_redirect($redirect);
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}
		else
		{
			
			// DECLINE
			if($response->responseMessage == 'Duplicate order id detected'){

				// SET NOTICE
				//$woocommerce->add_error(__('Payment error: ', 'woothemes') . '<b>'.$response->responseMessage.'</b>. Please contact merchant and verify if payment was completed.');
				wc_add_notice('<b>Payment error:</b> '.$response->responseMessage.' - Please contact merchant and verify if payment was already completed.','error');

			}else{

				// SET NOTICE
				//$woocommerce->add_error(__('Payment error: ', 'woothemes') . '<b>'.$response->responseMessage.'</b>');
				wc_add_notice('<b>Payment error:</b> '.$response->responseMessage,'error');

			}

			return;

		}		

	}

	/**
	 * Parse response
	 *
	 * @access public
	 * @param $post
	 * @return mixed $response
	 */
	public function parse_response($post){
		return (object) $post;
	}
	
}