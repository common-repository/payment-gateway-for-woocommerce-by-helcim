=== Payment Gateway for WooCommerce - Helcim ===
Contributors: Helcim
Tags: payment gateway for woocommerce, woocommerce, woocommerce payments, woocommerce payment gateway, payment gateway, shopping cart, wordpress payment gateway, woocommerce shopping cart, wordpress shopping cart, payment gateway for woocommerce, payments, credit cards, accept credit cards, recurring billing, woocommerce gateway, woocommerce payment plugin, woocommerce credit cards, accept credit cards woocommerce, credit card gateway woocommerce, accept credit cards on woocommerce, process payments, process credit cards, accept visa, accept mastercard,checkout, accept payments, merchant account, merchant services, helcim
Requires at least: WooCommerce 2.3.1
Tested up to: WooCommerce 3.0.4

== Description ==

**The Woocommerce Payment Gateway developed by [Helcim Inc](https://www.helcim.com "Credit Card Processing").**   
Start accepting credit card payments today on your website or using Helcim's secure hosted payment pages. For development purposes please contact [Helcim Development](https://www.helcim.com/us/virtual-terminal/payment-gateway-api/ "Web Developer Contect") for an account. 

**LEGACY PLUGIN - IMPORTANT**       
Please note that this plugin is for the legacy Helcim Gateway/Virtual Terminal. Merchants using our new Helcim Commerce platform should be using the [Helcim Commerce for WooCommerce plugin](https://wordpress.org/plugins/helcim-commmerce-for-woocommerce/) instead.

       
**WHY HELCIM:**       
       - Enjoy Cost+ pricing in your WooCommerce store.  
       - Quick to setup a merchant account in Canada or the USA,
       - Accept Visa, MasterCard, American Express, Unionpay, Discover Network and more.

**REQUIREMENTS:**       
       - [US Merchant Account Sign Up](https://www.helcim.com/us/apply/ "Unlock Payment Gateway for US Business")      
       - [Canadian Merchant Account Sign Up](https://www.helcim.com/ca/apply/ "Unlock Payment Gateway for Canadian Business")  
  
**YOU CHOOSE HOW TO HOST PAYMENTS:**  
      1. Hosted Payment Page (customers will be forwarded to a secure Helcim Payment Page)  
      2. Direct Integration (for use with the Helcim Payment Gateway, customers stay on your site)

== Installation ==

1. Extract to: /wp-contents/plugins/ directory
2. Activate the plugin through the "Plugins" menu in WordPress
3. Configure the module in WooCommerce->Settings->Checkout then click 'Helcim'
4. Click the Enable/Disable box to enable this gateway
5. Choose which method you would like to use: 
       * Hosted Payment Page (customers will be forwarded to a secure Helcim Payment Page)
       * Direct Integration (for use with the Helcim Payment Gateway, customers stay on your site)

6. Hit "Save Changes". More options will be available after choosing which method you would like to use.

7. For the Hosted Payment Page method:
       * Enter your payment page URL (generated in the Helcim Virtual Terminal)
       * Update your Helcim payment page approval URL to: http://your-website.com/your-wordpress-dir/?wc-api=WC_Gateway_Helcim

8. For the Direct Integration method:
       * You will need a valid SSL certificate and PCI DSS validation
       * Enter your Account ID and Token (from your welcome email and within the Helcim Virtual Terminal)
       * Enter the payment URL. Use https://gateway.helcim.com/ for production and https://gatewaytest.helcim.com/ for development
       * Choose whether you would like to require CVV or not (3 digits in the signature panel).

9. Enter the title to display on the payment selection portion of the checkout page such as "Credit Card"
10. Enter the description your customers will see on the checkout page for this payment option
11. Choose whether or not you would like to show the Helcim logo on the checkout page


==  Frequently Asked Questions ==

**How can I get a Helcim account?**
------------------------------------------------
Please visit the [Helcim Website](https://www.helcim.com/) for information on signing up for a Helcim account.


**Where do I find my Payment Page URL?**
------------------------------------------------
You can find your Payment Page URL in your Helcim Virtual Terminal account under "Payment Pages". 

If this is a new account. You will need to create a new payment page. To do so, follow the directions at: https://www.helcim.com/support/?article=9


**After processing the payment on the hosted payment page I am not re-directed back to my site.**
------------------------------------------------
You need to make sure you updated the "Approval URL" option in your payment page settings:

1. Login to your Helcim Virtual Terminal account.
2. Click "Payment Pages" and choose the payment page you created for your WooCommerce payments.
3. Update the "Approval URL" to your wordpress URL and add ?wc-api=WC_Gateway_Helcim. 
(For example, http://your-website.com/your-wordpress-dir/?wc-api=WC_Gateway_Helcim
4. Save Changes


**Where can I find my token for the direct integration method?**
------------------------------------------------
You can find your gateway token in your Helcim Virtual Terminal account under "Settings". If this is a new account, you will need to generate a new token.


== Screenshots ==

1. Helcim Direct Integration screenshot
2. Helcim Hosted Payment Page screenshot


==  Changelog ==

= 1.0.7 =
* Fixed plugin title

= 1.0.6 =
* Fixed plugin listing in WooCommerce Admin
* Tested for WooCommerce 2.6.14 / WordPress 4.7.3

= 1.0.3 =
* Improved error handling.
* Improved "Pay With Helcim" checkout graphics
* Removal depreciated PHP functions
* Fixed duplicate order ID's

= 1.0.2 =
* readme.txt instructions updated
* Changed response redirect: 'redirect' => $this->get_return_url( $order )
* Changed add_error function to new WooCommerce 2.1.2 notice functions
* Changed error_count function to new WooCommerce 2.1.2 notice functions
* Changed get_order_total() to new get_total()
* Changed get_shipping_total() to new get_total_shipping()
* Fixed AVS fields now sent for direct integration
* Fixed number formatting to proper #####.## for transaction amount

= 1.0.1 =
* Added description text to direct integration checkout.
* Added Credit card logos to direct integration checkout.

= 1.0.0 =
* Initial release.
