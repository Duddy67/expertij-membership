<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Models\Document;
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
     * @var array Rules set for file types.
     */
    public $relationships = [
	'attestation' => 'attestations', 'photo' => 'photo'
    ];

    /**
     * @var array Matching between file types and rules.
     */
    public $fileRules = [
	'attestation' => 'mimes:pdf,doc,docx,png,jpg,jpeg|max:10000', 'photo' => 'mimes:jpg,jpeg,png|max:10000'
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
        if (\Session::has('sherlocks_results')) {
	    // Retrieves then delete the results from the session.
	    $this->page['sherlocksResults'] = \Session::pull('sherlocks_results');
	}

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

        $insurancePendingPayment = false;
	if ($this->member->payments()->where([['status', 'pending'], ['mode', 'cheque'], ['item', 'like', 'insurance-%'], ['last', 1]])->first()) {
            $insurancePendingPayment = true; 
        }

	$this->page['flags'] = ['payment' => $payment, 'candidate' => ($this->member->member_since === null) ? true : false,
				'freePeriod' => ($this->member->free_period && $this->member->member_since) ? true : false,
                                'insurancePendingPayment' => $insurancePendingPayment];
	$this->page['documents'] = $this->loadDocuments();
	$this->page['years'] = Profile::getYears();
	$this->page['categoryIds'] = $this->member->categories->pluck('id')->toArray();
	$this->page['proStatuses'] = $this->getProStatuses();
	$this->page['texts'] = $this->getTexts();
	$this->page['javascriptMessages'] = $this->getJavascriptMessages();
	$this->page['insurances'] = $this->getInsurances();
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

	return $member;
    }

    protected function loadDocuments()
    {
        $licences = [];

	// Collects the member's licences, courts and languages.
        foreach ($this->member->profile->licences as $licence) {
	    $data = [];
	    $data['type'] = $licence->type;
	    $courtType = ($licence->type == 'expert') ? 'appeal_court_id' : 'court_id';
	    $data['court'] = $licence->$courtType;
	    $data['languages'] = [];

	    foreach ($licence->attestations as $attestation) {
		foreach ($attestation->languages as $language) {
		    $data['languages'][] = $language->alpha_3;
		}
	    }

	    $licences[] = $data;
	}

	// Gets the documents matching the member's licences, courts and languages.
	$documents = Document::where('status', 'published')->where(function ($query) use($licences) {
			foreach ($licences as $licence) {

			    $query->orWhere(function ($query) use($licence) {
				$query->where(function ($query) use($licence) {
				    $query->whereRaw('FIND_IN_SET(?,licence_types) > 0', [$licence['type']])->orWhereNull('licence_types');
				})->where(function ($query) use($licence) {
				    $courtType = ($licence['type'] == 'expert') ? 'appeal_courts' : 'courts';
				    $query->whereRaw('FIND_IN_SET(?,'.$courtType.') > 0', [$licence['court']])->orWhereNull($courtType);
				})->where(function ($query) use($licence) {

				    foreach ($licence['languages'] as $language) {
					$query->orWhereRaw('FIND_IN_SET(?,languages) > 0', [$language]);
				    }

				    $query->orWhereNull('languages');
				});
			    });
			}
		    })->get();

	return $documents;
    }

    private function getProStatuses()
    {
	$statuses = MemberModel::getProStatusOptionData();

	foreach ($statuses as $code => $langVar) {
	    $statuses[$code] = Lang::get($langVar);
	}

	return $statuses;
    }

    private function getInsurances()
    {
        $insurances = [];
        $i = 1;

        while (Settings::get('insurance_fee_f'.$i, null)) {
            $price = Settings::get('insurance_fee_f'.$i);
            $formula = Lang::get('codalia.membership::lang.payments.item.insurance-f'.$i);
            $insurance = ['formula' => $formula, 'price' => $price, 'code' => 'f'.$i];
            $insurances[] = $insurance;
            $i++;
        }

        return $insurances;
    }

    private function getTexts()
    {
        $langVars = require 'plugins/codalia/membership/lang/en/lang.php';
	$texts = [];
	$sections = ['professional_status', 'profile', 'action', 'attribute', 'status', 'member', 'payments', 'membership'];

	foreach ($langVars as $level1 => $section1) {
	    if (in_array($level1, $sections)) {
		foreach ($section1 as $level2 => $section2) {
		    $texts[$level1.'.'.$level2] = Lang::get('codalia.membership::lang.'.$level1.'.'.$level2);
		}
	    }
	}

	return $texts;
    }

    public function onReplaceAttestation()
    {
        $file = null;

	if (Input::hasFile('attestation')) {

            $rules = ['attestation' => $this->fileRules['attestation']];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

	    $file = (new File())->fromPost(Input::file('attestation'));
	    $member = $this->loadMember();

	    $member->attestation()->add($file);
	    $member->save();
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
	    $amount = ($paymentMode == 'free_period') ? 0 : Payment::getAmount($item, $member->profile->honorary_member);

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
	    $rules = [];
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

    protected function getJavascriptMessages()
    {
        $messages = [];
	$messages['pay_cheque_confirmation'] = Lang::get('codalia.membership::lang.action.pay_cheque_confirmation');
	$messages['pay_paypal_confirmation'] = Lang::get('codalia.membership::lang.action.pay_paypal_confirmation');
	$messages['pay_sherlocks_confirmation'] = Lang::get('codalia.membership::lang.action.pay_sherlocks_confirmation');
	$messages['pay_free_period_confirmation'] = Lang::get('codalia.membership::lang.action.pay_free_period_confirmation');

	return json_encode($messages);
    }
}
