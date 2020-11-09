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

    public $rules = [];


    public function getRenewalDayOptions()
    {
      return array('01' => '1','02' => '2','03' => '3','04' => '4','05' => '5','06' => '6','07' => '7','08' => '8',
		   '09' => '9','10' => '10','11' => '11','12' => '12','13' => '13','14' => '14','15' => '15','16' => '16',
		   '17' => '17','18' => '18','19' => '19','20' => '20','21' => '21','22' => '22','23' => '23','24' => '24', 
		   '25' => '25','26' => '26','27' => '27','28' => '28','29' => '29','30' => '30','31' => '31'); 
    }

    public function getRenewalMonthOptions()
    {
      return array('01' => 'codalia.membership::lang.global_settings.january','02' => 'codalia.membership::lang.global_settings.february',
		   '03' => 'codalia.membership::lang.global_settings.march','04' => 'codalia.membership::lang.global_settings.april',
		   '05' => 'codalia.membership::lang.global_settings.may','06' => 'codalia.membership::lang.global_settings.june',
		   '07' => 'codalia.membership::lang.global_settings.july','08' => 'codalia.membership::lang.global_settings.august',
		   '09' => 'codalia.membership::lang.global_settings.september','10' => 'codalia.membership::lang.global_settings.october',
		   '11' => 'codalia.membership::lang.global_settings.november','12' => 'codalia.membership::lang.global_settings.december');
    }

    public function beforeSave()
    {
      //file_put_contents('debog_file.txt', print_r($data, true));
        if (!$this->checkRenewalDate()) {
	    //throw new \ApplicationException(\Lang::get('codalia.journal::lang.settings.invalid_file_name'));
	    throw new \ValidationException(['renewal_day' => \Lang::get('codalia.journal::lang.settings.invalid_file_name')]);
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
}
