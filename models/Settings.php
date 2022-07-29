<?php namespace Codalia\Membership\Models;

use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'codalia_membership_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
	'renewal_period' => 'required|numeric',
	'reminder_renewal' => 'required|numeric',
	//'revocation' => 'required|numeric',
	'free_period' => 'required|numeric',
	'subscription_fee' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'honorary_subscription_fee' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f1' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f2' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f3' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f4' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f5' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f6' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f7' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f8' => 'required|regex:/^\d+(\.\d{1,2})?$/',
	'insurance_fee_f9' => 'required|regex:/^\d+(\.\d{1,2})?$/',
    ];


    public function getRenewalDayOptions()
    {
	return [
	    '01' => '1','02' => '2','03' => '3','04' => '4','05' => '5','06' => '6','07' => '7','08' => '8',
	    '09' => '9','10' => '10','11' => '11','12' => '12','13' => '13','14' => '14','15' => '15','16' => '16',
	    '17' => '17','18' => '18','19' => '19','20' => '20','21' => '21','22' => '22','23' => '23','24' => '24', 
	    '25' => '25','26' => '26','27' => '27','28' => '28','29' => '29','30' => '30','31' => '31'
	]; 
    }

    public function getRenewalMonthOptions()
    {
	return [
	    '01' => 'codalia.membership::lang.global_settings.january','02' => 'codalia.membership::lang.global_settings.february',
	    '03' => 'codalia.membership::lang.global_settings.march','04' => 'codalia.membership::lang.global_settings.april',
	    '05' => 'codalia.membership::lang.global_settings.may','06' => 'codalia.membership::lang.global_settings.june',
	    '07' => 'codalia.membership::lang.global_settings.july','08' => 'codalia.membership::lang.global_settings.august',
	    '09' => 'codalia.membership::lang.global_settings.september','10' => 'codalia.membership::lang.global_settings.october',
	    '11' => 'codalia.membership::lang.global_settings.november','12' => 'codalia.membership::lang.global_settings.december'
	];
    }

    public function beforeSave()
    {
        if (!$this->checkRenewalDate()) {
	    throw new \ValidationException(['#Form-field-Settings-renewal_day' => \Lang::get('codalia.membership::lang.global_settings.day_month_not_matching')]);
	}

        if (!$this->checkReminderDate()) {
	    throw new \ValidationException(['#Form-field-Settings-reminder_renewal' => \Lang::get('codalia.membership::lang.global_settings.reminder_renewal_too_high')]);
	}
    }

    protected function checkRenewalDate()
    {
        $data = post('Settings');
	$months = ['01', '03', '05', '07', '08', '10', '12'];

	if ($data['renewal_day'] == 31 && !in_array($data['renewal_month'], $months)) {
	    return false;
	}
	elseif ($data['renewal_day'] > 28 && $data['renewal_month'] == '02') {
	    return false;
	}

	return true;
    }

    protected function checkReminderDate()
    {
        $data = post('Settings');

	if (substr($data['reminder_renewal'], 0, 1 ) == '-') {
	    $daysReminder = ltrim($data['reminder_renewal'], '-');

	    if ($daysReminder >= $data['renewal_period']) {
		return false;
	    }
	}

	return true;
    }
}
