<?php namespace Codalia\Membership\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Membership\Models\Member;
use BackendAuth;
use Lang;
use Flash;

/**
 * Members Back-end Controller
 */
class Members extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string Configuration file for the `ListController` behavior.
     */
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.Membership', 'membership', 'members');
    }

    public function listInjectRowClass($record, $definition = null)
    {
      //file_put_contents('debog_file.txt', print_r($record->user->profile->first_name, true), FILE_APPEND);
    }

    public function update_onEditUser($recordId = null)
    {
        $user = BackendAuth::getUser();
        $member = Member::find($recordId);

	// Checks for check out matching.
	if ($member->checked_out && $user->id != $member->checked_out) {
	    Flash::error(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));
	    //return redirect('backend/codalia/journal/articles');
	    return;
	}

        file_put_contents('debog_file.txt', print_r($member->profile->first_name, true), FILE_APPEND);
    }

    public function update_onSaveUser($recordId = null)
    {
        $member = Member::find($recordId);
    }
}
