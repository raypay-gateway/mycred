<?php

add_action( 'plugins_loaded', 'mycred_raypay_plugins_loaded' );

function mycred_raypay_plugins_loaded() {
    add_filter( 'mycred_setup_gateways', 'Add_RayPay_to_Gateways' );
    function Add_RayPay_to_Gateways( $installed ) {
        $installed['raypay'] = [
            'title'    => get_option( 'raypay_display_name' ) ? get_option( 'raypay_display_name' ) : __( 'RayPay payment gateway', 'mycred-raypay-gateway' ),
            'callback' => [ 'myCred_RayPay' ],
        ];
        return $installed;
    }

    add_filter( 'mycred_buycred_refs', 'Add_RayPay_to_Buycred_Refs' );
    function Add_RayPay_to_Buycred_Refs( $addons ) {
        $addons['buy_creds_with_raypay'] = __( 'RayPay Gateway', 'mycred-raypay-gateway' );

        return $addons;
    }

    add_filter( 'mycred_buycred_log_refs', 'Add_RayPay_to_Buycred_Log_Refs' );
    function Add_RayPay_to_Buycred_Log_Refs( $refs ) {
        $raypay = [ 'buy_creds_with_raypay' ];

        return $refs = array_merge( $refs, $raypay );
    }

    add_filter( 'wp_body_open', 'raypay_success_message_handler' );
    function raypay_success_message_handler( $template ){
        if( !empty( $_GET['mycred_raypay_nok'] ) )
            echo '<div class="mycred_raypay_message error">'. $_GET['mycred_raypay_nok'] .'</div>';

        if( !empty( $_GET['mycred_raypay_ok'] ) )
            echo '<div class="mycred_raypay_message success">'. $_GET['mycred_raypay_ok'] .'</div>';

        if( !empty( $_GET['mycred_raypay_nok'] ) || !empty( $_GET['mycred_raypay_ok'] ))
            echo '<style>
                .mycred_raypay_message {
                    position: absolute;
                    z-index: 9;
                    top: 40px;
                    right: 15px;
                    color: #fff;
                    padding: 15px;
                }
                .mycred_raypay_message.error {
                    background: #F44336;
                }
                .mycred_raypay_message.success {
                    background: #4CAF50;
                }
            </style>';
    }
}

spl_autoload_register( 'mycred_raypay_plugin' );

