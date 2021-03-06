<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Membership\Models\Settings;
use Renatio\DynamicPDF\Classes\PDF; // import facade
use System\Classes\PluginManager;
use Lang;

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
     * Checks if a payment exists in database.
     *
     * @param string  $paymentMode	The payment mode name.
     * @param string  $transactionId	The transaction id to compare.
     *
     * @return object			The Payment object if the payment exists, null otherwise.
     */
    public static function getPayment($paymentMode, $transactionId)
    {
	return Payment::where([['mode', $paymentMode], ['transaction_id', $transactionId]])->first();
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

    public function getInvoicePDF()
    {
        $tmpInvoicePDF = null;

	if (PluginManager::instance()->exists('Renatio.DynamicPDF')) {
	    $vars = ['first_name' => $this->member->profile->first_name,
		     'last_name' => $this->member->profile->last_name,
		     'amount' => $this->amount,
		     'item' => $this->item,
		     'item_name' => Lang::get('codalia.membership::lang.payment.'.$this->item),
		     'payment_mode' => $this->mode,
		     'reference' => 'xxxxxxxxxx',
	    ];

	    // Separates subscription and insurance fees.
	    if (substr($this->item, 0, 12) === 'subscription') {
		$vars['subscription_fee'] = ($this->mode == 'free_period') ? 0 : self::getAmount('subscription');
	    }

	    // The user has paid only for insurance or for both subscription and insurance.
	    if (substr($this->item, 0, 9) === 'insurance' || substr($this->item, 0, 22) === 'subscription-insurance') {
		// Removes the 'subscription-' part from the item code.
		$insurance = (substr($this->item, 0, 9) === 'insurance') ? $this->item : substr($this->item, 13); 

		$vars['insurance_fee'] = self::getAmount($insurance);
		$vars['insurance_name'] = Lang::get('codalia.membership::lang.payment.'.$insurance);
	    }

	    // TODO: Temporary. Wait for the proper way to compute the invoice id number.
	    $tmpInvoicePDF = '/tmp/invoice_'.uniqid().'.pdf';
	    PDF::loadTemplate('invoice-membership', $vars)->save($tmpInvoicePDF);
	}

        return $tmpInvoicePDF;
    }
}
