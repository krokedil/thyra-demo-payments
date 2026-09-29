<?php

/*
 * Plugin Name: Krokedil demo payments
 * Description: Betalplugin demo payments
 * Version: 1.0.0
 * Author: Thyra Nyström
 * Text Domain: krokedil-demo-payments
 * Requires at least: 7
 */



//Abspath
if (! defined('ABSPATH')) {
    exit;
}

//Prefix kdp - Krokedil demo payments

//Från claude, kunde ej hitta hur det gjordes
add_action( 'plugins_loaded', 'kdp_init' );
add_action( 'plugins_loaded', 'kdp_init' );
function kdp_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        // Visa felmeddelande
        return;
    }
 
    // Måste lägga klassen här (efter pluginfilen lästs in)
    class KDP_WC_Gateway extends WC_Payment_Gateway {
 
        public function __construct() {
            $this->id                 = 'kdp_gateway';
            $this->method_title       = 'Krokedil Demo Payments';
            $this->method_description = 'En demo-betalningsmetod';
            $this->icon               = plugins_url( 'icon.jpg', __FILE__ );
            $this->has_fields         = false;
 
            $this->init_form_fields();
            $this->init_settings();
 
            $this->title       = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
 
            // Hook för att spara inställningar
            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array( $this, 'process_admin_options' )
            );
 
            add_action( 'woocommerce_thankyou_kdp_gateway', array( $this, 'kdp_thankyou_page' ) );
            add_action( 'woocommerce_email_after_order_table', array( $this, 'kdp_email_instructions' ), 10, 3 );
        }
 
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Aktivera', 'krokedil-demo-payments' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Aktivera Krokedil Demo Payments', 'krokedil-demo-payments' ),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'       => __( 'Rubrik', 'krokedil-demo-payments' ),
                    'type'        => 'text',
                    'description' => __( 'Visas som betalningsalternativ i kassan.', 'krokedil-demo-payments' ),
                    'default'     => __( 'Krokedil Demo Payments', 'krokedil-demo-payments' ),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __( 'Beskrivning', 'krokedil-demo-payments' ),
                    'type'        => 'textarea',
                    'description' => __( 'Visas under betalningsalternativet i kassan.', 'krokedil-demo-payments' ),
                    'default'     => __( 'Betala med Krokedil Demo Payments!', 'krokedil-demo-payments' ),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __( 'Instruktioner', 'krokedil-demo-payments' ),
                    'type'        => 'textarea',
                    'description' => __( 'Visas på tack-sidan efter genomförd betalning.', 'krokedil-demo-payments' ),
                    'default'     => __( 'Tack för din beställning!', 'krokedil-demo-payments' ),
                    'desc_tip'    => true,
                ),
            );
        }
 
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
 
            // Sätter ordern till rätt status
            $order->update_status( 'on-hold', __( 'Väntar på betalning', 'krokedil-demo-payments' ) );
 
            // Minskar lagersaldo
            wc_reduce_stock_levels( $order_id );
 
            // Tömmer kundvagnen
            WC()->cart->empty_cart();
 
            // Skickar kunden till tack-sidan
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }
 
        public function kdp_thankyou_page( $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order && 'kdp_gateway' === $order->get_payment_method() ) {
                echo '<h2>Tack för din beställning! Vänligen överför summan i orderbekräftelsen till vårt konto.</h2>';
            }
        }
 
        public function kdp_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( ! $sent_to_admin && 'kdp_gateway' === $order->get_payment_method() ) {
                echo '<h2>Tack för din beställning! Vänligen överför summan i orderbekräftelsen till vårt konto.</h2>';
            }
        }
 
    } 
 
    add_filter( 'woocommerce_payment_gateways', 'kdp_add_gateway' );
 
} 



function kdp_add_gateway( $methods ) {
    $methods[] = 'KDP_WC_Gateway';
    return $methods;
}

//Claude, la till för att göra ikonen mindre
add_action( 'wp_head', 'kdp_gateway_icon_style' );
function kdp_gateway_icon_style() {
    echo '<style>
        .wc_payment_method label img { 
            max-height: 30px !important; 
            width: auto !important; 
        }
    </style>';
}
