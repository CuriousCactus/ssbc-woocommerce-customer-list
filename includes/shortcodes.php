<?php

function get_products_from_category_by_slug( $slug ) {
	
	$category = get_term_by( 'slug', $slug, 'product_cat' );
	$cat_id = $category->term_id;
	
    $products = new WP_Query( array(
        'post_type'   => 'product',
        'post_status' => 'publish',
        'fields'      => 'ids',
		'posts_per_page' => -1,
        'tax_query'   => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_id,
            )
        ),
    ) );
    return $products->posts;
}

//////////////////////////////////

// Add Shortcode
function wpcl_shortcode( $atts ) {
	$output = '';
	//Shortcode options
	$customer_atts = shortcode_atts( array(
        'product' => get_the_id(),
		'admin' => 'FALSE',
		'summary' => 'FALSE'
    ), $atts );
	
	//HEADERS
	if($customer_atts['admin'] == 'TRUE') {
		$output .= '<table class="customer-list-admin"><tr>';
	}else if($customer_atts['admin'] == 'FALSE'){
		$output .= '<table class="customer-list"><tr>';
	}

	//name
	$output .= '<th>Name</th>';

	//admin headers
	if($customer_atts['admin'] == 'TRUE') {
	
	    //date
	    $output .= '<th>Date</th>';
	  
		//email
		$output .= '<th>Email</th>';
	
		//event-specific options
		if ( has_term( 'external_guests', 'product_cat' ) ) {
			$output .= '<th>College</th>';		
		}
		if ( has_term( 'food', 'product_cat' ) ) {
			$output .= '<th>Dietary requirements</th>';		
		}
	
		if ( has_term( 'bcd', 'product_cat' ) ) {
			$output .= '<th>Pre-drinks</th><th>Dinner drinks</th><th>Post-drinks</th>';
		}else if ( has_term( 'garden_party', 'product_cat' ) ) {
			$output .= '<th>Food order</th>';
		}else if ( has_term( 'race_entry', 'product_cat' ) ) {
			$output .= '<th>Role</th><th>Side</th><th>Gender</th><th>Notes</th>';
		}
		//stash
		if ($customer_atts['summary'] == 'TRUE') {
			$output .= '<th>Product</th>';
		}
		
		if ( has_term( 'sized_stash', 'product_cat' ) || $customer_atts['summary'] == 'TRUE') {
			$output .= '<th>Size</th>';
		}
		
		if ( has_term( 'embroidered_stash', 'product_cat' ) || $customer_atts['summary'] == 'TRUE') {
			$output .= '<th>Embroidery</th>';
		}
		
		//quantity, price and payment
		$output .= '<th>Quantity</th>';
		$output .= '<th>Price</th>';
		$output .= '<th>Payment method</th>';
	}

	$output .= '</tr>';

	//CONTENT
	
    if($customer_atts['summary'] == 'TRUE') {
		$post_ids = get_products_from_category_by_slug( 'stash' );
	}else{
		$post_ids = explode(",", $customer_atts['product']);
	}
	
	global $post, $wpdb;
	
	foreach($post_ids as $post_id){

		$wpcl_orders = '';
		$columns = array();
		$customerquery = "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_itemmeta woim 
			LEFT JOIN {$wpdb->prefix}woocommerce_order_items oi 
			ON woim.order_item_id = oi.order_item_id 
			WHERE meta_key = '_product_id' AND meta_value = %d
			GROUP BY order_id;";
		$order_ids = $wpdb->get_col( $wpdb->prepare( $customerquery, $post_id ) );
		
		if($customer_atts['summary'] == 'TRUE'){
			$order_status = array('wc-pending', 'wc-processing','wc-on-hold');
		}else{
			$order_status = get_option( 'wpcl_order_status_select', array('wc-completed') );
		}
		
		if( $order_ids ) {
			$args = array(
				'post_type'       =>'shop_order',
				'post__in'   => $order_ids,
				'posts_per_page' =>  999,
				'order'          => 'ASC',             
				'post_status' => $order_status,
			);
			$wpcl_orders = new WP_Query( $args );
		}

		if($wpcl_orders) {
			foreach($wpcl_orders->posts as $wpcl_order) {
				$order = new WC_Order($wpcl_order->ID);
				$orderitems = $order->get_items();
			
	 			if ( is_array( $orderitems) ) {
				
					foreach($orderitems as $orderitem){
					
						$prod_id = $orderitem['product_id'];
					
						//we need this so it doesn't list irrelevant things from the same order
					
						if ($prod_id == $post_id) {
					
							$output .= '<tr>';
						
							//names
							//guest name with host in brackets
							//name required
							if($orderitem['item_meta']["guest-name"] != '' && trim($orderitem['item_meta']["guest-name"]) != $order->billing_first_name . ' ' . $order->billing_last_name) {
								$output .= '<td>' . $orderitem['item_meta']["guest-name"] . ' (guest of '. $order->billing_first_name . ' ' . $order->billing_last_name .')</td>';
							
							//name not required
							}else if($orderitem['item_meta']["guest-name-not-own"] != '' && $orderitem['item_meta']["guest-name-not-own"] != $order->billing_first_name . ' ' . $order->billing_last_name) {
								$output .= '<td>' . $orderitem['item_meta']["guest-name-not-own"] . ' (guest of '. $order->billing_first_name . ' ' . $order->billing_last_name .')</td>';
						
							// name no brackets
							}else{
								$output .= '<td>' . $order->billing_first_name . ' ' . $order->billing_last_name . '</td>';
							}
				
							//admin content
							if($customer_atts['admin'] == 'TRUE') {
						
								//date
								$output .= '<td>' . $order->order_date . '</td>';	
													
								//email
								$output .= '<td>' . $order->billing_email . '</td>';
							
								//event-specific options
								if ( has_term( 'ask_for_college', 'product_cat' ) ) {
									$output .= '<td>' . $orderitem['item_meta']["college"] . '</td>';		
								}
								if ( has_term( 'food', 'product_cat' ) ) {
									$output .= '<td>' . $orderitem['item_meta']["dietary-requirements"] . '</td>';	
								}
							
								if ( has_term( 'bcd', 'product_cat' ) ) {
									$output .= '<td>' . $orderitem['item_meta']["pre-drinks"] . '</td>';
									$output .= '<td>' . $orderitem['item_meta']["dinner-drinks"] . '</td>';
									$output .= '<td>' . $orderitem['item_meta']["post-drinks"] . '</td>';
								}else if ( has_term( 'garden_party', 'product_cat' ) ) {
									$output .= '<td>' . $orderitem['item_meta']["food-order"] . '</td>';
								}else if ( has_term( 'race_entry', 'product_cat' ) ) {
									$output .= '<td>' . $orderitem['item_meta']["role"] . '</td>';
									$output .= '<td>' . $orderitem['item_meta']["side"] . '</td>';
									$output .= '<td>' . $orderitem['item_meta']["gender"] . '</td>';
									$output .= '<td>' . $orderitem['item_meta']["notes"] . '</td>';
								}

								//stash
								
								if ($customer_atts['summary'] == 'TRUE') {
									$output .= '<td>' . $orderitem->get_data()['name'] . '</td>';
								}
								if ( has_term( 'sized_stash', 'product_cat' ) || $customer_atts['summary'] == 'TRUE') {
									$output .= '<td>' . $orderitem['item_meta']["size-bar-crawl"] . $orderitem['item_meta']["size-stitch"] . $orderitem['item_meta']["size-square-blades"] . $orderitem['item_meta']["size-xxs-xxl"] . $orderitem['item_meta']["size-xs-xxl"] . $orderitem['item_meta']["size-xs-xl"] . $orderitem['item_meta']["size-s-l"] . '</td>'; //naming sizes rather than brands is now prefered
								}
								
								if ( has_term( 'embroidered_stash', 'product_cat' ) || $customer_atts['summary'] == 'TRUE') {
									if ( $orderitem['item_meta']["embroidery"] == 'Yes'){
										$output .= '<td>' . $orderitem['item_meta']["embroidery-text"] . '</td>';
									}else{
										$output .= '<td></td>';
									}
								}
							
								//quantity
								$output .= '<td>' . $orderitem['qty'] . '</td>';
							
								//totals (with refunds if needed)
								
								//has refund (?not working)
								if($order->get_total_refunded() !=NULL){
									foreach ($orderitems as $key => $product ) {
										//if ($product['item_meta']['_product_id'][0] == $prod_id){  //Taken this out because $product isn't a thing
											$total = $orderitem["line_total"] - $order->get_total_refunded_for_item($key);
											$output .= '<td>£' . number_format((float)$total, 2, '.', '') . '</td>';
										//}
									}
									
								//no refund
								}else{
									$total = $orderitem["line_total"];
									$output .= '<td>£' . number_format((float)$total, 2, '.', '') . '</td>';
								}
							
								//billing method
								$payment_method = $order->payment_method_title;
								$output .= '<td>' . $payment_method . '</td>';
							}
						}
						
						$output .= '</tr>';
					}
				}
			}
		}
	}
	$output .= '</table>';
	return $output;
}
add_shortcode( 'customer_list', 'wpcl_shortcode' );

//total sales count shrotcode


function wpcl_shortcode_total() {
	
	global $post, $wpdb;
	$post_id = $post->ID;

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

	if($item_sales) {
		$productcount = array();
		foreach( $item_sales as $sale ) {
			$order = wc_get_order( $sale->order_id );
			if($order->order_type !== 'refund') {
				if(get_option( 'wpcl_order_qty', 'yes' ) == 'yes') {
					$quantity = $order->get_item_meta( $sale->order_item_id, '_qty', true);
					$productcount[] = $quantity;
				}
			}
		}
	}
	return array_sum($productcount);
}

add_shortcode( 'total_sales', 'wpcl_shortcode_total' );