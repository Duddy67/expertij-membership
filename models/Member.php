<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Insurance;
use Codalia\Membership\Models\Category;
use Codalia\Membership\Models\AppealCourt;
use Codalia\Membership\Helpers\EmailHelper;
use Codalia\Membership\Helpers\RenewalHelper;
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
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['status', 'member_list', 'free_period', 'appeal_court_id'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
	'attestation' => 'required_if:_upload,1',
	'appealCourt' => 'required',
    ];

    /**
     * @var array Rule  messages for attributes
     */
    public $ruleMessages = [
	'attestation.required_if' => 'The :attribute field is required.',
    ];

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
        'appealCourt' => ['Codalia\Membership\Models\AppealCourt']
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
    public $attachOne = [];
    public $attachMany = [
        // Deletes the linked files once a model is removed.
        'attestations' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true],
        'invoices' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true]
    ];


    public static function getFromProfile($profile, $data = array())
    {
        if ($profile->member) {
	    return $profile->member;
	}

	$member = new static;
	$member->profile = $profile;

	if (RenewalHelper::instance()->isFreePeriod()) {
	    $member->free_period = 1;
	}

	if (isset($data['appealCourt'])) {
	    $member->appeal_court_id = (int)$data['appealCourt'];
	}

	// Important: Creates a member without validation.
	// NB. The validation has been performed earlier in the code.
	$member->forceSave();

	if (isset($data['categories']) && !empty($data['categories'])) {
	    $member->categories()->attach($data['categories']);
	}

	// Creates an empty insurance.
	$insurance = new Insurance;
	$member->insurance()->save($insurance);

	$profile->member = $member;

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

    /*
     * Used by the Profile plugin in the registration form.
     */
    public static function getSharedFields()
    {
	$sharedFields = ['attestation' => 'codalia.membership::lang.profile.attestation',
			 'appeal_court' => 'codalia.membership::lang.profile.appeal_court',
			 'categories' => 'codalia.membership::lang.profile.categories'
	];

	$sharedFields['category_options'] = Category::get()->pluck('name', 'id')->toArray();
	$sharedFields['appeal_court_options'] = AppealCourt::get()->pluck('name', 'id')->toArray();

        return $sharedFields;
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
        if (!Payment::transactionIdExists($data['mode'], $data['transaction_id'])) {
	    $payment = new Payment ($data);
	    $this->payments()->save($payment);
	    // Gets the id of the latest payment (ie: the one which has just been created).
	    $paymentId = Payment::transactionIdExists($data['mode'], $data['transaction_id']);

	    // Sets the 'last' flag of the older payments to zero.
	    $values = [['id', '<>', $paymentId]];
	    if (substr($data['item'], 0, 9) === 'insurance') {
		// Updates only the insurance payments.
		$values[] = ['item', 'like', 'insurance%'];
	    }

	    $this->payments()->where($values)->update(['last' => 0]);
            // Gets the newly created payment.
	    $this->payment = $this->payments()->where('id', $paymentId)->first();
	}
	// Updates the payment status.
	else {
	    $this->payment = $this->payments()->where('mode', $data['mode'])->where('transaction_id', $data['transaction_id'])->first();
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
