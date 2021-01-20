<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Flash;
use Lang;


class MemberList extends ComponentBase
{
    public $members = null;


    public function componentDetails()
    {
        return [
            'name'        => 'MemberList Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->prepareVars();

	$this->members = $this->listMembers();
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
	$thumbSize = explode(':', Settings::get('photo_thumbnail', '100:100'));
	$this->page['thumbSize'] = ['width' => $thumbSize[0], 'height' => $thumbSize[1]];

	/*$profiles = Profile::whereHas('licences', function($query) {
			$query->where('type', 'ceseda')->whereHas('languages', function($query) {
			    $query->where('alpha_2', 'be');
			});
		    })->get();

	foreach ($profiles as $profile) {
	  echo $profile->last_name;
	}*/
    }

    protected function listMembers()
    {
        $members = MemberModel::where('member_list', 1)->get();

	return $members;
    }
}
