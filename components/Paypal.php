<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Settings;
use Auth;


class Paypal extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Paypal Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
	return [
            'paypal_id' => [
                'title'       => 'codalia.membership::lang.settings.paypal_id',
                'description' => 'codalia.membership::lang.settings.paypal_id_description',
                'default'     => '',
                'type'        => 'string',
	    ],
            'paypal_url' => [
                'title'       => 'codalia.membership::lang.settings.paypal_url',
                'description' => 'codalia.membership::lang.settings.paypal_url_description',
                'default'     => '',
                'type'        => 'string',
	    ],
	];
    }

    public function init()
    {
    }

    public function onRun()
    {
	$path = explode('/', $this->currentPageUrl());
	$product = $path[count($path) - 2];
	$action = end($path);

	$this->page['product'] = $product; 
	$this->page['action'] = $action; 
	$this->page['paypal_id'] = $this->property('paypal_id'); 
	$this->page['paypal_url'] = $this->property('paypal_url'); 
	$this->page['subscription_fee'] = Settings::get('subscription_fee', 0);

	if ($action == 'notify') {
	    $this->onNotify();
	}
	else {
	    // Gets the current user.
	    $user = Auth::getUser();
	    $this->page['user_id'] = $user->id;
	}

      //$postData = $this->getPostData($product);
    }

    public function onNotify()
    {
	// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
	// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
	// Set this to 0 once you go live or don't require logging.
	define('DEBUG', 1);
	// Set to 0 once you're ready to go live
	define('LOG_FILE', 'ipn.log');

	// Reading posted data directly from $_POST causes serialization
	// issues with array data in POST. Reading raw POST data from input stream instead.
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();
file_put_contents('debog_file_notify.txt', print_r($raw_post_array, true));

	foreach ($raw_post_array as $keyval) {
	    $keyval = explode ('=', $keyval);

	    if (count($keyval) == 2) {
		$myPost[$keyval[0]] = urldecode($keyval[1]);
	    }
	}

	error_log(date('[Y-m-d H:i e] '). "raw_post_data: $raw_post_data" . PHP_EOL, 3, LOG_FILE);

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	if (function_exists('get_magic_quotes_gpc')) {
	    $get_magic_quotes_exists = true;
	}

	foreach ($myPost as $key => $value) {
	    if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		$value = urlencode(stripslashes($value));
	    }
	    else {
		$value = urlencode($value);
	    }

	    $req .= "&$key=$value";
	}

	// Post IPN data back to PayPal to validate the IPN data is genuine.
	// Without this step anyone can fake IPN data.

	$paypal_url = $this->page['paypal_url'];

	$ch = curl_init($paypal_url);

	if ($ch == false) {
	    return false;
	}

	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

	if (DEBUG == true) {
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
	}

	// CONFIG: Optional proxy configuration
	//curl_setopt($ch, CURLOPT_PROXY, $proxy);
	//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
	// Set TCP timeout to 30 seconds
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

	// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
	// of the certificate as shown below. Ensure the file is readable by the webserver.
	// This is mandatory for some environments.
	//$cert = __DIR__ . "./cacert.pem";
	//curl_setopt($ch, CURLOPT_CAINFO, $cert);
	$result = curl_exec($ch);

file_put_contents('debog_file_result.txt', print_r($result, true));
	if (curl_errno($ch) != 0) { // cURL error
	    if (DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
	    }

	    curl_close($ch);

	    exit;
	}
	else {
	  // Log the entire HTTP response if debug is switched on.
	  if (DEBUG == true) {
	      error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
	      error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $result" . PHP_EOL, 3, LOG_FILE);
	  }

	  curl_close($ch);
	}

	// Inspect IPN validation result and act accordingly
	// Split response headers and payload, a better way for strcmp
	$tokens = explode("\r\n\r\n", trim($result));
	$result = trim(end($tokens));

	if(strcmp ($result, "VERIFIED") == 0) {
	  // assign posted variables to local variables
	  $item_name = $_POST['item_name'];
	  $item_number = $_POST['item_number'];
	  $payment_status = $_POST['payment_status'];
	  $payment_amount = $_POST['mc_gross'];
	  $payment_currency = $_POST['mc_currency'];
	  $txn_id = $_POST['txn_id'];
	  $receiver_email = $_POST['receiver_email'];
	  $payer_email = $_POST['payer_email'];

	  //include("DBController.php");
	  //$db = new DBController();

	  // check whether the payment_status is Completed
	  $isPaymentCompleted = false;
    file_put_contents('debog_file_verified.txt', print_r($_POST, true));
	  if($payment_status == "Completed") {
	    $isPaymentCompleted = true;
	  }

	  // check that txn_id has not been previously processed
	  $isUniqueTxnId = false;
	  //$param_type="s";
	  //$param_value_array = array($txn_id);

	  //$result = $db->runQuery("SELECT * FROM payment WHERE txn_id = ?",$param_type,$param_value_array);

	  /*if(empty($result)) {
	    $isUniqueTxnId = true;
	  }*/

	  // check that receiver_email is your PayPal email
	  // check that payment_amount/payment_currency are correct
	  if ($isPaymentCompleted) {

	    //$param_type = "sssdss";
	    //$param_value_array = array($item_number, $item_name, $payment_status, $payment_amount, $payment_currency, $txn_id);
	    //$payment_id = $db->insert("INSERT INTO payment(item_number, item_name, payment_status, payment_amount, payment_currency, txn_id) VALUES(?, ?, ?, ?, ?, ?)", $param_type, $param_value_array);
	  }

	  // process payment and mark item as paid.

	  if (DEBUG == true) {
	      error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
	  }
	}
	else if (strcmp($result, "INVALID") == 0) {
	    // log for manual investigation
	    // Add business logic here which deals with invalid IPN messages
	    if (DEBUG == true) {
		error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
	    }
	}
    }

    public function getPostData($product)
    {
	$postData = ['business' => $this->property('paypal_id'),
		     'cmd' => '_xclick',
		     'item_name' => $product,
		     'item_number' => $product.'01',
		     'amount' => Settings::get('subscription_fee', 0),
		     'currency_code' => 'EUR',
		     'notify_url' => url('/').'/paypal/'.$product.'/notify',
		     'return' => url('/').'/paypal/'.$product.'/return',
		     'cancel_return' => url('/').'/paypal/'.$product.'/cancel'
	];

	return $postData;
    }
}
