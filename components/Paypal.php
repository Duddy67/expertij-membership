<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Payment;
use Auth;
use Lang;


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

    public function loadMember($userId)
    {
	// Loads the corresponding member through the profile_id attribute.
	$profileId = Profile::where('user_id', $userId)->pluck('id');
	$member = new Member;
	$member = $member->where('profile_id', $profileId);

	return $member->first();
    }

    public function onRun()
    {
	$path = explode('/', $this->currentPageUrl());
	$item = $path[count($path) - 2];
	$action = end($path);

	$this->page['item'] = $item; 
	$this->page['action'] = $action; 
	$this->page['paypalId'] = $this->property('paypal_id'); 
	$this->page['paypalUrl'] = $this->property('paypal_url'); 
	$this->page['amount'] = Payment::getAmount($item);

	if ($action == 'notify') {
	    $this->onNotify();
	}
	else {
	    // Gets the current user.
	    $user = Auth::getUser();
	    $this->page['userId'] = $user->id;

	    $this->page['itemName'] = Lang::get('codalia.membership::lang.payment.'.$item);
	}
    }

    /*
     * Source: https://phppot.com/php/paypal-payment-gateway-integration-in-php
     */
    public function onNotify()
    {
	// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
	// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
	// Set this to 0 once you go live or don't require logging.
	define('DEBUG', 1);
	// Set to 0 once you're ready to go live
	define('LOG_FILE', 'plugins/codalia/membership/components/paypal/logs/ipn.log');
	$separator = PHP_EOL.'###################################### INFORMATION ######################################'.PHP_EOL;
	$end = PHP_EOL.'###################################### END OF THE PROCESS ######################################'.PHP_EOL;

	// Reading posted data directly from $_POST causes serialization
	// issues with array data in POST. Reading raw POST data from input stream instead.
	$raw_post_data = file_get_contents('php://input');
	$raw_post_array = explode('&', $raw_post_data);
	$myPost = array();

	foreach ($raw_post_array as $keyval) {
	    $keyval = explode ('=', $keyval);

	    if (count($keyval) == 2) {
		$myPost[$keyval[0]] = urldecode($keyval[1]);
	    }
	}

	// Checks that txn_id has not been previously processed.
	if (Payment::transactionIdExists('paypal', $myPost['txn_id'])) {
	    // N.B: Paypal works like shit and keeps sending ipn despite the empty 200 response sent after closing curl.
	    //      So no need to waste time by manage this as an error etc... Just send again an empty 200 response (just in case) and quit the function. 
	    header("HTTP/1.1 200 OK");

	    return;
	}

	error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "raw_post_data: $raw_post_data" . PHP_EOL, 3, LOG_FILE);

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($myPost as $key => $value) {
	    $value = urlencode($value);
	    $req .= "&$key=$value";
	}

	// Post IPN data back to PayPal to validate the IPN data is genuine.
	// Without this step anyone can fake IPN data.
	$paypal_url = $this->page['paypalUrl'];

	$ch = curl_init($paypal_url);

	if ($ch == false) {
	    error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "Curl failed to init ! url: $paypal_url" . PHP_EOL.$end, 3, LOG_FILE);
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

	if (curl_errno($ch) != 0) { // cURL error
	    if (DEBUG == true) {
		error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "Can't connect to PayPal to validate IPN message: ".curl_error($ch).PHP_EOL.$end, 3, LOG_FILE);
	    }

	    curl_close($ch);

	    exit;
	}
	else {
	  // Log the entire HTTP response if debug is switched on.
	  if (DEBUG == true) {
	      error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
	      error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "HTTP response of validation request: $result" . PHP_EOL, 3, LOG_FILE);
	  }

	  curl_close($ch);
          // IMPORTANT: Send back an empty 200 response or PayPal will keep sending ipn to the application.
	  header("HTTP/1.1 200 OK");
	}

	// Inspect IPN validation result and act accordingly
	// Split response headers and payload, a better way for strcmp
	$tokens = explode("\r\n\r\n", trim($result));
	$result = trim(end($tokens));
	$post = post();
	// Retrieves the member id and the item code from the custom string.
	preg_match('#^([0-9]+)\-([a-z\-0-9]+)#', $post['custom'], $matches);
	$memberId = $matches[1];
	$item = $matches[2];
	// Prepares variables.
	$vars = ['mode' => 'paypal', 'item' => $item, 'amount' => $post['mc_gross'], 'currency' => $post['mc_currency'], 'transaction_id' => $post['txn_id'], 'last' => 1];
	$message = '';

	if(strcmp ($result, "VERIFIED") == 0) {
	  // check that receiver_email is your PayPal email
	  if ($post['receiver_email'] != $this->page['paypalId']) {
	      $vars['status'] = 'error';
	      $message .= 'Wrong receiver email ! ';
	  }

	  // check that payment_amount/payment_currency are correct
	  if ($post['mc_currency'] != 'EUR') {
	  }

	  if ($post['payment_status'] == 'Completed') {
	      if (!isset($vars['status'])) {
		  $vars['status'] = 'completed';
		  $message = 'The payment has been completed successfully !';
	      }
	  }
	  else {
	      // See: https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNandPDTVariables for the available options.
	  }

	  $vars['message'] = $message;
	  $vars['data'] = 'Verified IPN: '.$req;

	  // process payment and mark item as paid.

	  if (DEBUG == true) {
	      error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "Verified IPN: $req ". PHP_EOL.$end, 3, LOG_FILE);
	  }
	}
	else if (strcmp($result, "INVALID") == 0) {
	    // log for manual investigation
	    // Add business logic here which deals with invalid IPN messages
	    $vars['status'] = 'error';
	    $vars['message'] = 'Invalid IPN !';
	    $vars['data'] = 'Invalid IPN: '.$req;
	    
	    if (DEBUG == true) {
		error_log($separator.PHP_EOL.date('[Y-m-d H:i:s e] '). "Invalid IPN: $req" . PHP_EOL.$end, 3, LOG_FILE);
	    }
	}

	$member = $this->loadMember($memberId);
	$member->savePayment($vars);
    }
}
