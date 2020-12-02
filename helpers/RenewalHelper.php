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
        $path = plugins_path().'/codalia/membership/helpers/jobs';
	fopen($path.'/'.$jobName, 'w');
    }

    public function isJobDone($jobName)
    {
        $path = plugins_path().'/codalia/membership/helpers/jobs';

	if (file_exists($path.'/'.$jobName)) {
	    return true;
	}

	return false;
    }

    public function deleteJob($jobName)
    {
        $path = plugins_path().'/codalia/membership/helpers/jobs';
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

    public function getRenewalDate($extraDays = 0)
    {
        $renewalDay = Settings::get('renewal_day', null);
        $renewalMonth = Settings::get('renewal_month', null);

	// Checks first against the current year.
	$renewal = new \DateTime(date('Y').'-'.$renewalMonth.'-'.$renewalDay);

	if ($extraDays) {
	    // Adds x extra days to the renewal period.
	    $renewal->add(new \DateInterval('P'.$extraDays.'D'));
	}

	$now = new \DateTime(date('Y-m-d'));

	// The renewal period for the current year is passed.
	if ($now > $renewal) {
	    // Sets date to the next year.
	    $renewal->add(new \DateInterval('P1Y'));
	}

	return $renewal;
    }

    public function checkRenewal()
    {
        $renewal = self::getRenewalDate();
        $daysPeriod = Settings::get('renewal_period', null);
        $daysReminder = Settings::get('reminder_renewal', null);
	$now = new \DateTime(date('Y-m-d'));

	$period = clone $renewal;
	$reminder = clone $renewal;
	// Subtracts x days to get the begining of the renewal period as well as the reminder sending.
	$period->sub(new \DateInterval('P'.$daysPeriod.'D'));
	$reminder->sub(new \DateInterval('P'.$daysReminder.'D'));

	// The renewal time period has started.
	if ($now >= $period && $now < $reminder && !self::isJobDone('renewal')) {
	    self::setRenewalPendingStatus();
	    self::jobDone('renewal');
	    // Informs the members.
	    \Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal();

	    return 'renewal';
	}
	// Now checks for the reminder sending.
	elseif ($now >= $reminder && $now < $renewal && !self::isJobDone('reminder')) {
	    self::jobDone('reminder');
	    // Reminds the members.
	    \Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal('reminder');

	    return 'reminder';
	}

	return self::checkRevocation();
    }

    /*
     *
     */
    public function checkRevocation()
    {
        $daysRevocation = (int)Settings::get('revocation', 0);
        $revocation = self::getRenewalDate($daysRevocation);
	$renewal = clone $revocation;
	// Subtracts x days to get the end of the renewal period.
	$renewal->sub(new \DateInterval('P'.$daysRevocation.'D'));

	$now = new \DateTime(date('Y-m-d'));

	if ($now >= $renewal && $now < $revocation && !self::isJobDone('last_reminder')) {
	    self::disableMembers();
	    self::jobDone('last_reminder');
	    \Codalia\Membership\Helpers\EmailHelper::instance()->alertRenewal('last_reminder');
	    return 'last_reminder';
	}

	if ($now == $revocation) {
	    // Deletes jobs from the previous checking.
	    self::deleteJob('renewal');
	    self::deleteJob('reminder');
	    self::deleteJob('last_reminder');

	    return self::revokeMembers();
	}

	return 'none';
    }
}
