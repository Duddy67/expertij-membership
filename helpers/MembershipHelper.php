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
    public function getCheckInHtml($record, $user)
    {
	$userName = $user->first_name.' '.$user->last_name;
	$itemName = (isset($record->name)) ? $record->name : $record->title; 
	$html = '<div class="checked-out">'.$itemName.'<span class="lock"></span></div>';
	$html .= '<div class="check-in"><p class="user-check-in">'.$userName.'</p>'.Backend::dateTime($record->checked_out_time).'</div>';

	return $html;
    }

    public function jobDone($jobName)
    {
        $path = plugins_path().'/codalia/membership/jobs';
	fopen($path.'/'.$jobName, 'w');
    }

    public function isJobDone($jobName)
    {
        $path = plugins_path().'/codalia/membership/jobs';

	if (file_exists($path.'/'.$jobName)) {
	    return true;
	}

	return false;
    }

    public function deleteJob($jobName)
    {
        $path = plugins_path().'/codalia/membership/jobs';
	@unlink($path.'/'.$jobName);
    }

    public function setRenewalPendingStatus()
    {
	Db::table('codalia_membership_members')->where('status', 'member')
					       ->update(['status' => 'pending_renewal',
							 'updated_at' => Carbon::now()]);
    }

    public function checkRenewal()
    {
        $renewalDay = Settings::get('renewal_day', null);
        $renewalMonth = Settings::get('renewal_month', null);
        $daysPeriod = Settings::get('renewal_period', null);
        $daysReminder = Settings::get('reminder_renewal', null);

	if (!$renewalDay || !$renewalMonth || !$daysPeriod) {
	    return;
	}

	// First checks against the current year.
	$renewal = new \DateTime(date('Y').'-'.$renewalMonth.'-'.$renewalDay);
	$now = new \DateTime(date('Y-m-d'));

	// The renewal period for the current year is passed.
	if ($now > $renewal) {
	    // Checks against the next year.
	    $renewal->add(new \DateInterval('P1Y'));
	}

	$period = clone $renewal;
	$reminder = clone $renewal;
	// Subtracts x days to get the begining of the renewal period as well as the reminder sending.
	$period->sub(new \DateInterval('P'.$daysPeriod.'D'));
	$reminder->sub(new \DateInterval('P'.$daysReminder.'D'));

	// Renewal period hasn't started yet.
	if ($now < $period) {
	    // Deletes jobs from the previous checking.
	    self::deleteJob('renewal');
	    self::deleteJob('reminder');
	}
	// The renewal time period has started.
	elseif ($now >= $period && !self::isJobDone('renewal')) {
	    self::setRenewalPendingStatus();
	    self::jobDone('renewal');
	}
	// Now checks for the reminder sending.
	elseif ($now >= $reminder && !self::isJobDone('reminder')) {
	    self::jobDone('reminder');
	}
    }
}
