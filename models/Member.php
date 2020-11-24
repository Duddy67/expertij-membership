<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Profile\Models\Profile as ProfileModel;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Helpers\EmailHelper;
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
    protected $fillable = ['status'];

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
    public $hasOne = [];
    public $hasMany = [
        'votes' => ['Codalia\Membership\Models\Vote'],
        'payments' => ['Codalia\Membership\Models\Payment']
    ];
    public $belongsTo = [
        'profile' => ['Codalia\Profile\Models\Profile']
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
        'attestations' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true]
    ];


    public static function getFromProfile($profile)
    {
        if ($profile->member) {
	    return $profile->member;
	}

	$member = new static;
	$member->profile = $profile;
	// Important: Creates a member without validation.
	// NB. The validation has been performed earlier in the code.
	$member->forceSave();
	$profile->member = $member;

	return $member;
    }

    public function getStatusOptions()
    {
	return array('pending' => 'codalia.membership::lang.status.pending',
		     'refused' => 'codalia.membership::lang.status.refused',
		     'pending_subscription' => 'codalia.membership::lang.status.pending_subscription',
		     'canceled' => 'codalia.membership::lang.status.canceled',
		     'member' => 'codalia.membership::lang.status.member',
		     'pending_renewal' => 'codalia.membership::lang.status.pending_renewal',
		     'revoked' => 'codalia.membership::lang.status.revoked');
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

    public function savePayment($data)
    {
        $payment = new Payment ($data);
	$this->payments()->save($payment);
	// Gets the latest payment.
	$payment = $this->payments()->where('item', 'subscription')->latest()->first();
	// Sets the 'last' flag of the older payments to zero.
	$this->payments()->where([['id', '<>', $payment->id], ['item', '=', 'subscription']])->update(['last' => 0]);

	if ($data['status'] == 'completed') {
	    if ($data['item'] == 'subscription') {
		$isNewMember = ($this->member_since === null) ? true : false;
		// Becomes member again or new member.
		$this->update(['status' => 'member']);
		// Informs the member about the status change.
		EmailHelper::instance()->statusChange($this->id, 'member', $isNewMember);
	    }

	    EmailHelper::instance()->alertPayment($this->id, $data);
	}
	// error
	else {
	    EmailHelper::instance()->alertPayment($this->id, $data);
	}
    }

    /*
     * N.B: Called only if one or more values have been modified.
     */ 
    public function afterUpdate()
    {
        // It's a brand new member.
        if ($this->status == 'member' && $this->member_since === null) {
	    Member::where('id', $this->id)->update(['member_since' => Carbon::now()]);
	}
    }
}
