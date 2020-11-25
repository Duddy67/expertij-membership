<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberItem;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Helpers\EmailHelper;
use Auth;
use Input;
use Validator;
use ValidationException;
use Flash;
use Lang;
use Redirect;
use System\Models\File;


class Account extends \Codalia\Profile\Components\Account
{
    public $member;

    public function componentDetails()
    {
        return [
            'name'        => 'Account Membership Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
	$this->member = $this->page['member'] = $this->loadMember();
	// Sets the payment flag.
	$isPayment = false;
	if ($this->member->status == 'pending_subscription' || $this->member->status == 'pending_renewal') {
	    $isPayment = true;
	}

	// The user has paid by cheque.
	if ($isPayment && $this->member->payments()->where([['status', 'pending'], ['mode', 'cheque']])->first()) {
	    // The payment form is no longer necessary.
	    $isPayment = false;
	}

	$this->page['isPayment'] = $isPayment;

//$data = ['status' => 'completed', 'mode' => 'paypal', 'item' => 'subscription', 'amount' => '1.00'];    
//EmailHelper::instance()->alertPayment(1, $data);
        parent::prepareVars();
    }

    protected function loadMember()
    {
        // Gets the current user.
        $user = Auth::getUser();
	// Loads the corresponding member through the profile_id attribute.
	$profileId = Profile::where('user_id', $user->id)->pluck('id');
	$member = new MemberItem;
	$member = $member->where('profile_id', $profileId);

	if (($member = $member->first()) === null) {
	    return null;
	}

	//var_dump($member->name);
	return $member;
    }

    public function onReplaceFile()
    {
        $member = $this->loadMember();

	if (Input::hasFile('attestation')) {
	    $member->attestations = Input::file('attestation');
	    $member->save();
	}

        Flash::success(Lang::get('codalia.membership::lang.action.file_replace_success'));
    }

    public function onUploadDocument()
    {
        $input = Input::all();

        $file = (new File())->fromPost($input['attestation']);

        return[
            '#newFile' => '<a class="btn btn-danger btn-lg" target="_blank" href="'.$file->getPath().'"><span class="glyphicon glyphicon-download"></span>Download</a>'
        ];
    }

    public function onPayment()
    {
        $data = post('payment_mode');
	$member = $this->loadMember();
//file_put_contents('debog_file.txt', print_r($data, true));
        if ($data == 'cheque') {
	    $data = ['mode' => 'cheque', 'item' => 'subscription', 'amount' => Settings::get('subscription_fee', 0), 'last' => 1];
	    $payment = new Payment ($data);
	    $member->payments()->save($payment);

	    Flash::success(Lang::get('codalia.membership::lang.action.cheque_payment_success'));

	    EmailHelper::instance()->chequePayment($member);

	    return[
		'#payment-modes' => '<div class="card bg-light mb-3"><div class="card-header">Information</div><div class="card-body">There is no payment to display.</div></div>'
	    ];
	}
	elseif ($data == 'paypal') {
	    return Redirect::to('/paypal/subscription/pay-now');
	}
    }

    public function onUpdate()
    {
        parent::onUpdate();
    }
}
