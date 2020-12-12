<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberItem;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Models\Document;
use Codalia\Membership\Helpers\EmailHelper;
use Auth;
use Input;
use Validator;
use ValidationException;
use Flash;
use Lang;
use Redirect;
use System\Models\File;


class Member extends ComponentBase
{
    public $member;
    public $documents;

    public function componentDetails()
    {
        return [
            'name'        => 'Member Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function onRun()
    {
	$this->prepareVars();
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
	$this->page['insuranceName'] = Lang::get('codalia.membership::lang.global_settings.insurance_'.$this->member->insurance->code);
	$this->page['status'] = $this->member->status;
	$this->page['memberList'] = $this->member->member_list;

	$this->page['documents'] = $this->loadDocuments($this->member->categories);
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

    protected function loadDocuments($categories)
    {
        $catIds = $categories->pluck('id')->toArray();
	// Gets only documents which match the member's categories.
	$documents = Document::where('status', 'published')->whereHas('categories', function($query) use($catIds) {
	    $query->whereIn('id', $catIds);
	})->get();

	return $documents;
    }

    public function onReplaceFile()
    {
        $rules = (new MemberItem)->rules;

	$messages = [
	    'attestation.required_if' => 'The :attribute field is required.',
	];

	$validation = Validator::make(Input::all(), $rules, $messages);
	if ($validation->fails()) {
	    throw new ValidationException($validation);
	}

        $member = $this->loadMember();

	if (Input::hasFile('attestation')) {
	    $member->attestations = Input::file('attestation');
	    $member->forceSave();
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
        $insuranceCode = post('insurance_code');
	$member = $this->loadMember();

	// The user has added the insurance to the subscription fee.
	if ($item == 'subscription' && $insuranceCode && $insuranceCode != 'f0') {
	    $item = 'subscription-insurance-'.$insuranceCode;
	}
	elseif ($item == 'insurance') {
	    $item = 'insurance-'.post('code');
	}

        if ($paymentMode == 'cheque') {
	  $data = ['mode' => 'cheque', 'status' => 'pending', 'item' => $item, 'amount' => Payment::getAmount($item),
		   'currency' => 'EUR', 'transaction_id' => uniqid('CHQ'), 'last' => 1];
	    $member->savePayment($data);

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
	/*$data = post();
        $rules = (new MemberItem)->rules;

	$validation = Validator::make($data, $rules);
	if ($validation->fails()) {
	    throw new ValidationException($validation);
	}*/
        parent::onUpdate();
    }

    public function onMemberList()
    {
        $memberList = (int)post('member_list');
	$member = $this->loadMember();
	$member->update(['member_list' => $memberList]);

	Flash::success(Lang::get('codalia.membership::lang.action.member_list_'.$memberList));
    }

    public function onCancellation()
    {
	$member = $this->loadMember();
	$member->cancelMembership('cancellation');

	Flash::success(Lang::get('codalia.membership::lang.action.cancellation_success'));

	return[
	    '#member-space' => '<div class="card bg-light mb-3"><div class="card-header">Information</div><div class="card-body">Your membership has been cancelled.</div></div>'
	];

    }
}