<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
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

    /**
     * @var array Matching between file types and model relationships.
     */
    public $relationships = [
	'attestation' => 'attestations', 'photo' => 'photo'
    ];

    /**
     * @var array Matching between file types and rules.
     */
    public $fileRules = [
	'attestation' => 'required|mimes:pdf', 'photo' => 'required|mimes:jpg,jpeg,png'
    ];


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
	if (!$this->member = $this->page['member'] = $this->loadMember()) {
	    return Redirect::to('403');
	}

	// Sets the payment flag.
	$payment = false;
	if ($this->member->status == 'pending_subscription' || $this->member->status == 'pending_renewal') {
	    $payment = true;
	}

	// The user has paid by cheque.
	if ($payment && $this->member->payments()->where([['status', 'pending'], ['mode', 'cheque']])->first()) {
	    // The payment form is no longer necessary.
	    $payment = false;
	}

	$hostedFields = MemberModel::getHostedFields();

	foreach ($hostedFields as $key => $value) {
	    // Ensures a language variable is available.
	    if (!is_array($value) && strpos($value, '::lang') !== false) {
		// Replaces the language variable with the actual label.
		$hostedFields[$key] = Lang::get($value);
	    }
	}

	$this->page['flags'] = ['payment' => $payment, 'candidate' => ($this->member->member_since === null) ? true : false,
				'freePeriod' => ($this->member->free_period && $this->member->member_since) ? true : false];
	$this->page['documents'] = $this->loadDocuments($this->member->categories);
	$this->page['hostedFields'] = $hostedFields;
	$this->page['years'] = Profile::getYears();
	$this->page['categoryIds'] = $this->member->categories->pluck('id')->toArray();
	$this->page['texts'] = $this->getTexts();
    }

    protected function loadMember()
    {
        // Gets the current user.
        $user = Auth::getUser();
	// Loads the corresponding member through the profile_id attribute.
	$profileId = Profile::where('user_id', $user->id)->pluck('id');
	$member = new MemberModel;
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

    protected function getTexts()
    {
	$texts = ['member_status' => Lang::get('codalia.membership::lang.status.'.$this->member->status),
		  'member_insurance' => Lang::get('codalia.membership::lang.global_settings.insurance_'.$this->member->insurance->code)
	];

	$codes = ['profile.appeal_court', 'profile.attestation', 'profile.categories', 'profile.select', 'profile.liberal_profession', 'attribute.status'];

	foreach ($codes as $code) {
	    $key = substr($code, strpos($code, '.') + 1);
	    $texts[$key] = Lang::get('codalia.membership::lang.'.$code);
	}

	return $texts;
    }

    public function onReplaceAttestation()
    {
        $file = null;

	if (Input::hasFile('attestation')) {
	    $file = (new File())->fromPost(Input::file('attestation'));
	    $member = $this->loadMember();

	    $member->attestation()->add($file);
	    $member->forceSave();
	}
	else {
	    return;
	}

        Flash::success(Lang::get('codalia.membership::lang.action.file_replace_success'));

	return [
	  '#new-attestation' => '<a target="_blank" href="'.$file->getPath().'">'.$file->file_name.'</a>', 
	  // Replaces the old file input by a new one to clear the previous file selection.
	  '#attestation-file-input' => '<input type="file" name="attestation" class="form-control" id="inputAttestation">'
	];
    }

    public function onPayment()
    {
        $paymentMode = post('payment_mode');
        $item = post('item');
        $insuranceCode = post('insurance_code');
	$member = $this->loadMember();
	$offlineModes = ['cheque', 'bank_transfer', 'free_period'];

	// The user has added the insurance to the subscription fee.
	if ($item == 'subscription' && $insuranceCode && $insuranceCode != 'f0') {
	    $item = 'subscription-insurance-'.$insuranceCode;
	}
	elseif ($item == 'insurance') {
	    $item = 'insurance-'.post('code');
	}

	// Ensures the member data matches the free period conditions.
	if ($paymentMode == 'free_period' && (!$member->free_period || !$member->member_since)) {
	    return Redirect::to('403');
	}

        if (in_array($paymentMode, $offlineModes)) {
	    // Handles the free period option.
	    $status = ($paymentMode == 'free_period') ? 'completed' : 'pending';
	    $amount = ($paymentMode == 'free_period') ? 0 : Payment::getAmount($item);

	    $data = ['mode' => $paymentMode, 'status' => $status, 'item' => $item, 'amount' => $amount,
		     'currency' => 'EUR', 'transaction_id' => uniqid('OFFL'), 'last' => 1];

	    $member->savePayment($data);

	    $langVar = ($paymentMode == 'free_period') ? 'free_period_privilege_success' : $paymentMode.'_payment_success';
	    Flash::success(Lang::get('codalia.membership::lang.action.'.$langVar));

	    return[
		'#payment-modes' => '<div class="card bg-light mb-3"><div class="card-header">Information</div><div class="card-body">There is no payment to display.</div></div>'
	    ];
	}
	// Online modes (Paypal etc..).
	else {
	    return Redirect::to('/'.$paymentMode.'/'.$item.'/pay-now');
	}
    }

    public function onUpdate()
    {
	$data = post();

	// Data comes from the main form.
	if (isset($data['membership'])) {
	    $update = $data['membership'];
	    $rules = MemberModel::getRules();
	}
	// or from the information form.
	else {
	    $update = ['member_list' => $data['member_list']];
	}

	$validation = Validator::make($data, $rules);
	if ($validation->fails()) {
	    throw new ValidationException($validation);
	}

	// Updates the passed data.
	$member = $this->loadMember();
	$member->update($update);

	Flash::success(Lang::get('codalia.membership::lang.action.update_success'));
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
