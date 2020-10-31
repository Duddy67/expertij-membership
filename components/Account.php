<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberItem;
use Codalia\Profile\Models\Profile;
use Auth;
use Input;

class Account extends \Codalia\Profile\Components\Account
{
    public function componentDetails()
    {
        return [
            'name'        => 'Account Membership Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
	$this->member = $this->page['member'] = $this->loadMember();

        parent::prepareVars();
    }

    protected function loadMember()
    {
        // Gets the current user.
        $user = Auth::getUser();
	// Loads the corresponding member through the profile_id attribute.
	$profileId = Profile::where('user_id', $user->id)->pluck('id');
	$member = new MemberItem;
	$member = $member->where('profile_id', $profileId);

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

//file_put_contents('debog_file.txt', print_r($member->profile, true));
    }

}
