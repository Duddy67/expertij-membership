<?php namespace Codalia\Membership\Models;

use Model;
use Codalia\Membership\Models\Settings;
use Renatio\DynamicPDF\Classes\PDF; // import facade
use System\Classes\PluginManager;
use Codalia\Membership\Helpers\RenewalHelper;
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

        if ($item == 'subscription') {
            $amount = Settings::get('subscription_fee', 0);
        }
        elseif (substr($item, 0, 24) === 'subscription-insurance-f') {
            preg_match('#subscription-insurance-f([0-9]*)#', $item, $matches);
            $amount = Settings::get('subscription_fee', 0) + Settings::get('insurance_fee_f'.$matches[1], 0);
        }
        elseif (substr($item, 0, 11) === 'insurance-f') {
            preg_match('#insurance-f([0-9]*)#', $item, $matches);
            $amount = Settings::get('insurance_fee_f'.$matches[1], 0);
        }

	return $amount;
    }

    public function getInvoicesPDF()
    {
        $tmpInvoicesPDF = $types = [];

	if (PluginManager::instance()->exists('Renatio.DynamicPDF')) {
	    $vars = ['first_name' => $this->member->profile->first_name,
		     'last_name' => $this->member->profile->last_name,
		     'civility' => $this->member->profile->civility,
		     'street' => $this->member->profile->street,
		     'city' => $this->member->profile->city,
		     'postcode' => $this->member->profile->postcode,
		     'amount' => $this->amount,
		     'item' => $this->item,
		     'item_name' => Lang::get('codalia.membership::lang.payment.'.$this->item),
		     'item_reference' => Lang::get('codalia.membership::lang.payments.item.'.$this->item),
		     'payment_mode' => $this->mode,
		     'member_number' => $this->member->member_number,
                     'subscription_start_date' => RenewalHelper::instance()->getSubscriptionStartDate()->format('d/m/Y'),
                     'subscription_end_date' => RenewalHelper::instance()->getSubscriptionEndDate()->format('d/m/Y'),
                     'current_date' => date('d/m/Y'),
	    ];

	    // Separates subscription and insurance fees.
	    if (substr($this->item, 0, 12) === 'subscription') {
		$vars['subscription_fee'] = ($this->mode == 'free_period') ? 0 : self::getAmount('subscription');
                $types[] = 'subscription';
	    }

	    // The user has paid only for insurance or for both subscription and insurance.
	    if (substr($this->item, 0, 9) === 'insurance' || substr($this->item, 0, 22) === 'subscription-insurance') {
		// Removes the 'subscription-' part from the item code.
		$insurance = (substr($this->item, 0, 9) === 'insurance') ? $this->item : substr($this->item, 13); 

		$vars['insurance_fee'] = self::getAmount($insurance);
		$vars['insurance_name'] = Lang::get('codalia.membership::lang.payment.'.$insurance);
                $types[] = 'insurance';
	    }

            foreach ($types as $type) {
                // TODO: Temporary. Wait for the proper way to compute the invoice id number.
                $tmpInvoicePDF = '/tmp/'.$type.'_invoice_'.uniqid().'.pdf';
                PDF::loadTemplate($type.'-invoice-membership', $vars)->save($tmpInvoicePDF);
                $tmpInvoicesPDF[$type] = $tmpInvoicePDF;
            }
	}

        return $tmpInvoicesPDF;
    }
}
