<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Codalia\Membership\Models\Settings;
use Codalia\Membership\Models\Member;
use Carbon\Carbon;
use Backend;
use Flash;
use Db;


class RenewalHelper
{
    use Singleton;


    public function isRenewalPeriod()
    {
        $renewalDay = Settings::get('renewal_day', null);
        $renewalMonth = Settings::get('renewal_month', null);
        $daysPeriod = Settings::get('renewal_period', null);

	if (!$renewalDay || !$renewalMonth || !$daysPeriod) {
	    return;
	}

	// Checks against the current year.
	$renewal = new \DateTime(date('Y').'-'.$renewalMonth.'-'.$renewalDay);
	$now = new \DateTime(date('Y-m-d'));

	// The renewal period for the current year is passed.
	if ($now > $renewal) {
	    return false;
	}

	$period = clone $renewal;
	// Subtracts x days to get the begining of the renewal period as well as the reminder sending.
	$period->sub(new \DateInterval('P'.$daysPeriod.'D'));
    }

    public function jobDone($jobName)
    {
        $path = plugins_path().'/codalia/membership/helpers/done-jobs';
	fopen($path.'/'.$jobName, 'w');
    }

    public function isJobDone($jobName)
    {
        $path = plugins_path().'/codalia/membership/helpers/done-jobs';

	if (file_exists($path.'/'.$jobName)) {
	    return true;
	}

	return false;
    }

    public function deleteJob($jobName)
    {
        $path = plugins_path().'/codalia/membership/helpers/done-jobs';
	@unlink($path.'/'.$jobName);
    }

    /*
     * Resets all the member status to pending_renewal and the payment 'last' flag to zero. 
     * Resets all the running insurances as well.
     */
    public function setRenewalPendingStatus()
    {
	Db::table('codalia_membership_members AS m')->join('codalia_membership_payments AS p', 'p.member_id', '=', 'm.id')
						    ->where('m.status', 'member')
						    ->update(['m.status' => 'pending_renewal',
							      'm.updated_at' => Carbon::now(),
							      'p.last' => 0]);

	Db::table('codalia_membership_insurances')->where('status', 'running')
						  ->update(['status' => 'pending_renewal',
							    'updated_at' => Carbon::now()]);
    }

    /*
     * Ensures that members are no longer displayed in the member list and their insurance
     * is no longer running.
     */
    public function disableMembers()
    {
	Db::table('codalia_membership_members AS m')->join('codalia_membership_insurances AS i', 'i.member_id', '=', 'm.id')
						    ->where('m.status', 'pending_renewal')
						    // Removes members from the member list.
						    ->update(['m.member_list' => 0,
							      'm.updated_at' => Carbon::now(),
							      // Insurance is no longer running
							      'i.status' => 'disabled',
							      'i.updated_at' => Carbon::now()]);
    }

    public function revokeMembers()
    {
        $ids = Member::where('status', 'pending_renewal')->get()->pluck('id')->toArray();

	foreach ($ids as $id) {
	    $member = Member::find($id);
	    $member->cancelMembership('revoked');
	}

	return count($ids);
    }

    public function getRenewalDate($latestDate = true)
    {
        $renewalDay = Settings::get('renewal_day', null);
        $renewalMonth = Settings::get('renewal_month', null);

	// Checks first against the current year.
	$renewal = new \DateTime(date('Y').'-'.$renewalMonth.'-'.$renewalDay);

	$now = new \DateTime(date('Y-m-d'));

	// The renewal period for the current year is passed.
	if ($latestDate && $now > $renewal) {
	    // Sets date to the next year.
	    $renewal->add(new \DateInterval('P1Y'));
	}

	return $renewal;
    }

    /*
     * If the candidates subscribe and pay during this period (starting few months before the renewal
     * day), their current subscription fee payment will be actually taken into account on
     * the renewal day. Meaning that the months before the renewal day are for free.  
     */
    public function isFreePeriod()
    {
        $renewal = self::getRenewalDate();
        $daysPeriod = Settings::get('free_period', null);

	$freePeriod = clone $renewal;
	$freePeriod->sub(new \DateInterval('P'.$daysPeriod.'D'));
	$now = new \DateTime(date('Y-m-d'));

	if ($now >= $freePeriod && $now < $renewal) {
	    return true;
	}

	return false;
    }

    public function checkRenewal()
    {
        $renewal = self::getRenewalDate();
        $daysPeriod = Settings::get('renewal_period', null);
        $daysReminder = Settings::get('reminder_renewal', null);
	$now = new \DateTime(date('Y-m-d'));

	$period = clone $renewal;
	$reminder = clone $renewal;
	// Subtracts x days to get the begining of the renewal period.
	$period->sub(new \DateInterval('P'.$daysPeriod.'D'));

        // Sets the reminder sending.

	// x days before the renewal date.
	if (substr($daysReminder, 0, 1 ) == '-') {
	    $daysReminder = ltrim($daysReminder, '-');
	    $reminder->sub(new \DateInterval('P'.$daysReminder.'D'));
	}
	// x days after the renewal date.
	elseif ($daysReminder != 0) {
	    // In case the renewal date has passed, gets the renewal date for the current year.
	    $reminder = self::getRenewalDate(false);
	    $reminder->add(new \DateInterval('P'.$daysReminder.'D'));
	}

	// NB: If $daysReminder is zero reminder date is equal to renewal date.

	// The renewal time period has started.
	if ($now >= $period && !self::isJobDone('renewal')) {
	    self::setRenewalPendingStatus();

	    self::jobDone('renewal');
	    // Deletes the reminder job from the previous checking.
	    self::deleteJob('reminder');

	    // Informs the members.
	    \Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal();

	    return 'renewal';
	}
	// Checks for the reminder sending. (N.B: It can be set during or after the renewal period).
	elseif ($now >= $reminder && !self::isJobDone('reminder')) {
	    self::jobDone('reminder');
	    // Reminds the members who haven't paid yet.
	    \Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal('reminder');

	    return 'reminder';
	}
        // The renewal period is over, (or hasn't started yet).
	elseif ($now < $period && self::isJobDone('renewal')) {
	    // Disables the members who haven't paid yet.
	    self::disableMembers();
	    self::deleteJob('renewal');
            // Possibly...
	    //\Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal('last_reminder');

	    return 'delete_renewal_job';
	}

        return 'none';
    }

    public function getSubscriptionStartDate()
    {
        $startDate = self::getRenewalDate();
        $oneYear = new \DateInterval('P1Y');
        $startDate->sub($oneYear);

        return $startDate;
    }

    public function getSubscriptionEndDate()
    {
        $endDate = self::getRenewalDate();
        $oneDay = new \DateInterval('P1D');
        $endDate->sub($oneDay);

        return $endDate;
    }

    /*
     * For test purpose only.
     */
    public function _testScheduler()
    {
        file_put_contents('debog_scheduler.txt', print_r('scheduler ', true), FILE_APPEND);
    }
}
