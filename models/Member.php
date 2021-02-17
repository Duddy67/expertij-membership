<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Insurance;
use Codalia\Membership\Models\Category;
use Codalia\Membership\Helpers\EmailHelper;
use Codalia\Membership\Helpers\RenewalHelper;
use Codalia\Profile\Models\Profile;
use Carbon\Carbon;

/**
 * Member Model
 */
class Member extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_membership_members';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id', 'profile_id', 'created_at', 'updated_at'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $payment = null;

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [
        'insurance' => ['Codalia\Membership\Models\Insurance']
    ];
    public $hasMany = [
        'votes' => ['Codalia\Membership\Models\Vote'],
        'payments' => ['Codalia\Membership\Models\Payment']
    ];
    public $belongsTo = [
        'profile' => ['Codalia\Profile\Models\Profile'],
    ];
    public $belongsToMany = [
	'categories' => ['Codalia\Membership\Models\Category',
			 'table' => 'codalia_membership_cat_members',
			 'order' => 'created_at desc',
	],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'attestation' => ['System\Models\File', 'delete' => true],
    ];
    public $attachMany = [
        // Deletes the attached files once a model is removed.
        'invoices' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true]
    ];


    public static function getFromProfile($profile, $data)
    {
        if ($profile->member) {
	    return $profile->member;
	}

	$member = new static;
	$member->profile = $profile;

	if (RenewalHelper::instance()->isFreePeriod()) {
	    $member->free_period = 1;
	}

	// Important: Creates a member without validation.
	// NB. The validation has been performed earlier in the code.
	$member->save();

	// Creates an empty insurance.
	$insurance = new Insurance;
	$member->insurance()->save($insurance);

	$profile->member = $member;

	// Honorary members don't have a professional situation.
	if ($profile->honorary_member) {
	    return $member;
	}

	$member->update($data['membership']);

	return $member;
    }

    public function getStatusOptions()
    {
	return array('pending' => 'codalia.membership::lang.status.pending',
		     'refused' => 'codalia.membership::lang.status.refused',
		     'pending_subscription' => 'codalia.membership::lang.status.pending_subscription',
		     'cancelled' => 'codalia.membership::lang.status.cancelled',
		     'member' => 'codalia.membership::lang.status.member',
		     'pending_renewal' => 'codalia.membership::lang.status.pending_renewal',
		     'revoked' => 'codalia.membership::lang.status.revoked',
		     'cancellation' => 'codalia.membership::lang.status.cancellation');
    }

    public function getProStatusOptions()
    {
	return Member::getProStatusOptionData();
    }

    public static function getProStatusOptionData()
    {
	return ['liberal_profession' => 'codalia.membership::lang.professional_status.liberal_profession',
		'micro_entrepreneur' => 'codalia.membership::lang.professional_status.micro_entrepreneur',
		'company' => 'codalia.membership::lang.professional_status.company',
		'other' => 'codalia.membership::lang.professional_status.other',
	];
    }

    public static function getRules()
    {
	$rules = [
	    'membership.pro_status' => 'required',
	    'membership.since' => 'required',
	    'membership.pro_status_info' => 'required_if:membership.pro_status,other|between:2,30',
	    'membership.siret_number' => 'required|size:14',
	    'membership.naf_code' => 'required|size:5',
	];

	if (\Session::has('registration_context')) {
	    $rules['membership__attestation'] = 'required|mimes:pdf';
	}

	return $rules;
    }

    public static function getValidationRuleAttributes()
    {
        $rules = Member::getRules();
	$attributes = [];

	foreach ($rules as $attribute => $rule) {
	    $lang = str_replace('membership.', '', $attribute);
	    $attributes[$attribute] = 'codalia.membership::lang.professional_status.'.$lang;
	}

	return $attributes;
    }

    public static function getValidationRuleMessages()
    {
        $messages = [];

	return $messages;
    }

    public function getAppealCourts()
    {
        return Profile::getAppealCourts();
    }

    public function getCourts()
    {
        return Profile::getCourts();
    }

    public function getThumbnailSize()
    {
        return Profile::getThumbnailSize();
    }

    public function getBlankProfileUrl()
    {
        return Profile::getBlankProfileUrl();
    }

    /*
     * Used by the Profile plugin in the registration form.
     */
    public static function getHostedFields()
    {
	$hostedFields = ['attestation' => 'codalia.membership::lang.professional_status.attestation',
			 'pro_status' => 'codalia.membership::lang.professional_status.pro_status',
			 'pro_status_info' => 'codalia.membership::lang.professional_status.pro_status_info',
			 'liberal_profession' => 'codalia.membership::lang.professional_status.liberal_profession',
			 'micro_entrepreneur' => 'codalia.membership::lang.professional_status.micro_entrepreneur',
			 'company' => 'codalia.membership::lang.professional_status.company',
			 'other' => 'codalia.membership::lang.professional_status.other',
			 'siret_number' => 'codalia.membership::lang.professional_status.siret_number',
			 'since' => 'codalia.membership::lang.professional_status.since',
			 'naf_code' => 'codalia.membership::lang.professional_status.naf_code',
	];

	$hostedFields['pro_status_options'] = Member::getProStatusOptionData();

        return $hostedFields;
    }

    /**
     * Switch visibility of some fields.
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
    }

    /**
     * Sets both member and insurance statuses according to the payment result. 
     *
     * @param  array	$data
     * @return void
     */
    public function savePayment($data)
    {
        // Creates a new payment row.
        if (!$this->payment = Payment::getPayment($data['mode'], $data['transaction_id'])) {
	    $payment = new Payment ($data);
	    $this->payments()->save($payment);
            // Gets the newly created payment.
	    $this->payment = Payment::getPayment($data['mode'], $data['transaction_id']);

	    // Sets the 'last' flag of the older payments to zero.
	    $values = [['id', '<>', $this->payment->id]];
	    if (substr($data['item'], 0, 9) === 'insurance') {
		// Updates only the insurance payments.
		$values[] = ['item', 'like', 'insurance%'];
	    }

	    $this->payments()->where($values)->update(['last' => 0]);
	}
	// Updates the payment status.
	else {
	    $this->payment->update(['status' => $data['status']]);
	}

	if ($data['mode'] == 'cheque' && $data['status'] == 'pending') {
	    EmailHelper::instance()->alertChequePayment($this, $data);
	    return;
	}

	if ($data['status'] == 'completed') {
	    // New subscription or renewal.
	    if (substr($data['item'], 0, 12) === 'subscription') {
		$isNewMember = ($this->member_since === null) ? true : false;
		$update = ['status' => 'member'];

		if (!$isNewMember) {
		    // The privilege of the free period stops after the first renewal.
		    $update['free_period'] = 0;
		}

		// Becomes member again or new member.
		$this->update($update);
		// Reset the insurance status.
		$this->insurance()->update(['status' => 'disabled']);

		// Informs the member (or candidate) about the status change.
		EmailHelper::instance()->statusChange($this->id, 'member', $isNewMember);
	    }

            // Insurance only or insurance included with subscription.
	    if (substr($data['item'], 0, 9) === 'insurance' || substr($data['item'], 0, 22) === 'subscription-insurance') {
		// Removes the 'subscription-' part from the item code.
		$insurance = (substr($data['item'], 0, 9) === 'insurance') ? $data['item'] : substr($data['item'], 13); 
		// Gets the insurance code placed after the hyphen (ie: insurance-xx).
		$code = substr($insurance, 10);
		$this->insurance()->update(['status' => 'running', 'code' => $code]);
	    }

	    // Stores the invoice.
	    if ($tmpFile = $this->payment->getInvoicePDF()) {
		$this->invoices = $tmpFile;
		$this->forceSave();
		@unlink($tmpFile);

		$invoice = $this->invoices()->first();
		$data['invoice_path'] = $invoice->getLocalPath();
		$data['invoice_name'] = $invoice->file_name;
	    }

	    EmailHelper::instance()->alertPayment($this->id, $data);
	}
	// error, cancelled
	else {
	    EmailHelper::instance()->alertPayment($this->id, $data);
	}
    }

    public function cancelMembership($status)
    {
        if ($status != 'cancelled' && $status != 'revoked' && $status != 'cancellation') {
	    return;
	}

        $this->update(['status' => $status]);
	$this->insurance()->update(['status' => 'disabled']);
	EmailHelper::instance()->statusChange($this->id, $status);
    }

    /*
     * TODO: Temporary. Wait for the proper way to compute the member numbers.
     */
    public function getMemberNumber()
    {
        return uniqid('MB');
    }

    /*
     * N.B: Called only if one or more values have been modified.
     */ 
    public function afterUpdate()
    {
        // It's a brand new member.
        if ($this->status == 'member' && $this->member_since === null) {
	    Member::where('id', $this->id)->update(['member_since' => Carbon::now(), 'member_number' => $this->getMemberNumber()]);

	    // The first subscription fee must be paid during the free period as well to be valid.
	    if (!RenewalHelper::instance()->isFreePeriod()) {
		Member::where('id', $this->id)->update(['free_period' => 0]);
	    }
	}
    }
}
