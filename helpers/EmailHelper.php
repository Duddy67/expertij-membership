<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Carbon\Carbon;
use Backend;
use BackendAuth;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Settings;
use Backend\Models\UserGroup;
use Mail;
use Flash;
use Lang;
use Db;


class EmailHelper
{
    use Singleton;


    /**
     * 
     *
     * @param Member    $member
     *
     * @return void
     */
    public function afterRegistration($member)
    {
	$vars = ['first_name' => $member->profile->first_name, 'last_name' => $member->profile->last_name];

	Mail::send('codalia.membership::mail.candidate_application', $vars, function($message) use($member) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.your_application'));
	});

	// Fetches the emails of the user belonging to the Office group.
        $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.alert_office', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.new_application'));
	    });
	}
    }

    /**
     * 
     *
     * @param integer    $memberId
     *
     * @return void
     */
    public function alertMembers($memberId)
    {
        $candidate = Member::find($memberId)->profile;
	// Fetches the emails of the users belonging to the Member group.
        $emails = UserGroup::where('code', 'member')->first()->users->pluck('email')->toArray();

	//file_put_contents('debog_file.txt', print_r($emails, true));

	if (!empty($emails)) {
	    $vars = ['first_name' => $candidate->first_name, 'last_name' => $candidate->last_name];

	    Mail::send('codalia.membership::mail.alert_members', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.new_application'));
	    });
	}
    }

    /**
     * 
     *
     * @param integer    $memberId
     *
     * @return void
     */
    public function alertVote($memberId)
    {
        $candidate = Member::find($memberId)->profile;
        $user = BackendAuth::getUser();
	// Fetches the emails of the users belonging to the Office group.
        $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();

	if (!empty($emails)) {
	  $vars = ['first_name' => $user->first_name, 'last_name' => $user->last_name,
		   'candidate_first_name' => $candidate->first_name, 'candidate_last_name' => $candidate->last_name];

	    Mail::send('codalia.membership::mail.alert_vote', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.new_vote'));
	    });
	}
    }

    public function statusChange($memberId, $newStatus, $oldStatus)
    {
        $member = Member::find($memberId);
	$vars = ['first_name' => $member->profile->first_name, 'last_name' => $member->profile->last_name, 'subscription_fee' => Settings::get('subscription_fee', 0)];

	if ($newStatus != 'pending' && $newStatus != 'member') {
	    $status = $newStatus;
	}
	elseif ($newStatus == 'member' && $oldStatus == 'pending_subscription') {
	    $status = 'new_member';
	}
	elseif ($newStatus == 'member' && $oldStatus == 'pending_renewal') {
	    $status = 'renewal_subscription';
	}
	else {
	    return;
	}

	Mail::send('codalia.membership::mail.'.$status, $vars, function($message) use($member, $status) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.'.$status));
	});
    }

    public function chequePayment($member)
    {
	$vars = ['first_name' => $member->profile->first_name, 'last_name' => $member->profile->last_name, 'subscription_fee' => Settings::get('subscription_fee', 0)];

	Mail::send('codalia.membership::mail.cheque_payment', $vars, function($message) use($member) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.cheque_payment'));
	});

	// Fetches the emails of the users belonging to the Office group.
        $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.alert_cheque_payment', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.alert_cheque_payment'));
	    });
	}
    }
}
