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

    public function setPendingStatus()
    {
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
	$period = clone $renewal;
	$reminder = clone $renewal;
	// Subtracts x days to get the begining of the renewal period as well as the reminder sending.
	$period->sub(new \DateInterval('P'.$daysPeriod.'D'));
	$reminder->sub(new \DateInterval('P'.$daysReminder.'D'));
	$now = new \DateTime(date('Y-m-d'));

	// The renewal period is halfway between two years.
	if ($now >= $renewal && $now <= $period) {
	    // Adds one more year to the renewal date.
	    $nextYear = date('Y') + 1;
	    $renewal = new \DateTime($nextYear.'-'.$renewalMonth.'-'.$renewalDay);
	}

	// Renewal date is passed.
	if ($now > $renewal) {
	    self::deleteJob('renewal');
	    self::deleteJob('reminder');
	}
	// The renewal time period has started.
	elseif ($now >= $period && $now <= $renewal && !self::isJobDone('renewal')) {
	    self::jobDone('renewal');
	    // Just in case.
	    self::deleteJob('reminder');
	}
	// Now checks for the reminder sending.
	elseif ($now >= $reminder && $now <= $renewal && !self::isJobDone('reminder')) {
	    self::jobDone('reminder');
	}
	//file_put_contents('debog_file.txt', print_r($renewal->format('Y-m-d'), true));
    }
}
