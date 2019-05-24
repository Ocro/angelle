<?php

function storefront_child_styles() {

    $parent_style = 'storefront-style';

    wp_enqueue_style($parent_style, get_template_directory_uri().'/style.css');
    wp_enqueue_style(
		'storefront-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'storefront_child_styles');

// Remove Add to cart message. 
function empty_wc_add_to_cart_message($message, $product_id) { 
    return ''; 
};
add_filter('wc_add_to_cart_message', 'empty_wc_add_to_cart_message', 10, 2);

function jk_remove_useless_hook_at_init() {
	// Remove search bar in header.
	remove_action('storefront_header', 'storefront_product_search', 40);
	
	// Remove link to cart in menu header.
	remove_action( 'storefront_header', 'storefront_header_cart', 60 );
	
	// Remove menu bar on mobile layout.
	remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
	
	// Customize homepage screen by removing unecessary content.
	remove_action('homepage', 'storefront_product_categories', 20);
	remove_action('homepage', 'storefront_recent_products', 30);
	remove_action('homepage', 'storefront_featured_products', 40);
	remove_action('homepage', 'storefront_popular_products', 50);
	remove_action('homepage', 'storefront_on_sale_products', 60);
	remove_action('homepage', 'storefront_best_selling_products', 70);
	
	// Remove result count on products list.
	remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
	remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
	
	remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
	remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10);
	
	// Remove link "Add to cart" on product list.
	remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart'); 
	
	// Remove related product on product page.
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('init', 'jk_remove_useless_hook_at_init');

// Change Add to cart label.
function woo_custom_single_add_to_cart_text() {
    return __('Demander une consultation', 'woocommerce');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'woo_custom_single_add_to_cart_text');

// Change Add to cart label.
function woo_custom__add_to_cart_text() {
    return __('Demander une consultation', 'woocommerce');
}
add_filter('woocommerce_product_add_to_cart_text', 'woo_custom__add_to_cart_text');

// Redirect user to checkout page after add to cart.
function custom_add_to_cart_redirect($url) {
	return wc_get_checkout_url();
}
add_filter('woocommerce_add_to_cart_redirect', 'custom_add_to_cart_redirect');

// Before add to cart, only allow 1 item in a cart.
function woo_custom_add_to_cart_before($cart_item_data) {
 
    global $woocommerce;
    $woocommerce->cart->empty_cart();
 
    // Do nothing with the data and return
    return true;
}
add_filter( 'woocommerce_add_to_cart_validation', 'woo_custom_add_to_cart_before' );

// Edit checkout field
function custom_override_checkout_fields($fields) {
     $fields['order']['order_comments']['placeholder'] = 'Commentaire concernant votre commande';
     return $fields;
}
add_filter('woocommerce_checkout_fields' , 'custom_override_checkout_fields');

// Remove credits.
function custom_remove_footer_credit(){
    return false;
}
add_filter('storefront_credit_link','custom_remove_footer_credit',10);

/**
 * @snippet       Disable Payment Method for Specific Category
 * @how-to        Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode    https://businessbloomer.com/?p=19892
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 2.5.2
 */ 
function bbloomer_unset_gateway_by_category($available_gateways) {
	global $woocommerce;
	// 19: En Cabinet, 21: par Téléphone, 22: Par Internet, 23: a distance
	$onlyCodCatIDs = array(19);
	$allButCodCatIDs = array(21, 22, 23);
	
	foreach ($woocommerce->cart->cart_contents as $key => $values) {
		$terms = get_the_terms($values['product_id'], 'product_cat');    
		foreach ($terms as $term) {        
			if(in_array($term->term_id, $onlyCodCatIDs)) {
				unset($available_gateways['paypal'], $available_gateways['bacs']);
				break;
			} else if(in_array($term->term_id, $allButCodCatIDs)) {
				unset($available_gateways['cod']);
				break;
			}
			break;
		}
	}
    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways','bbloomer_unset_gateway_by_category');

?>
