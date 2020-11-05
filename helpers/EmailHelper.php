<?php namespace Codalia\Membership\Helpers;

use October\Rain\Support\Traits\Singleton;
use Carbon\Carbon;
use Backend;
use Codalia\Membership\Models\Member;
use Backend\Models\UserGroup;
use Mail;
use Flash;
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
	    $message->subject('Your application');
	});

	// Fetches the emails of the user belonging to the Office group.
        $emails = UserGroup::where('code', 'office')->first()->users->pluck('email')->toArray();

	if (!empty($emails)) {
	    Mail::send('codalia.membership::mail.alert_office', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject('New application');
	    });
	}
    }

    /**
     * 
     *
     * @param integer    $recordId
     *
     * @return void
     */
    public function alertMembers($recordId)
    {
        $candidate = Member::find($recordId)->profile;
	// Fetches the emails of the user belonging to the Member group.
        $emails = UserGroup::where('code', 'member')->first()->users->pluck('email')->toArray();

	//file_put_contents('debog_file.txt', print_r($emails, true));

	if (!empty($emails)) {
	    $vars = ['first_name' => $candidate->first_name, 'last_name' => $candidate->last_name];

	    Mail::send('codalia.membership::mail.alert_members', $vars, function($message) use($emails) {
		$message->to($emails, 'Admin System');
		$message->subject('New application');
	    });
	}
    }

}
