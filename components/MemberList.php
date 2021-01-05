<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Models\AppealCourt;
use Flash;
use Lang;


class MemberList extends ComponentBase
{
    public $members = null;
    public $appealCourts = null;


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
	$this->appealCourts = $this->loadAppealCourts();
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
    }

    protected function listMembers()
    {
        $members = MemberModel::where('member_list', 1)->get();

	return $members;
    }

    protected function loadAppealCourts()
    {
        return AppealCourt::get();
    }
}
