<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Carbon\Carbon;
use Backend;
use BackendAuth;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Settings;
use Backend\Models\UserGroup;
use System\Classes\PluginManager;
use Renatio\DynamicPDF\Classes\PDF; // import facade
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
    public function alertDecisionMakers($memberId)
    {
        $candidate = Member::find($memberId)->profile;
	// Fetches the emails of the users belonging to the Decision Maker group.
        $emails = UserGroup::where('code', 'decision-maker')->first()->users->pluck('email')->toArray();


	if (!empty($emails)) {
	    $vars = ['first_name' => $candidate->first_name, 'last_name' => $candidate->last_name];

	    Mail::send('codalia.membership::mail.alert_decision_makers', $vars, function($message) use($emails) {
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

    public function alertRenewal($type = null)
    {
        $emails = Member::where('status', 'pending_renewal')->with('profile.user')->get()->pluck('profile.user.email')->toArray();

	if (!empty($emails)) {
	    $renewal = \Codalia\Membership\Helpers\RenewalHelper::instance()->getRenewalDate()->format('d/m/Y');
	    $vars = ['renewal' => $renewal, 'subscription_fee' => Settings::get('subscription_fee', 0)];
	    $pattern = ($type) ? 'pending_renewal_'.$type : 'pending_renewal';

	    if ($type == 'last_reminder') {
		$daysRevocation = (int)Settings::get('revocation', 0);
		$vars['limit_date'] = \Codalia\Membership\Helpers\RenewalHelper::instance()->getRenewalDate($daysRevocation)->format('d/m/Y');
	    }

	    Mail::send('codalia.membership::mail.'.$pattern, $vars, function($message) use($emails, $pattern) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.'.$pattern));
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
    public function alertPayment($memberId, $data)
    {
        $member = Member::find($memberId);
	// Fetches the emails of the users belonging to the Office group.
        $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();
	// Prepares variables.
	$vars = ['first_name' => $member->profile->first_name,
		 'last_name' => $member->profile->last_name,
		 'amount' => $data['amount'],
		 'item' => $data['item'],
		 'item_name' => Lang::get('codalia.membership::lang.payment.'.$data['item']),
		 'payment_mode' => $data['mode'],
		 'reference' => 'xxxxxxxxxx',
        ];

	if (substr($data['item'], 0, 12) === 'subscription') {
	    $vars['subscription_fee'] = Payment::getAmount('subscription');
	}

	// The user has paid only for insurance or for both subscription and insurance.
	if (substr($data['item'], 0, 9) === 'insurance' || substr($data['item'], 0, 22) === 'subscription-insurance') {
	    // Removes the 'subscription-' part from the item code.
	    $insurance = (substr($data['item'], 0, 9) === 'insurance') ? $data['item'] : substr($data['item'], 13); 

	    $vars['insurance_fee'] = Payment::getAmount($insurance);
	    $vars['insurance_name'] = Lang::get('codalia.membership::lang.payment.'.$insurance);
	}

	Mail::send('codalia.membership::mail.payment_'.$data['status'], $vars, function($message) use($member, $data, $vars) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.payment_'.$data['status']));

	    if (PluginManager::instance()->exists('Renatio.DynamicPDF')) {
		$tempFile = tempnam(sys_get_temp_dir(), 'inv');
		PDF::loadTemplate('invoice-membership', $vars)->save($tempFile);

		$message->attach($tempFile, ['as' => 'Your_Invoice.pdf']);
	    }
	});

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.payment_'.$data['status'].'_admin', $vars, function($message) use($emails, $data) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.payment_'.$data['status'].'_admin'));
	    });
	}
    }

    public function statusChange($memberId, $newStatus, $isNewMember = false)
    {
        $member = Member::find($memberId);
	$vars = ['first_name' => $member->profile->first_name, 'last_name' => $member->profile->last_name, 'subscription_fee' => Settings::get('subscription_fee', 0)];

	if ($newStatus == 'member' && $isNewMember) {
	    $status = 'new_member';
	}
	elseif ($newStatus == 'member' && !$isNewMember) {
	    $status = 'renewal_subscription';
	}
	// refused, pending_subscription, cancelled, revoked, cancellation
	else {
	    $status = $newStatus;
	}

	Mail::send('codalia.membership::mail.'.$status, $vars, function($message) use($member, $status) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.'.$status));
	});

	// Informs the admins about the member's cancellation.
	if ($status == 'cancellation') {
	    // Fetches the emails of the users belonging to the Office group.
	    $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();

	    if (!empty($emails)) {
		Mail::send('codalia.membership::mail.cancellation_admin', $vars, function($message) use($emails) {
		    $message->to($emails, 'Admin System');
		    $message->subject(Lang::get('codalia.membership::lang.email.cancellation'));
		});
	    }
	}
    }

    public function alertChequePayment($member, $data)
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

    public function member($memberId, $pattern)
    {
        $member = Member::find($memberId);
	$vars = ['first_name' => $member->profile->first_name, 'last_name' => $member->profile->last_name];

	Mail::send('codalia.membership::mail.'.$pattern, $vars, function($message) use($member, $pattern) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.'.$pattern));
	});
    }
}
