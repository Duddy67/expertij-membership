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
	$this->page['isMember'] = ($this->member->status == 'member') ? true : false;
	$this->page['isCandidate'] = ($this->member->member_since === null) ? true : false; 
	$this->page['insurance'] = $this->member->insurance->status;

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
        $paymentMode = post('payment_mode');
        $item = post('item');
        $insurance = post('insurance');
	$member = $this->loadMember();

	// The user has added the insurance to the subscription fee.
	if ($item == 'subscription' && $insurance && $insurance != 'f0') {
	    $item = 'subscription-insurance-'.$insurance;
	}
	elseif ($item == 'insurance') {
	    $item = 'insurance-'.post('code');
	}
//file_put_contents('debog_file.txt', print_r($data, true));
        if ($paymentMode == 'cheque') {
	    $data = ['mode' => 'cheque', 'item' => $item, 'amount' => Payment::getAmount($item), 'last' => 1];
	    $payment = new Payment ($data);
	    $member->payments()->save($payment);

	    Flash::success(Lang::get('codalia.membership::lang.action.cheque_payment_success'));


	    return[
		'#payment-modes' => '<div class="card bg-light mb-3"><div class="card-header">Information</div><div class="card-body">There is no payment to display.</div></div>'
	    ];
	}
	elseif ($paymentMode == 'paypal') {
	    return Redirect::to('/paypal/'.$item.'/pay-now');
	}
    }

    public function onUpdate()
    {
        parent::onUpdate();
    }
}
