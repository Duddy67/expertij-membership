<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Payment;
use Redirect;
use Auth;
use Flash;
use Lang;


class Sherlocks extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Sherlocks Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
      return [
            'merchant_id' => [
                'title'       => 'codalia.membership::lang.settings.merchant_id',
                'description' => 'codalia.membership::lang.settings.merchant_id_description',
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

        // If the user session is still available check for the honorary_member variable. 
        // If not it doesn't matter as the correct amount has already been sent to the
        // bank. It can therefore be set to false.
        $honoraryMember = ($user = Auth::getUser()) ? $user->profile->honorary_member : false;

	$this->page['item'] = $item; 
	$this->page['action'] = $action; 
	$this->page['merchantId'] = $this->property('merchant_id'); 
	$this->page['paypalUrl'] = $this->property('paypal_url'); 
	$this->page['amount'] = Payment::getAmount($item, $honoraryMember);

	if ($action == 'pay-now') {
	    $this->page['result'] = $this->payNow();
	}
	elseif ($action == 'auto-response') {
	    $this->autoResponse();
	}
	elseif ($action == 'response') {
	    $this->response();
	    return Redirect::to('membership');
	}
	elseif ($action == 'cancel') {
	    return Redirect::to('membership');
	}
	else {
	    return Redirect::to('404');
	}
    }

    public function payNow()
    {
	$path = base_path();

        include $path.'/sherlocks/code/call_request.php';

	return $return;
    }

    public function autoResponse()
    {
	$path = base_path();

        include $path.'/sherlocks/code/call_autoresponse.php';

	if ($results['code'] == 0) {
	    $results['raw_data'] = $result;
	    $this->setPayment($results);
	}
	else {
	    // An system error has occured. 
	    // The error message has already been written down in the log file.
	}
    }

    public function response()
    {
	$path = base_path();

        include $path.'/sherlocks/code/call_response.php';

	if ($results['code'] == 0) {
	    $results['raw_data'] = $result;
	    $this->setPayment($results);
	}
	else {
	    // An system error has occured. 
	    // The error message has already been written down in the log file by the
	    // call_autoresponse.php file.
	}

	if (!\Session::has('sherlocks_results')) {
	    // Stores the result variable.
	    \Session::put('sherlocks_results', $results);
	}
    }

    protected function setPayment($results)
    {
        // Ensure first that the payment has not been stored already.
        if (!Payment::getPayment('sherlocks', $results['transaction_id'])) {
	    // Prepares variables.
	    $amount = (int)$results['amount'] / 100;
	    $status = ($results['bank_response_code'] == '00') ? 'completed' : 'error';
	    $vars = ['status' => $status, 'mode' => 'sherlocks', 'item' => $results['caddie'], 'amount' => $amount,
		     'currency' => $results['currency_code'], 'transaction_id' => $results['transaction_id'],
		     'data' => $results['raw_data'], 'last' => 1];

	    $member = $this->loadMember((int)$results['customer_id']);
	    $member->savePayment($vars);
	}
    }
}
