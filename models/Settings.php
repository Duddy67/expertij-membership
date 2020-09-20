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
      return array('01' => 'codalia.membership::lang.settings.january','02' => 'codalia.membership::lang.settings.february',
		   '03' => 'codalia.membership::lang.settings.march','04' => 'codalia.membership::lang.settings.april',
		   '05' => 'codalia.membership::lang.settings.may','06' => 'codalia.membership::lang.settings.june',
		   '07' => 'codalia.membership::lang.settings.july','08' => 'codalia.membership::lang.settings.august',
		   '09' => 'codalia.membership::lang.settings.september','10' => 'codalia.membership::lang.settings.october',
		   '11' => 'codalia.membership::lang.settings.november','12' => 'codalia.membership::lang.settings.december');
    }

    public function beforeSave()
    {
        $data = post('Settings');
      file_put_contents('debog_file.txt', print_r($data, true));
        throw new \ApplicationException(\Lang::get('codalia.journal::lang.settings.invalid_file_name'));
      //throw new \Exception("Invalid Model!");
	/*if (!$user->isValid()) {
	    return false;
	  }*/
    }
}
