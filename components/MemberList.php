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

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
        parent::prepareVars();
    }

    public function onTest()
    {
        Flash::success(Lang::get('codalia.membership::lang.action.file_replace_success'));
    }
}
