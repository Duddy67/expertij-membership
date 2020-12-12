<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Flash;
use Lang;


class MemberList extends ComponentBase
{
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
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
    }

    public function onTest()
    {
        Flash::success(Lang::get('codalia.membership::lang.action.file_replace_success'));
    }
}
