<?php
/*
 * Plugin Name:     SSBC - WooCommerce - Customer List
 * Plugin URI: 		https://github.com/curiouscactus/woocommerce-customer-list and https://github.com/kokomomtl/wc-product-customer-list
 * Description: 	Displays a list of customers that bought a product on the edit page. Creates tables of who has bought the product, which can be inserted using [customer_list product=(defaults to page you're on) admin=(defaults to false) summary=(defaults to false)]. Calculates a sales count, which can be insterted using [total_sales].
 * Author: 			Lois Overvoorde (adapted from wc-product-customer-list by Kokomo)
 * Author URI: 		https://github.com/curiouscactus and http://www.kokomoweb.com/
 * Text Domain: 	ssbc-woocommerce-customer-list
 * Version: 		1.0.0
 */

// Prevent direct access

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check if Woocommerce is activated

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Define plugin path

	define( 'WPCL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	// Localize plugin

	if( ! function_exists('wpcl_load_textdomain') ) {
		function wpcl_load_textdomain() {
			load_plugin_textdomain( 'wc-product-customer-list', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
		}
		add_action('plugins_loaded', 'wpcl_load_textdomain');
	}

	// Add row action

	if( ! function_exists('wpcl_row_action') ) {
		function wpcl_row_action($actions, $post){
			global $post;
			if ($post->post_type == 'product'){
				$actions['wpcl-customers'] = '<a href="' . admin_url( 'post.php' ) . '?post=' . $post->ID . '&action=edit#customer-bought">' . __('Customers','wc-product-customer-list') . '</a>';
			}
			return $actions;
		}
		add_filter('post_row_actions','wpcl_row_action', 10, 2);
	}

	// Load front-end shortcode 
	
	require_once( WPCL_PLUGIN_PATH . 'includes/shortcodes.php' );

	// Woocommerce Settings

	function wpcl_add_section( $sections ) {
		$sections['wpcl'] = __( 'Product Customer List', 'wc-product-customer-list' );
		return $sections;
	}
	add_filter( 'woocommerce_get_sections_products', 'wpcl_add_section' );

	function wpcl_all_settings( $settings, $current_section ) {
		if ( $current_section == 'wpcl' ) {
			$settings_wpcl = array();
			$settings_wpcl[] = array( 'name' => __( 'Product Customer List for WooCommerce', 'wc-product-customer-list' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure Product Customer List for WooCommerce', 'wc-product-customer-list' ), 'id' => 'wcslider' );
			$settings_wpcl[] = array(
				'name'    => __( 'Order status', 'woocommerce' ),
				'desc'    => __( 'Select one or multiple order statuses for which you will display the customers.', 'wc-product-customer-list' ),
				'id'      => 'wpcl_order_status_select',
				'css'     => 'min-width:300px;',
				'default' => array('wc-completed','wc-processing'),
				'type'    => 'multiselect',
				'options' => array(
					'wc-pending'        => __( 'Pending', 'wc-product-customer-list' ),
					'wc-processing'       => __( 'Processing', 'wc-product-customer-list' ),
					'wc-on-hold'       => __( 'On Hold', 'wc-product-customer-list' ),
					'wc-completed'        => __( 'Completed', 'wc-product-customer-list' ),
					'wc-cancelled'  => __( 'Cancelled', 'wc-product-customer-list' ),
					'wc-refunded'       => __( 'Refunded', 'wc-product-customer-list' ),
					'wc-failed'       => __( 'Failed', 'wc-product-customer-list' ),
				),
				'desc_tip' =>  true,
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Order number column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_order_number',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable order number column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Order date column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_order_date',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable order date column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Order status column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_order_status',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable order status column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Order quantity column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_order_qty',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable order quantity column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Payment method column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_order_payment',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable payment method column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Customer message column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_customer_message',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable customer message column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing first name column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_first_name',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing first name column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing last name column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_last_name',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing last name column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing e-mail column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_email',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing e-mail column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing phone column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_phone',
				'default' => 'yes',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing phone column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing address 1 column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_address_1',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing address 1 column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing address 2 column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_address_2',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing address 2 column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing state column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_state',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing state column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing Postal Code / Zip column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_postalcode',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing postal code / Zip column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Billing country column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_billing_country',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable billing country column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping first name column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_first_name',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping first name column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping last name column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_last_name',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping last name column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping address 1 column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_address_1',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping address 1 column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping address 2 column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_address_2',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping address 2 column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping state column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_state',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping state column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping Postal Code / Zip column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_postalcode',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping postal code / Zip column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'     => __( 'Shipping country column', 'wc-product-customer-list' ),
				'id'       => 'wpcl_shipping_country',
				'default' => 'no',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable shipping country column', 'wc-product-customer-list' ),
			);
			$settings_wpcl[] = array(
				'name'    => __( 'PDF orientation', 'woocommerce' ),
				'id'      => 'wpcl_export_pdf_orientation',
				'css'     => 'min-width:300px;',
				'default' => array('portrait'),
				'type'    => 'select',
				'options' => array(
					'portrait'        => __( 'Portrait', 'wc-product-customer-list' ),
					'landscape'       => __( 'Landscape', 'wc-product-customer-list' ),
				),
				'desc_tip' =>  false,
			);
			$settings_wpcl[] = array(
				'name'    => __( 'PDF page size', 'woocommerce' ),
				'id'      => 'wpcl_export_pdf_pagesize',
				'css'     => 'min-width:300px;',
				'default' => array('letter'),
				'type'    => 'select',
				'options' => array(
					'LETTER'        => __( 'US Letter', 'wc-product-customer-list' ),
					'LEGAL'       => __( 'US Legal', 'wc-product-customer-list' ),
					'A3'       => __( 'A3', 'wc-product-customer-list' ),
					'A4'       => __( 'A4', 'wc-product-customer-list' ),
					'A5'       => __( 'A5', 'wc-product-customer-list' ),
				),
				'desc_tip' =>  false,
			);


			$settings_wpcl[] = array( 'type' => 'sectionend', 'id' => 'wpcl' );
			return $settings_wpcl;

		} else {
			return $settings;
		}
	}

	add_filter( 'woocommerce_get_settings_products', 'wpcl_all_settings', 10, 2 );

	// Load metabox at bottom of product admin screen

	if( ! function_exists('wpcl_post_meta_boxes_setup') ) {
		add_action( 'load-post.php', 'wpcl_post_meta_boxes_setup' );
		function wpcl_post_meta_boxes_setup() {
			add_action( 'add_meta_boxes', 'wpcl_add_post_meta_boxes' );
		}
	}

	// Set metabox defaults

	if( ! function_exists('wpcl_add_post_meta_boxes') ) {
		function wpcl_add_post_meta_boxes() {
			add_meta_box(
				'customer-bought',
				esc_html__( 'Customers who bought this product', 'wc-product-customer-list' ),
				'wpcl_post_class_meta_box',
				'product',
				'normal',
				'default'
			);
		}
	}

	// Enqueue stylesheets and scripts on post edit page only

	if( ! function_exists('wpcl_enqueue_scripts') ) {
		function wpcl_enqueue_scripts($hook) {
			if ( 'post.php' != $hook ) {
				return;
			}
			wp_register_style( 'wpcl-admin-css', plugin_dir_url( __FILE__ ) . 'assets/admin.css', false, '2.3.1' );
			wp_register_style( 'datatables-css', 'https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css', false, '1.10.11' );
			wp_register_style( 'datatables-buttons-css', 'https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css', false, '1.2.2' );

			wp_register_script( 'datatables-js', 'https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.js', true, '2.0.2' );
			wp_register_script( 'datatables-buttons-js', 'https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js', true, '1.2.2' );
			wp_register_script( 'datatables-buttons-flash', 'https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js', true, '1.2.2' );
			wp_register_script( 'datatables-print', 'https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js', true, '1.2.2' );
			wp_register_script( 'datatables-jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js', true, '2.5.0' );
			wp_register_script( 'datatables-pdfmake', plugin_dir_url( __FILE__ ) . 'assets/pdfmake/pdfmake.min.js', true, '0.1.20' );
			wp_register_script( 'datatables-vfs-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.20/vfs_fonts.js', true, '0.1.20' );
			wp_register_script( 'datatables-buttons-html', 'https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js', true, '1.2.2' );
			wp_register_script( 'datatables-buttons-print', 'https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js', true, '1.2.2' );
			wp_register_script( 'datatables-colreorder', 'https://cdn.datatables.net/colreorder/1.3.2/js/dataTables.colReorder.min.js', true, '1.3.2' );
			wp_register_script( 'wpcl-script', plugin_dir_url( __FILE__ ) . 'assets/admin.js', true, '2.3.6' );

			wp_enqueue_style( 'wpcl-admin-css' );
			wp_enqueue_style( 'datatables-css' );
			wp_enqueue_style( 'datatables-buttons-css' );

			wp_enqueue_script( 'datatables-js');
			wp_enqueue_script( 'datatables-buttons-js');
			wp_enqueue_script( 'datatables-buttons-flash');
			wp_enqueue_script( 'datatables-print');
			wp_enqueue_script( 'datatables-jszip');
			wp_enqueue_script( 'datatables-pdfmake');
			wp_enqueue_script( 'datatables-vfs-fonts');
			wp_enqueue_script( 'datatables-buttons-html');
			wp_enqueue_script( 'datatables-buttons-print');
			wp_enqueue_script( 'datatables-colreorder');
			wp_enqueue_script( 'wpcl-script');

			wp_localize_script('wpcl-script', 'wpcl_script_vars', array(
				'copybtn' => __('Copy', 'wc-product-customer-list'),
				'printbtn' => __('Print', 'wc-product-customer-list'),
				'search' => __('Search', 'wc-product-customer-list'),
				'emptyTable' => __('This product currently has no customers', 'wc-product-customer-list'),
				'zeroRecords' => __('No orders match your search', 'wc-product-customer-list'),
				'tableinfo' => __('Showing _START_ to _END_ out of _TOTAL_ orders', 'wc-product-customer-list'),
				'lengthMenu' => __('Show _MENU_ orders', 'wc-product-customer-list'),
				'copyTitle' => __('Copy to clipboard', 'wc-product-customer-list'),
				'copySuccessMultiple' => __('Copied %d rows', 'wc-product-customer-list'),
				'copySuccessSingle' => __('Copied 1 row', 'wc-product-customer-list'),
				'paginateFirst' => __('First', 'wc-product-customer-list'),
				'paginatePrevious' => __('Previous', 'wc-product-customer-list'),
				'paginateNext' => __('Next', 'wc-product-customer-list'),
				'paginateLast' => __('Last', 'wc-product-customer-list'),
				)
			);
		}
		add_action( 'admin_enqueue_scripts', 'wpcl_enqueue_scripts' );
	}

	// Output customer list inside metabox

	if( ! function_exists('wpcl_post_class_meta_box') ) {
		function wpcl_post_class_meta_box( $object, $box )  {
			global $sitepress, $post, $wpdb;
			$post_id = $post->ID;

			// Check for translated products if WPML is activated

			if(isset($sitepress)) {
				$trid = $sitepress->get_element_trid($post_id, 'post_product');
				$translations = $sitepress->get_element_translations($trid, 'product');
				$post_id = Array();
				foreach( $translations as $lang=>$translation){
				    $post_id[] = $translation->element_id;
				}
			}

			// Query the orders related to the product

			$order_statuses = array_map( 'esc_sql', (array) get_option( 'wpcl_order_status_select', array('wc-completed') ) );
			$order_statuses_string = "'" . implode( "', '", $order_statuses ) . "'";
			$post_id = array_map( 'esc_sql', (array) $post_id );
			$post_string = "'" . implode( "', '", $post_id ) . "'";

			$item_sales = $wpdb->get_results( $wpdb->prepare(
				"SELECT o.ID as order_id, oi.order_item_id FROM
				{$wpdb->prefix}woocommerce_order_itemmeta oim
				INNER JOIN {$wpdb->prefix}woocommerce_order_items oi
				ON oim.order_item_id = oi.order_item_id
				INNER JOIN $wpdb->posts o
				ON oi.order_id = o.ID
				WHERE oim.meta_key = '_product_id'
				AND oim.meta_value IN ( $post_string )
				AND o.post_status IN ( $order_statuses_string )
				ORDER BY o.ID DESC",
				$post_id,
				$order_statuses
			) );

			// Get selected columns from the options page

			$product = WC()->product_factory->get_product( $post );
			$columns = array();
			if(get_option( 'wpcl_order_number', 'yes' ) == 'yes') { $columns[] = __('Order', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_order_date', 'no' ) == 'yes') {$columns[] = __('Date', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_first_name', 'yes' ) == 'yes') { $columns[] = __('Billing First name', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_last_name', 'yes' ) == 'yes') { $columns[] = __('Billing Last name', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_email', 'yes' ) == 'yes') { $columns[] = __('Billing E-mail', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_phone', 'yes' ) == 'yes') { $columns[] = __('Billing Phone', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_address_1','no' ) == 'yes') { $columns[] = __('Billing Address 1', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_address_2','no' ) == 'yes') { $columns[] = __('Billing Address 2', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_city','no' ) == 'yes') { $columns[] = __('Billing City', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_state','no' ) == 'yes') { $columns[] = __('Billing State', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_postalcode','no' ) == 'yes') { $columns[] = __('Billing Postal Code / Zip', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_billing_country','no' ) == 'yes') { $columns[] = __('Billing Country', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_first_name','no' ) == 'yes') { $columns[] = __('Shipping First name', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_last_name','no' ) == 'yes') { $columns[] = __('Shipping Last name','wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_address_1','no' ) == 'yes') { $columns[] = __('Shipping Address 1', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_address_2','no' ) == 'yes') { $columns[] = __('Shipping Address 2', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_city','no' ) == 'yes') { $columns[] = __('Shipping City', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_state','no' ) == 'yes') { $columns[] = __('Shipping State', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_postalcode','no' ) == 'yes') { $columns[] = __('Shipping Postal Code / Zip', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_shipping_country','no' ) == 'yes') { $columns[] = __('Shipping Country', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_customer_message','yes' ) == 'yes') { $columns[] = __('Customer Message', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_order_status','no' ) == 'yes') { $columns[] = __('Order Status', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_order_payment', 'no' ) == 'yes') { $columns[] = __('Payment method', 'wc-product-customer-list'); }
			if ( 'variable' == $product->get_type() ) { $columns[] = __('Variation', 'wc-product-customer-list'); }
			if(get_option( 'wpcl_order_qty', 'yes' ) == 'yes') { $columns[] = __('Qty', 'wc-product-customer-list'); }
			?>
			
			<div class="wpcl-init" data-pdf-orientation="<?php echo get_option( 'wpcl_export_pdf_orientation', 'portrait' ); ?>" data-pdf-pagesize="<?php echo get_option( 'wpcl_export_pdf_pagesize', 'LETTER' ); ?>"></div>
			<div id="postcustomstuff" class="wpcl">
				<?php if($item_sales) {
					$emaillist = array();
					$productcount = array();
					?>
					<table id="list-table" style="width:100%">
						<thead>
						<tr>
							<?php foreach($columns as $column) { ?>
								<th>
									<strong><?php echo $column; ?></strong>
								</th>
							<?php } ?>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach( $item_sales as $sale ) {
							$order = wc_get_order( $sale->order_id );
							if($order->order_type !== 'refund') {
							?>
							<tr>
								<?php if(get_option( 'wpcl_order_number', 'yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo '<a href="' . admin_url( 'post.php' ) . '?post=' . $order->id . '&action=edit" target="_blank">' . $order->id . '</a>'; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_order_date', 'no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->order_date; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_first_name', 'yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_first_name; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_last_name', 'yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_last_name; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_email', 'yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo '<a href="mailto:' . $order->billing_email . '">' . $order->billing_email . '</a>'; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_phone', 'yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo '<a href="tel:' . $order->billing_phone . '">' . $order->billing_phone . '</a>'; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_address_1','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_address_1; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_address_2','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_address_2; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_city','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_city; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_state','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_state; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_postalcode','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_postcode; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_billing_country','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->billing_country; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_first_name','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_first_name; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_last_name','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_last_name; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_address_1','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_address_1; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_address_2','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_address_2; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_city','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_city; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_state','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_state; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_postalcode','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_postcode; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_shipping_country','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->shipping_country; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_customer_message','yes' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->customer_message; ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_order_status','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo wc_get_order_status_name($order->status); ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_order_payment','no' ) == 'yes') { ?>
									<td>
										<p>
											<?php echo $order->payment_method; ?>
										</p>
									</td>
								<?php } ?>
								<?php if( 'variable' == $product->get_type() ) {
									$variation = wc_get_product( $order->get_item_meta( $sale->order_item_id, '_variation_id', true) );
									?>
									<td>
										<p>
											<?php 
											if($variation) {
												echo $variation->get_formatted_variation_attributes(true); 

											} else {
												_e('Variation no longer exists', 'wc-product-customer-list');
											} ?>
										</p>
									</td>
								<?php } ?>
								<?php if(get_option( 'wpcl_order_qty', 'yes' ) == 'yes') {
								$quantity = $order->get_item_meta( $sale->order_item_id, '_qty', true);
								$productcount[] = $quantity;
								?>
								<td>
									<p>
										<?php echo $quantity; } ?>
									</p>
								</td>
							</tr>
							<?php
							}
							if ( $order->billing_email ) {
								$emaillist[] = $order->billing_email;
							}
						}
						$emaillist = implode( ',', array_unique( $emaillist ) );
						?>
						</tbody>
					</table>
					<?php if(get_option( 'wpcl_order_qty' ) == 'yes') { ?>
						<p class="total">
							<?php echo '<strong>' . __('Total', 'wc-product-customer-list') . ' : </strong>' . array_sum($productcount); ?>
						</p>
					<?php } ?>
					<a href="mailto:?bcc=<?php echo $emaillist; ?>" class="button"><?php _e('Email all customers', 'wc-product-customer-list'); ?></a>
				<?php
				} else {
					_e('This product currently has no customers', 'wc-product-customer-list');
				}
				?>
			</div>
			<?php
		}
	}

} else {

	// Output error message if Woocommerce is not activated

	if( ! function_exists('wpcl_admin_message') ) {
		add_action('admin_notices', 'wpcl_admin_message');
		function wpcl_admin_message() {
			echo '<div class="error"><p>' . __('Woocommerce Product Customer List is enabled but not effective. It requires WooCommerce 2.2+ in order to work.', 'wc-product-customer-list') . '</p></div>';
		}
	}
} 


