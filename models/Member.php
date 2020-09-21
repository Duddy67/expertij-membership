<?php namespace Codalia\Membership\Models;

use Model;

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
    public $hasMany = [];
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User'],
        'profile' => ['Codalia\Profile\Models\Profile']
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        'attestations' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true]
    ];


    public static function getFromUser($user, $profile)
    {
        if ($user->member) {
	    return $user->member;
	}

	$member = new static;
	$member->user = $user;
	$member->profile = $profile;
	$member->save();
	$user->member = $member;
	$profile->member = $member;

	return $member;
    }

    public function getStatusOptions()
    {
	return array('pending' => 'codalia.membership::lang.status.pending',
		     'refused' => 'codalia.membership::lang.status.refused',
		     'pending_payment' => 'codalia.membership::lang.status.pending_payment',
		     'discarded' => 'codalia.membership::lang.status.discarded',
		     'member' => 'codalia.membership::lang.status.member');
    }

    /*public function getFirstNameAttribute()
    {
        return $this->user->profile->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->user->profile->last_name;
    }*/
}
