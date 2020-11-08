<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberItem;
use Codalia\Profile\Models\Profile;
use Auth;
use Input;
use Validator;
use ValidationException;
use Flash;
use Lang;
use Redirect;
use System\Models\File;


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

    public function onReplaceFile()
    {
        $member = $this->loadMember();

	if (Input::hasFile('attestation')) {
	    $member->attestations = Input::file('attestation');
	    $member->save();
	}

        Flash::success(Lang::get('codalia.membership::lang.action.file_replace_success'));
    }

    public function onUploadDocument()
    {
        $input = Input::all();

        $file = (new File())->fromPost($input['attestation']);

        return[
            '#newFile' => '<a class="btn btn-danger btn-lg" target="_blank" href="'.$file->getPath().'"><span class="glyphicon glyphicon-download"></span>Download</a>'
        ];
    }

    public function onUpdate()
    {
        parent::onUpdate();
    }
}
