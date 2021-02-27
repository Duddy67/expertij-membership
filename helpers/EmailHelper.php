<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Carbon\Carbon;
use Backend;
use BackendAuth;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Document;
use Codalia\Membership\Models\Payment;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
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
    public function alertDecisionMakers($memberId)
    {
        $candidate = Member::find($memberId)->profile;
	// Fetches the emails of the users belonging to the Decision Maker group.
	if (!$group = UserGroup::where('code', 'decision-maker')->first()) {
	    throw new \ApplicationException('Error: No group called "decision-maker" !');
	}

        $emails = $group->users->pluck('email')->toArray();


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

	$langVar = ($data['mode'] == 'free_period') ? 'free_period_validated' : 'payment_'.$data['status'];

	Mail::send('codalia.membership::mail.'.$langVar, $vars, function($message) use($member, $data, $vars, $langVar) {
	    $message->to($member->profile->user->email, 'Admin System');
	    $message->subject(Lang::get('codalia.membership::lang.email.'.$langVar));

	    if ($data['status'] == 'completed' && isset($data['invoice_path'])) {
		$message->attach($data['invoice_path'], ['as' => $data['invoice_name']]);
	    }
	});

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.'.$langVar.'_admin', $vars, function($message) use($emails, $data, $langVar) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.'.$langVar.'_admin'));
	    });
	}
    }

    /**
     * 
     *
     * @param integer    $documentId
     *
     * @return void
     */
    public function alertDocument($documentId)
    {
        $document = Document::find($documentId);
	// Prepares data.
	$data = [];
	$data['licence_types'] = (!empty($document->licence_types)) ? explode(',', $document->licence_types) : [];
	$data['appeal_courts'] = (!empty($document->appeal_courts)) ? explode(',', $document->appeal_courts) : [];
	$data['courts'] = (!empty($document->courts)) ? explode(',', $document->courts) : [];
	$data['languages'] = (!empty($document->languages)) ? explode(',', $document->languages) : [];

	// Searches members from the Profile relationship as it contained most of the relevant data to search for.
	$profiles = Profile::whereHas('licences', function($query) use($data) {

			if (!empty($data['licence_types'])) {
			    $query->whereIn('type', $data['licence_types']);

			    if (!empty($data['appeal_courts'])) {
				$query->whereIn('appeal_court_id', $data['appeal_courts']);

			        if (empty($data['courts'])) {
				    $query->orWhereNull('court_id');
				}
			    }

			    if (!empty($data['courts'])) {
			        if (!empty($data['appeal_courts'])) {
				    $query->orWhereIn('court_id', $data['courts']);
				}
				else {
				    $query->whereIn('court_id', $data['courts'])->orWhereNull('appeal_court_id');
				}
			    }
			}

			$query->whereHas('attestations', function($query) use($data) {
			    //
			    if (!empty($data['languages'])) {
				$query->whereHas('languages', function($query) use($data) { 
					$query->whereIn('alpha_2', $data['languages']);
				}); 
			    }
			});
		    })->whereHas('member', function($query) {
			$query->where('member_list', 1)->where(function ($query) {
			    $query->where('status', 'member');

			    $now = new \DateTime(date('Y-m-d'));
			    $renewalDate = \Codalia\Membership\Helpers\RenewalHelper::instance()->getRenewalDate();

			    if ($now->format('Y-m-d') < $renewalDate->format('Y-m-d')) {
				$query->orWhere('status', 'pending_renewal');
			    }
			});
		    })->get();

	$emails = [];

	foreach ($profiles as $profile) {
	    $emails[] = $profile->user->email;
	}

	// Prepares variables.
	$vars = ['title' => $document->title,
        ];

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.alert_document', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject(Lang::get('codalia.membership::lang.email.new_document'));
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
