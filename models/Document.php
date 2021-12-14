<?php namespace Codalia\Membership\Models;

use Model;
use Carbon\Carbon;
use Codalia\Profile\Models\Profile;
use Codalia\Profile\Models\Licence;
use BackendAuth;

/**
 * Document Model
 */
class Document extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_membership_documents';

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
    public $rules = ['title' => 'required', 'files' => 'required'];

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
    public $belongsTo = [];
    public $belongsToMany = [
	'categories' => ['Codalia\Membership\Models\Category',
			 'table' => 'codalia_membership_cat_documents',
			 'order' => 'created_at desc',
      ],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [
        // Deletes the linked files once a model is removed.
        'files' => ['System\Models\File', 'order' => 'created_at desc', 'delete' => true]
    ];


    public function beforeCreate()
    {
	$user = BackendAuth::getUser();
	$this->created_by = $user->id;
    }

    public function beforeUpdate()
    {
	$user = BackendAuth::getUser();
	$this->updated_by = $user->id;
	// Prevent error when updating.
	$this->last_email_sending = ($this->last_email_sending) ? $this->last_email_sending : null;
    }

    public function getStatusOptions()
    {
	return array('unpublished' => 'codalia.membership::lang.status.unpublished',
		     'published' => 'codalia.membership::lang.status.published',
		     'archived' => 'codalia.membership::lang.status.archived');
    }

    public function getAppealCourtsOptions()
    {
        return Profile::getAppealCourts();
    }

    public function getCourtsOptions()
    {
        return Profile::getCourts();
    }

    public function getLicenceTypesOptions()
    {
	$types = Licence::getTypes();
	$licenceTypes = [];

	foreach ($types as $type) {
	    $licenceTypes[$type] = 'codalia.profile::lang.licence.'.$type;
	}

	return $licenceTypes;
    }

    public function getLanguagesOptions()
    {
        $codes = Profile::getLanguages();
	$languages = [];

	foreach ($codes as $code) {
	    $languages[$code] = 'codalia.profile::lang.language.'.$code;
	}

	return $languages;
    }

    public function getUpdatedByFieldAttribute()
    {
	$names = '';

	if($this->updated_by) {
	    $user = BackendAuth::findUserById($this->updated_by);
	    $names = $user->first_name.' '.$user->last_name;
	}

	return $names;
    }

    public function getCreatedByFieldAttribute()
    {
	$names = '';

        if ($this->created_by) {
	    $user = BackendAuth::findUserById($this->created_by);
	    $names = $user->first_name.' '.$user->last_name;
	}

	return $names;
    }

    public static function setPublishingDate($document)
    {
	// Sets to the current date time in case the record has never been published before. 
	return ($document->status == 'published' && is_null($document->published_up)) ? Carbon::now() : $document->published_up;
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
}

