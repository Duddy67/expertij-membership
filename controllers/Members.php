<?php namespace Codalia\Membership\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Membership\Models\Member;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Helpers\MembershipHelper;
use Codalia\Profile\Helpers\ProfileHelper;
use BackendAuth;
use Mail;
use Validator;
use Input;
use ValidationException;
use Lang;
use Flash;

use October\Rain\Database\Models\DeferredBinding;


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


    public function index()
    {
	$this->addCss(url('plugins/codalia/membership/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).
	MembershipHelper::instance()->checkIn((new Member)->getTable(), BackendAuth::getUser());
	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
	//DeferredBinding::cleanUp();
    }

    public function update($recordId = null, $context = null)
    {
        //$this->vars['myvalue'] = 5;
	//$member = Member::find($recordId);
	//$user = BackendAuth::getUser();

	return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $class = '';

	if ($record->checked_out) {
	    $class = 'safe disabled nolink';
	}

	return $class;
    }

    public function listOverrideColumnValue($record, $columnName, $definition = null)
    {
        if ($record->checked_out && $columnName == 'name') {
	    return MembershipHelper::instance()->getCheckInHtml($record, BackendAuth::findUserById($record->checked_out));
	}
    }

    public function index_onCheckIn()
    {
	// Needed for the status column partial.
	//$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $count = 0;
	  foreach ($checkedIds as $recordId) {
	      MembershipHelper::instance()->checkIn((new Member)->getTable(), null, $recordId);
	      $count++;
	  }

	  Flash::success(Lang::get('codalia.journal::lang.action.check_in_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function update_onEditUser($recordId = null)
    {
        $user = BackendAuth::getUser();
        $member = Member::find($recordId);

	// Checks for profile check out matching.
	if ($member->profile->checked_out && $user->id != $member->profile->checked_out) {
	    Flash::error(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));
	    return ['action' => 'disable'];
	}

	// Locks the User plugin item for this user.
	MembershipHelper::instance()->checkOut((new Profile)->getTable(), $user, $member->profile->id);

	return ['action' => 'enable'];
    }

    public function update_onSaveUser($recordId = null)
    {
	$data = post();
        $rules = (new Profile)->rules;

	$validation = Validator::make($data['Member']['profile'], $rules);
	if ($validation->fails()) {
	    throw new ValidationException($validation);
	}

        $member = Member::find($recordId);
	$member->profile->update($data['Member']['profile']);

	Flash::success(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));

	return ['action' => 'disable'];
    }

    public function update_onCancelUserEditing($recordId = null)
    {
        $member = Member::find($recordId);
	MembershipHelper::instance()->checkIn((new Profile)->getTable(), null, $member->profile->id);

	return ['action' => 'disable'];
    }

    public function update_onSave($recordId = null, $context = null)
    {
        parent::update_onSave($recordId, $context);

        /*return[
            '#Form-field-Member-id-group' => '<a class="btn btn-danger btn-lg" target="_blank" href="#"><span class="glyphicon glyphicon-download"></span>Download</a>'
	];*/
    }

    public function loadScripts()
    {
        $this->addCss(url('plugins/codalia/membership/assets/css/extra.css'));
	$this->addJs('/plugins/codalia/membership/assets/js/member.js');
    }

    public function update_onSendEmailToMembers()
    {
        $vars = ['name' => 'John Doe', 'candidate' => 'Jane Smith'];

	Mail::send('codalia.membership::mail.alert_members', $vars, function($message) {
	    $message->to('lucas.sanner+member@gmail.com', 'Admin System');
	    $message->subject('New candidate');
	});
    }
}
