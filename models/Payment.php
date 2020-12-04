<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Membership\Models\Settings;

/**
 * Payment Model
 */
class Payment extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_membership_payments';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id', 'member_id', 'created_at', 'updated_at'];

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
        'member' => ['Codalia\Membership\Models\Member']
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    /*
     * Check if a transaction id exists.
     *
     * @param string  $paymentMode	The payment mode name.
     * @param string  $transactionId	The transaction id to compare.
     *
     * @return integer			Zero if the transaction id doesn't exist, the payment id otherwise.
     */
    public static function transactionIdExists($paymentMode, $transactionId)
    {
	return (int)Payment::where('mode', $paymentMode)->where('transaction_id', $transactionId)->value('id');
    }

    /*
     *  return  decimal		The amount for a given item code.
     */
    public static function getAmount($item)
    {
        $amount = 0;

        switch ($item) {
	    case 'subscription':
	        $amount = Settings::get('subscription_fee', 0);
	        break;
	    case 'subscription-insurance-f1':
	        $amount = Settings::get('subscription_fee', 0) + Settings::get('insurance_fee_f1', 0);
	        break;
	    case 'subscription-insurance-f2':
	        $amount = Settings::get('subscription_fee', 0) + Settings::get('insurance_fee_f2', 0);
	        break;
	    case 'insurance-f1':
	        $amount = Settings::get('insurance_fee_f1', 0);
	        break;
	    case 'insurance-f2':
	        $amount = Settings::get('insurance_fee_f2', 0);
	        break;
	}

	return $amount;
    }
}
