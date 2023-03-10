<?php
namespace Bookme\Inc\Payment;

use Bookme\Inc;

/**
 * Class PayPal
 * @package Bookme\Lib\Payment
 */
class PayPal
{
    const TYPE_EXPRESS_CHECKOUT = 'ec';
    const TYPE_PAYMENTS_STANDARD = 'ps';

    const URL_POSTBACK_IPN_LIVE = 'https://www.paypal.com/cgi-bin/webscr';
    const URL_POSTBACK_IPN_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    // Array for cleaning PayPal request
    static public $remove_parameters = array( 'bookme_action', 'bookme_fid', 'error_msg', 'token', 'PayerID',  'type' );

    /** @var  string */
    private $error;

    /** @var array */
    protected $products = array();

    /**
     * Send the Express Checkout request
     *
     * @param $form_id
     * @throws \Exception
     */
    public function send_ec_request($form_id )
    {
        $current_url = Inc\Mains\Functions\System::get_current_page_url();

        // create the data to send on PayPal
        $data = array(
            'SOLUTIONTYPE' => 'Sole',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_CURRENCYCODE'  => get_option( 'bookme_currency' ),
            'NOSHIPPING' => 1,
            'RETURNURL'  => add_query_arg( array( 'bookme_action' => 'paypal-ec-return', 'bookme_fid' => $form_id ), $current_url ),
            'CANCELURL'  => add_query_arg( array( 'bookme_action' => 'paypal-ec-cancel', 'bookme_fid' => $form_id ), $current_url )
        );
        $total = 0;
        foreach ( $this->products as $index => $product ) {
            $data[ 'L_PAYMENTREQUEST_0_NAME' . $index ] = $product->name;
            $data[ 'L_PAYMENTREQUEST_0_AMT' . $index ]  = $product->price;
            $data[ 'L_PAYMENTREQUEST_0_QTY' . $index ]  = $product->qty;

            $total += ( $product->qty * $product->price );
        }
        $data['PAYMENTREQUEST_0_AMT']     = $total;
        $data['PAYMENTREQUEST_0_ITEMAMT'] = $total;

        // send the request to PayPal
        $response = $this->send_nvp_request( 'SetExpressCheckout', $data );
        if ( $response === null ) {
            $url = wp_sanitize_redirect(
                add_query_arg( array(
                    'bookme_action' => 'paypal-ec-error',
                    'bookme_fid'    => $form_id,
                    'error_msg'     => urlencode( $this->error ),
                ), $current_url ) );
        } else {
            // Respond according to message we receive from PayPal
            $ack = strtoupper( $response['ACK'] );
            if ( $ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING' ) {
                // Redirect url to PayPal.
                $url = sprintf(
                    'https://www%s.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=%s',
                    get_option( 'bookme_paypal_sandbox' ) ? '.sandbox' : '',
                    urlencode( $response['TOKEN'] )
                );
            } else {
                $url = wp_sanitize_redirect(
                    add_query_arg( array(
                        'bookme_action' => 'paypal-ec-error',
                        'bookme_fid'    => $form_id,
                        'error_msg'     => urlencode( $response['L_LONGMESSAGE0'] ),
                    ), $current_url ) );
            }
        }
        header( 'Location: ' . $url );
        exit;
    }

    /**
     * Send the NVP Request
     *
     * @param       $method
     * @param array $data
     * @return array|null
     */
    public function send_nvp_request($method, array $data )
    {
        $paypal_response = array();
        $url  = 'https://api-3t' . ( get_option( 'bookme_paypal_sandbox' ) ? '.sandbox' : '' ) . '.paypal.com/nvp';

        $data['METHOD']    = $method;
        $data['VERSION']   = '76.0';
        $data['USER']      = get_option( 'bookme_paypal_api_username' );
        $data['PWD']       = get_option( 'bookme_paypal_api_password' );
        $data['SIGNATURE'] = get_option( 'bookme_paypal_api_signature' );

        $args = array(
            'sslverify' => false,
            'body'      => $data,
        );

        $response = wp_remote_post( $url, $args );
        if ( $response instanceof \WP_Error ) {
            $this->error = 'Invalid HTTP Response for POST request to ' . $url;
            return null;
        } else {
            // Extract the response details.
            parse_str( $response['body'], $paypal_response );

            if ( ! array_key_exists( 'ACK', $paypal_response ) ) {
                $this->error = 'Invalid HTTP Response for POST request to ' . $url;
                return null;
            }
        }

        return $paypal_response;
    }

    /**
     * Outputs HTML form for PayPal
     *
     * @param string $form_id
     */
    public static function render_ec_form($form_id )
    {
        $replacement = array(
            '%form_id%' => $form_id,
            '%gateway%' => Inc\Mains\Tables\Payment::TYPE_PAYPAL,
        );

        $form = '<form method="post" class="bookme-%gateway%-form">
                <input type="hidden" name="bookme_action" value="paypal-ec-init"/>
                <input type="hidden" name="bookme_fid" value="%form_id%"/>
             </form>';

        echo strtr( $form, $replacement );
    }

    /**
     * @param \stdClass $product
     */
    public function add_product(\stdClass $product )
    {
        $this->products[] = $product;
    }

    /**
     * Verify IPN request
     * @return bool
     */
    public static function verify_ipn()
    {
        $paypalUrl = get_option( 'bookme_paypal_sandbox' ) ?
            self::URL_POSTBACK_IPN_SANDBOX :
            self::URL_POSTBACK_IPN_LIVE;

        $raw_post_data  = file_get_contents( 'php://input' );
        $raw_post_array = explode( '&', $raw_post_data );
        $postData       = array();
        foreach ( $raw_post_array as $keyval ) {
            $keyval = explode( '=', $keyval );
            if ( count( $keyval ) == 2 ) {
                $postData[ $keyval[0] ] = urldecode( $keyval[1] );
            }
        }

        $req = 'cmd=_notify-validate';
        foreach ( $postData as $key => $value ) {
            if (
                ( function_exists( 'get_magic_quotes_gpc' ) === true )
                && ( get_magic_quotes_gpc() === 1 )
            ) {
                $value = urlencode( stripslashes( $value ) );
            } else {
                $value = urlencode( $value );
            }
            $req .= "&$key=$value";
        }

        $response = wp_safe_remote_post(
            $paypalUrl,
            array(
                'sslcertificates' => __DIR__ . '/PayPal/cert/cacert.pem',
                'body'            => $req,
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return strcmp( $response['body'], 'VERIFIED' ) === 0;
    }

    /**
     * Gets error
     *
     * @return mixed
     */
    public function get_error()
    {
        return $this->error;
    }

}