function mycred_raypay_plugin() {
    if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {
        return;
    }

    if ( ! class_exists( 'myCred_RayPay' ) ) {
        class myCred_RayPay extends myCRED_Payment_Gateway {

            function __construct( $gateway_prefs ) {
                $types            = mycred_get_types();
                $default_exchange = [];

                foreach ( $types as $type => $label ) {
                    $default_exchange[ $type ] = 1000;
                }

                parent::__construct( [
                    'id'                => 'raypay',
                    'label'             => get_option( 'raypay_display_name' ) ? get_option( 'raypay_display_name' ) : __( 'RayPay payment gateway', 'mycred-raypay-gateway' ),
                    'documentation'     => 'https://raypay.ir/Plugins',
                    'gateway_logo_url'  => plugins_url( '/assets/logo.svg', __FILE__ ),
                    'defaults'          => [
                        'user_id'            => '20064',
                        'acceptor_code'            => '220000000003751',
                        'raypay_display_name' => __( 'RayPay payment gateway', 'mycred-raypay-gateway' ),
                        'currency'           => 'rial',
                        'exchange'           => $default_exchange,
                        'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
                    ],
                ], $gateway_prefs );
            }

            public function RayPay_Iranian_currencies( $currencies ) {
                unset( $currencies );

                $currencies['rial']  = __( 'Rial', 'mycred-raypay-gateway' );
                $currencies['toman'] = __( 'Toman', 'mycred-raypay-gateway' );

                return $currencies;
            }

            function preferences() {
                add_filter( 'mycred_dropdown_currencies', [
                    $this,
                    'RayPay_Iranian_currencies',
                ] );

                $prefs = $this->prefs;
                ?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'user_id' ); ?>"><?php _e( 'User ID', 'mycred-raypay-gateway' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'user_id' ); ?>"
                                   name="<?php echo $this->field_name( 'user_id' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['user_id']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'acceptor_code' ); ?>"><?php _e( 'Acceptor Code', 'mycred-raypay-gateway' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'acceptor_code' ); ?>"
                                   name="<?php echo $this->field_name( 'acceptor_code' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['acceptor_code']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>


                <label class="subheader"
                       for="<?php echo $this->field_id( 'raypay_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'raypay_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'raypay_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['raypay_display_name'] ? $prefs['raypay_display_name'] : __( 'RayPay payment gateway', 'mycred-raypay-gateway' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->currencies_dropdown( 'currency', 'mycred-gateway-raypay-currency' ); ?>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
                <?php
            }

            public function sanitise_preferences( $data ) {
                $new_data['user_id']            = sanitize_text_field( $data['user_id'] );
                $new_data['acceptor_code']            = sanitize_text_field( $data['acceptor_code'] );
                $new_data['raypay_display_name'] = sanitize_text_field( $data['raypay_display_name'] );
                $new_data['currency']           = sanitize_text_field( $data['currency'] );
                $new_data['item_name']          = sanitize_text_field( $data['item_name'] );

                if ( isset( $data['exchange'] ) ) {
                    foreach ( (array) $data['exchange'] as $type => $rate ) {
                        if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), ['.', ',',] ) ) {
                            $data['exchange'][ $type ] = (float) '0' . $rate;
                        }
                    }
                }

                $new_data['exchange'] = $data['exchange'];
                update_option( 'raypay_display_name', $new_data['raypay_display_name'] );
                return $data;
            }

            public function process() {

                $pending_post_id = sanitize_text_field( $_REQUEST['payment_id'] );
                $org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );
                $mycred = mycred( $org_pending_payment->point_type );
                $invoice_id = sanitize_text_field( $_REQUEST['?invoiceID'] );

                if ( $invoice_id ) {

                    $data = array(
                        'order_id' => '',
                    );

                    $headers = array(
                        'Content-Type' => 'application/json',
                    );

                    $args = array(
                        'body' => json_encode($data),
                        'headers' => $headers,
                        'timeout' => 15,
                    );

                    $response = $this->call_gateway_endpoint( 'http://185.165.118.211:14000/raypay/api/v1/Payment/checkInvoice?pInvoiceID=' . $invoice_id, $args );
                    if ( is_wp_error( $response ) ) {
                        $log = $response->get_error_message();
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_raypay',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id
                        );

                        $return = add_query_arg( 'mycred_raypay_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }
                    $http_status = wp_remote_retrieve_response_code( $response );
                    $result      = wp_remote_retrieve_body( $response );
                    $result      = json_decode( $result  );

                    if ( $http_status != 200 ) {
                        $log = __( 'An error occurred while verifying the transaction.', 'mycred-raypay-gateway' );
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_raypay',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id
                        );

                        $return = add_query_arg( 'mycred_raypay_nok', $log, $this->get_cancelled() );
                        wp_redirect( $return );
                        exit;
                    }

                    $state = $result->Data->State;

                    if ( $state === 1) {
                        $message =  __( 'Payment succeeded.', 'mycred-raypay-gateway');
                        add_filter( 'mycred_run_this', function( $filter_args ) use ( $message ) {
                            return $this->mycred_raypay_success_log( $filter_args, $message );
                        } );

                        if (  $this->complete_payment( $org_pending_payment , $invoice_id ) ) {


                            $this->log_call( $pending_post_id, $message );
                            $this->trash_pending_payment( $pending_post_id );

                            $return = add_query_arg( 'mycred_raypay_ok', $message, $this->get_thankyou() );
                            wp_redirect( $return );
                            exit;
                        } else {

                            $log =__( 'An unexpected error occurred when completing the payment but it is done at the gateway.', 'mycred-raypay-gateway');
                            $this->log_call( $pending_post_id, $log );
                            $mycred->add_to_log(
                                'buy_creds_with_raypay',
                                $pending_payment->buyer_id,
                                $pending_payment->amount,
                                $log,
                                $pending_payment->buyer_id,
                                $result
                            );

                            $return = add_query_arg( 'mycred_raypay_nok', $log, $this->get_cancelled() );
                            wp_redirect( $return );
                            exit;
                        }
                    }

                    $log = __( 'Payment failed.', 'mycred-raypay-gateway' );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_raypay',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $result
                    );

                    $return = add_query_arg( 'mycred_raypay_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;

                } else {
                    $log= 'Error';
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_raypay',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id
                    );

                    $return = add_query_arg( 'mycred_raypay_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    exit;
                }
            }

            public function returning() {}

            public function mycred_raypay_success_log( $request, $log ){
                if( $request['ref'] == 'buy_creds_with_raypay' )
                    $request['entry'] = $log;

                return $request;
            }
            /**
             * Prep Sale
             *
             * @since   1.8
             * @version 1.0
             */
            public function prep_sale( $new_transaction = FALSE )
            {

                // Point type
                $type = $this->get_point_type();
                $mycred = mycred($type);

                // Amount of points
                $amount = $mycred->number($_REQUEST['amount']);

                // Get cost of that points
                $cost = $this->get_cost($amount, $type);
                $cost = abs($cost);

                $to = $this->get_to();
                $from = $this->current_user_id;

                // Revisiting pending payment
             //   if (isset($_REQUEST['revisit'])) {
             //      $this->transaction_id = strtoupper($_REQUEST['revisit']);
            //    } else {
                    $post_id = $this->add_pending_payment([
                        $to,
                        $from,
                        $amount,
                        $cost,
                        $this->prefs['currency'],
                        $type,
                    ]);
                    $this->transaction_id = get_the_title($post_id);
          //     }


                    $is_ajax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) ? true : false;
                    $callback = add_query_arg('payment_id', $this->transaction_id, $this->callback_url());
                    $callback .= "&";
                    $user_id = $this->prefs['user_id'];
                    $acceptor_code = $this->prefs['acceptor_code'];
                    $invoice_id = round(microtime(true) * 1000);


                    $data = array(
                        'amount' => strval($amount),
                        'invoiceID' => strval($invoice_id),
                        'userID' => $user_id,
                        'redirectUrl' => $callback,
                        'factorNumber' => strval($this->transaction_id),
                        'acceptorCode' => $acceptor_code,
                    );

                    $headers = array(
                        'Content-Type' => 'application/json',
                    );

                    $args = array(
                        'body' => json_encode($data),
                        'headers' => $headers,
                        'timeout' => 15,
                    );
                    $response = $this->call_gateway_endpoint('http://185.165.118.211:14000/raypay/api/v1/Payment/getPaymentTokenWithUserID', $args);
                    if (is_wp_error($response)) {
                        $error = $response->get_error_message();
                        $mycred->add_to_log(
                            'buy_creds_with_raypay',
                            $from,
                            $amount,
                            $error,
                            $from,
                            $data,
                            'point_type_key'
                        );

                        if ($is_ajax) {
                            $this->errors[] = $error;
                        } else if (empty($_GET['raypay_error'])) {
                            wp_redirect($_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&raypay_error=' . $error);
                            exit;
                        }
                    }

                    $http_status = wp_remote_retrieve_response_code($response);
                    $result = wp_remote_retrieve_body($response);
                    $result = json_decode($result);


                    if ($http_status != 200 || empty($result) || empty($result->Data)) {
                        $error = 'Error';
                        if (!empty($result->Message)) {
                            $error = $result->Message;
                        }
                        $mycred->add_to_log(
                            'buy_creds_with_raypay',
                            $from,
                            $amount,
                            $error,
                            $from,
                            $data,
                            'point_type_key'
                        );

                        if ($is_ajax) {
                            $this->errors[] = $error;

                        } else if (empty($_GET['raypay_error'])) {
                            wp_redirect($_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&raypay_error=' . $error);
                            exit;
                        }

                    }

                    $access_token = $result->Data->Accesstoken;
                    $terminal_id = $result->Data->TerminalID;

                    $item_name = str_replace('%number%', $this->amount, $this->prefs['item_name']);
                    $item_name = $this->core->template_tags_general($item_name);

                    $redirect_fields = [
                        //'pay_to_email'        => $this->prefs['account'],
                        'transaction_id' => $this->transaction_id,
                        'return_url' => $this->get_thankyou(),
                        'cancel_url' => $this->get_cancelled($this->transaction_id),
                        'status_url' => $this->callback_url(),
                        'return_url_text' => get_bloginfo('name'),
                        'hide_login' => 1,
                        'merchant_fields' => 'sales_data',
                        'sales_data' => $this->post_id,
                        'amount' => $this->cost,
                        'currency' => $this->prefs['currency'],
                        'detail1_description' => __('Item Name', 'mycred'),
                        'detail1_text' => $item_name,
                    ];

                    // Customize Checkout Page
                    if (isset($this->prefs['account_title']) && !empty($this->prefs['account_title'])) {
                        $redirect_fields['recipient_description'] = $this->core->template_tags_general($this->prefs['account_title']);
                    }

                    if (isset($this->prefs['account_logo']) && !empty($this->prefs['account_logo'])) {
                        $redirect_fields['logo_url'] = $this->prefs['account_logo'];
                    }

                    if (isset($this->prefs['confirmation_note']) && !empty($this->prefs['confirmation_note'])) {
                        $redirect_fields['confirmation_note'] = $this->core->template_tags_general($this->prefs['confirmation_note']);
                    }

                    // If we want an email receipt for purchases
                    if (isset($this->prefs['email_receipt']) && !empty($this->prefs['email_receipt'])) {
                        $redirect_fields['status_url2'] = $this->prefs['account'];
                    }

                    // Gifting
                    if ($this->gifting) {
                        $user = get_userdata($this->recipient_id);
                        $redirect_fields['detail2_description'] = __('Recipient', 'mycred');
                        $redirect_fields['detail2_text'] = $user->display_name;
                    }

                    $this->redirect_fields = $redirect_fields;
                    $this->mycred_raypay_send_data_shaparak($access_token , $terminal_id);
                    //$this->redirect_to = empty($_GET['raypay_error']) ? $result->link : $_SERVER['REQUEST_URI'];
                }


            /**
             * AJAX Buy Handler
             *
             * @since   1.8
             * @version 1.0
             */
            public function ajax_buy() {
                // Construct the checkout box content
                $content = $this->checkout_header();
                $content .= $this->checkout_logo();
                $content .= $this->checkout_order();
                $content .= $this->checkout_cancel();
                $content .= $this->checkout_footer();

                // Return a JSON response
                $this->send_json( $content );
            }

            /**
             * Checkout Page Body
             * This gateway only uses the checkout body.
             *
             * @since   1.8
             * @version 1.0
             */
            public function checkout_page_body() {
                echo $this->checkout_header();
                echo $this->checkout_logo( FALSE );
                echo $this->checkout_order();
                echo $this->checkout_cancel();
                if( !empty( $_GET['raypay_error'] ) ){
                    echo '<div class="alert alert-error raypay-error">'. $_GET['raypay_error'] .'</div>';
                    echo '<style>
                        .checkout-footer, .raypay-logo, .checkout-body > img {display: none;}
                        .raypay-error {
                            background: #F44336;
                            color: #fff;
                            padding: 15px;
                            margin: 10px 0;
                        }
                    </style>';
                }
                else {
                    echo '<style>.checkout-body > img {display: none;}</style>';
                }
                echo $this->checkout_footer();
                echo sprintf(
                    '<span class="raypay-logo" style="font-size: 12px;padding: 5px 0;"><img src="%1$s" style="display: inline-block;vertical-align: middle;width: 70px;">%2$s</span>',
                    plugins_url( '/assets/logo.svg', __FILE__ ), __( 'Pay with RayPay', 'mycred-raypay-gateway' )
                );

            }

            /**
             * Calls the gateway endpoints.
             *
             * Tries to get response from the gateway for 4 times.
             *
             * @param $url
             * @param $args
             *
             * @return array|\WP_Error
             */
            private function call_gateway_endpoint( $url, $args ) {
                $number_of_connection_tries = 2;
                while ( $number_of_connection_tries ) {
                    $response = wp_remote_post( $url, $args );
                    if ( is_wp_error( $response ) ) {
                        $number_of_connection_tries --;
                        continue;
                    } else {
                        break;
                    }
                }
                return $response;
            }

            public function mycred_raypay_send_data_shaparak($access_token , $terminal_id){
                echo '<form name="frmRayPayPayment" method="post" action=" https://mabna.shaparak.ir:8080/Pay ">';
                echo '<input type="hidden" name="TerminalID" value="' . $terminal_id . '" />';
                echo '<input type="hidden" name="token" value="' . $access_token . '" />';
                echo '<input class="submit" type="submit" value="پرداخت" /></form>';
                echo '<script>document.frmRayPayPayment.submit();</script>';
            }
    }
}
}
