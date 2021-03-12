<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Codalia\Membership\Models\Settings;
use Carbon\Carbon;
use Backend;
use Flash;
use Db;


class MembershipHelper
{
    use Singleton;


    /**
     * Checks out a given item for a given user.
     *
     * @param string  $tableName
     * @param User    $user
     * @param integer $recordId
     *
     * @return void
     */
    public function checkOut($tableName, $user, $recordId)
    {
	Db::table($tableName)->where('id', $recordId)
			     ->update(['checked_out' => $user->id, 
				       'checked_out_time' => Carbon::now()]);
    }

    /**
     * Checks in an item table. The "check-in" can be more specific according to the
     * optional parameters passed.
     *
     * @param string  $tableName
     * @param User    $user (optional)
     * @param integer $recordId (optional)
     *
     * @return void
     */
    public function checkIn($tableName, $user = null, $recordId = null)
    {
	Db::table($tableName)->where(function($query) use($user, $recordId) {
	                                 if ($user) {
					     $query->where('checked_out', $user->id);
					 }

	                                 if ($recordId) {
					     $query->where('id', $recordId);
					 }
				    })->update(['checked_out' => null,
						'checked_out_time' => null]);
    }

    /**
     * Builds and returns the check-in html code to display.
     *
     * @param objects record$
     * @param User    $user
     *
     * @return string
     */
    public function getCheckInHtml($record, $user, $columnValue)
    {
	$userName = $user->first_name.' '.$user->last_name;
	$html = '<div class="checked-out">'.$columnValue.'<span class="lock"></span></div>';
	$html .= '<div class="check-in"><p class="user-check-in">'.$userName.'</p>'.Backend::dateTime($record->checked_out_time).'</div>';

	return $html;
    }

    /**
     * Returns the css status mapping.
     *
     * @return array
     */
    public function getStatusIcons()
    {
        return ['published' => 'success', 'unpublished' => 'danger', 'archived' => 'muted']; 
    }

    /**
     * Checks that all the fields in the global settings are correctly set.
     *
     * @return boolean
     */
    public function checkSettings()
    {
      $fields = ['renewal_day', 'renewal_month', 'renewal_period', 'free_period', 'reminder_renewal',
		 'revocation', 'subscription_fee', 'insurance_fee_f1', 'insurance_fee_f2'];

      foreach ($fields as $field) {
	  if (!Settings::get($field, null)) {
	      return false;
	  }
      }

      return true;
    }
}
