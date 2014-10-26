<?php
App::uses('Component', 'Controller');

class PaypalComponent extends Component {
    public $components = array('Acl');
    protected $endpoint;
    protected $credentials;
    protected $token;

    public function initialize(Controller $controller) {
        $this->endpoint = Configure::read('Store.Paypal.EndPoint');
        $this->credentials = ConnectionManager::enumConnectionObjects()['paypal'];
    }

    /**
     * Queries PayPal for an access token and returns it.
     *
     * @return mixed token
     * @throws Exception
     */
    protected function getAccessToken() {

        if ( !empty($this->token) ) {
            CakeLog::write('paypal', 'PayPal token found on class and used.');
            return $this->token;
        }

        $token = Cache::read('paypaltoken', 'paypal');

        if ( !empty($token) ) {
            CakeLog::write('paypal', 'PayPal token found in cache and used.');
            $this->token = $token;
            return $token;
        }

        $header = array('Accept: application/json', 'Accept-Language: en_US');

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, "https://{$this->endpoint}/v1/oauth2/token" );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch, CURLOPT_USERPWD, "{$this->credentials['clientid']}:{$this->credentials['secret']}" );

        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials' );
        $data = curl_exec($ch);
        curl_close($ch);

        if( empty($data) ) {
            CakeLog::write('paypal_error', 'Empty access token returned.');
            throw new Exception('PayPal Error');
        }

        $json = json_decode( $data );

        $json->timestamp = time();
        Cache::write('paypaltoken', $json, 'paypal');
        CakeLog::write('paypal', 'New PayPal token obtained and saved.');

        $this->token = $json;

        return $json;
    }

    /**
     * Queries PayPal to create a payment for the specified amount.
     *
     * @param string $returnURL the URL to return to when the purchase is completed
     * @param string $cancelURL the URL to return to when the purchase is cancelled
     * @param double $price the amount to charge
     * @return mixed
     * @throws Exception
     */
    public function createPayment($returnURL, $cancelURL, $price) {

        $access = $this->getAccessToken()->access_token;

        $price = sprintf( '%d.%02d', $price / 100, $price % 100 );

        $header = array( "Authorization: Bearer $access",'Content-Type: application/json', 'Accept: application/json' );

        $curl = curl_init( "https://{$this->endpoint}/v1/payments/payment" );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header );

        $pay = array(
            'intent' => 'sale',
            'redirect_urls' => array(
                'return_url' => $returnURL,
                'cancel_url' => $cancelURL
            ),
            'payer' => array(
                'payment_method' => 'paypal'
            ),
            'transactions' => array(
                array(
                    'amount' => array(
                        'total' => $price,
                        'currency' => 'USD',
                        'details' => array(
                            'subtotal' => $price,

                        )
                    ),
                    'item_list' => array(
                        'items' => array()
                    )
                )
            )
        );

        $pay['transactions'][0]['item_list']['items'][] = array(
            'quantity' => 1,
            'name' => 'CASH',
            'price' => $price,
            'currency' => 'USD'
        );

        $postdata = json_encode( $pay );

        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        $response = curl_exec( $curl );

        if (empty($response)) {
            curl_close($curl);
            CakeLog::write('paypal_error', 'Empty response while creating payment.');
            throw new Exception('PayPal Error');
        }

        curl_close($curl);
        $jsonResponse = json_decode($response);

        if( !isset($jsonResponse->state) || $jsonResponse->state != 'created' ) {
            CakeLog::write('paypal_error', 'Response state missing or incorrect.');
            throw new Exception('PayPal Error');
        }

        CakeLog::write('paypal', 'Payment created successfully.');

        return $jsonResponse;
    }

    /**
     * Executes a confirmed payment. This should be called by the action that corresponds to the confirm URL.
     *
     * @param mixed $payment the payment sent by the PayPal response after confirmation
     * @param string $payerid
     * @return mixed
     * @throws Exception
     */
    public function executePayment( $payment, $payerid ) {

        $access = $this->getAccessToken()->access_token;

        $header = array( "Authorization: Bearer $access", 'Content-Type: application/json', 'Accept: application/json' );

        $url = $this->findExecuteUrl( $payment );
        $curl = curl_init( $url );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header );

        $postdata = json_encode( array( 'payer_id' => $payerid ) );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);

        $response = curl_exec( $curl );

        if (empty($response)) {
            curl_close($curl);
            CakeLog::write('paypal_error', 'Empty response while executing payment.');
            throw new Exception('PayPal Error');
        }

        curl_close($curl);
        $jsonResponse = json_decode($response);

        CakeLog::write('paypal', 'Payment executed successfully.');

        return $jsonResponse;
    }

    /**
     * Parses a payment and pulls out the approval URL.
     *
     * @param mixed $payment
     * @return mixed
     * @throws Exception
     */
    public function findApprovalUrl( $payment ) {

        foreach( $payment->links as $p ) {
            if( $p->rel == 'approval_url' ) {
                return $p->href;
            }
        }

        CakeLog::write('paypal_error', 'No approval URL found in payment.');
        throw new Exception( "PayPal Error: No Approval URL" );
    }

    /**
     * Parses a payment and pulls out the execute URL.
     *
     * @param $payment
     * @return mixed
     * @throws Exception
     */
    public function findExecuteUrl( $payment ) {

        foreach( $payment->links as $p ) {
            if( $p->rel == 'execute' ) {
                return $p->href;
            }
        }

        CakeLog::write('paypal_error', 'No execute URL found in payment.');
        throw new Exception( 'PayPal Error: No Execute URL' );
    }
}