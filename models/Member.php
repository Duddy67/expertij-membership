<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Profile\Models\Profile as ProfileModel;

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
    protected $guarded = ['*'];

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

    /*public function getEmailFieldAttribute()
    {
        return $this->profile->user->email;
    }*/

    /*public function getFirstNameAttribute()
    {
        return $this->user->profile->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->user->profile->last_name;
    }*/
}
