<?php

/**
 * WooCommerce Core Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author 		WooThemes
 * @category 	Core
 * @package 	WooCommerce/Functions
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'woocommerce_get_page_id' ) ) {

	/**
	 * WooCommerce page IDs
	 *
	 * retrieve page ids - used for myaccount, edit_address, change_password, shop, cart, checkout, pay, view_order, thanks, terms
	 *
	 * returns -1 if no page is found
	 *
	 * @access public
	 * @param string $page
	 * @return int
	 */
	function woocommerce_get_page_id( $page ) {
		$page = apply_filters('woocommerce_get_' . $page . '_page_id', get_option('woocommerce_' . $page . '_page_id'));
		return ( $page ) ? $page : -1;
	}
}

/**
 * is_znd_woocommerce - Returns true if on a page which uses WooCommerce templates (cart and checkout are standard pages with shortcodes and thus are not included)
 *
 * @access public
 * @return bool
 */
function is_znd_woocommerce() {
	return ( is_znd_shop() || is_znd_product_category() || is_znd_product_tag() || is_znd_product() ) ? true : false;
}

if ( ! function_exists( 'is_znd_shop' ) ) {

	/**
	 * is_znd_shop - Returns true when viewing the product type archive (shop).
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_shop() {
		return ( is_znd_post_type_archive( 'product' ) || is_znd_page( woocommerce_get_page_id( 'shop' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_znd_product_taxonomy' ) ) {

	/**
	 * is_znd_product_taxonomy - Returns true when viewing a product taxonomy archive.
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_product_taxonomy() {
		return is_znd_tax( get_object_taxonomies( 'product' ) );
	}
}

if ( ! function_exists( 'is_znd_product_category' ) ) {

	/**
	 * is_znd_product_category - Returns true when viewing a product category.
	 *
	 * @access public
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_znd_product_category( $term = '' ) {
		return is_znd_tax( 'product_cat', $term );
	}
}

if ( ! function_exists( 'is_znd_product_tag' ) ) {

	/**
	 * is_znd_product_tag - Returns true when viewing a product tag.
	 *
	 * @access public
	 * @param string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_znd_product_tag( $term = '' ) {
		return is_znd_tax( 'product_tag', $term );
	}
}

if ( ! function_exists( 'is_znd_product' ) ) {

	/**
	 * is_znd_product - Returns true when viewing a single product.
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_product() {
		return is_znd_singular( array( 'product' ) );
	}
}

if ( ! function_exists( 'is_znd_cart' ) ) {

	/**
	 * is_znd_cart - Returns true when viewing the cart page.
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_cart() {
		return is_znd_page( woocommerce_get_page_id( 'cart' ) );
	}
}

if ( ! function_exists( 'is_znd_checkout' ) ) {

	/**
	 * is_znd_checkout - Returns true when viewing the checkout page.
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_checkout() {
		return ( is_znd_page( woocommerce_get_page_id( 'checkout' ) ) || is_znd_page( woocommerce_get_page_id( 'pay' ) ) ) ? true : false;
	}
}

if ( ! function_exists( 'is_znd_account_page' ) ) {

	/**
	 * is_znd_account_page - Returns true when viewing an account page.
	 *
	 * @access public
	 * @return bool
	 */
	function is_znd_account_page() {
		return is_znd_page( woocommerce_get_page_id( 'myaccount' ) ) || is_znd_page( woocommerce_get_page_id( 'edit_address' ) ) || is_znd_page( woocommerce_get_page_id( 'view_order' ) ) || is_znd_page( woocommerce_get_page_id( 'change_password' ) ) || is_znd_page( woocommerce_get_page_id( 'lost_password' ) ) || apply_filters( 'woocommerce_is_znd_account_page', false ) ? true : false;
	}
}

if ( ! function_exists( 'is_znd_order_received_page' ) ) {

    /**
    * is_znd_order_received_page - Returns true when viewing the order received page.
    *
    * @access public
    * @return bool
    */
    function is_znd_order_received_page() {
        return ( is_znd_page( woocommerce_get_page_id( 'thanks' ) ) ) ? true : false;
    }
}