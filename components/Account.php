<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberItem;
use Auth;
use Input;

class Account extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Account Membership Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
	$this->member = $this->page['member'] = $this->loadMember();
    }

    protected function loadMember()
    {
        $user = Auth::getUser();
	$member = new MemberItem;

	$member = $member->where('user_id', $user->id);

	if (($member = $member->first()) === null) {
	    return null;
	}

	//var_dump($member->name);
	return $member;
    }

    public function onReplaceDocument()
    {
        $member = $this->loadMember();

	if (Input::hasFile('attestation')) {
	    $member->attestations = Input::file('attestation');
	    $member->save();
	}

file_put_contents('debog_file.txt', print_r($member->profile, true));
    }

}
