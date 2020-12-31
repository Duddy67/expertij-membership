<?php namespace Codalia\Membership\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Membership\Models\Member;
use Codalia\Membership\Models\Vote;
use Codalia\Profile\Models\Profile;
use Codalia\Membership\Helpers\MembershipHelper;
use Codalia\Membership\Helpers\EmailHelper;
use Codalia\Membership\Helpers\RenewalHelper;
use Codalia\Profile\Helpers\ProfileHelper;
use BackendAuth;
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
        $this->prepareVotes($recordId);

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
        if ($record->checked_out && $columnName == '_first_name') {
	    return MembershipHelper::instance()->getCheckInHtml($record, BackendAuth::findUserById($record->checked_out), $record->_first_name);
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

	  Flash::success(Lang::get('codalia.membership::lang.action.check_in_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function index_onCheckRenewal()
    {
	$result = RenewalHelper::instance()->checkRenewal();

	if (is_string($result)) {
	    Flash::success(Lang::get('codalia.membership::lang.action.check_renewal_'.$result.'_success'));
	}
	// integer
	else {
	    Flash::success(Lang::get('codalia.membership::lang.action.revocation_success', ['count' => $result]));
	}
    }

    public function update_onEditUser($recordId = null)
    {
        $user = BackendAuth::getUser();
        $member = Member::find($recordId);

	// Checks for profile check out matching.
	if ($member->profile->checked_out && $user->id != $member->profile->checked_out) {
	    Flash::error(Lang::get('codalia.membership::lang.action.check_out_do_not_match'));
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

	Flash::success(Lang::get('codalia.membership::lang.action.profile_update_success'));

	return ['action' => 'disable'];
    }

    public function update_onCancelUserEditing($recordId = null)
    {
        $member = Member::find($recordId);
	MembershipHelper::instance()->checkIn((new Profile)->getTable(), null, $member->profile->id);

	return ['action' => 'disable'];
    }

    public function update_onVote($recordId = null)
    {
	$data = post();
	$vote = new Vote($data['Vote']);
        $member = Member::find($recordId);
	$member->votes()->save($vote);

	Flash::success(Lang::get('codalia.membership::lang.action.vote_success'));

	EmailHelper::instance()->alertVote($recordId);
    }

    public function update_onSave($recordId = null, $context = null)
    {
	$data = post();
        $member = Member::find($recordId);
	$originalStatus = $member->getOriginal('status');
	// The status drop down list is sometimes disabled depending on its setting (member, cancelled, etc...), therefore
	// its value is not passed through the edit form. 
	$newStatus = (isset($data['Member']['status'])) ? $data['Member']['status'] : $originalStatus;

	// Use case 1: A member (or candidate) has paid the subscription fee and the system set their status to 'member'. 
	//             Or a member has cancelled their subscription through the cancellation button.
	//             Or the system has just set some of the member statuses to 'revoked'.
	//             Meanwhile an administrator is updating the data with a different status value which going to erase the status value set by the system. 
	if ($newStatus != 'member' && $originalStatus == 'member') {
	    Flash::warning(Lang::get('codalia.membership::lang.action.status_changed_by_system'));
	    return;
	}

	// Use case 2: The system has just set the member statuses to 'pending_renewal'.
	//             Meanwhile an administrator is updating the data while the form status value is still set to 'member'.
	//        N.B: As the status drop down list is disabled when set to 'member', the new status won't be erased as the drop down
	//             list value is not passed. So no need to stop the workflow. 

	// Calls the original update_onSave method
	$this->asExtension('FormController')->update_onSave($recordId, $context);

	// The possible (ie: authorized) changes.
	$options = ['refused', 'pending_subscription', 'cancelled', 'revoked'];

	// The status has changed.
	if (in_array($newStatus, $options) && $newStatus != $originalStatus) {
	    EmailHelper::instance()->statusChange($recordId, $newStatus);
	}

        if ($redirect = $this->makeRedirect('update', $member)) {
            return $redirect;
        }
    }

    public function loadScripts()
    {
        $this->addCss(url('plugins/codalia/membership/assets/css/extra.css'));
	$this->addJs('/plugins/codalia/membership/assets/js/member.js');
    }

    public function update_onSendEmailToDecisionMakers($recordId = null)
    {
	EmailHelper::instance()->alertDecisionMakers($recordId);
	Member::find($recordId)->increment('email_sendings');
	$member = Member::find($recordId);
	Flash::success(Lang::get('codalia.membership::lang.action.email_sendings_success'));

	return ['#btn-email-sendings' => Lang::get('codalia.membership::lang.action.email_sendings', ['count' => $member->email_sendings])];
    }

    protected function prepareVotes($recordId)
    {
        $this->vars['canVote'] = false;
	$member = Member::find($recordId);

        if ($member->status == 'pending' && $this->user->role->code == 'decision-maker') {
	    $this->vars['canVote'] = true;
	}

	$this->vars['vote'] = $member->votes->first( function($item) {
	    return $item->user_id == $this->user->id;
	});

        if ($this->user->role->code != 'decision-maker') {
	    $votes = [];
	    foreach ($member->votes as $vote) {
		$user = BackendAuth::findUserById($vote->user_id);
		$vote->first_name = $user->first_name;
		$vote->last_name = $user->last_name;
		$votes[] = $vote;
	    }

	    $this->vars['votes'] = $votes;
	}
    }

    public function update_onSavePayment($recordId = null)
    {
	$data = post();
	// Sets the item type (ie: subscription or insurance).
	$itemType = (isset($data['_subscription_payment_status'])) ? 'subscription' : 'insurance';

	if ($data['_'.$itemType.'_payment_status'] == 'pending') {
	    return;
	}

	// Prepares the required variables. 
	$data['status'] = $data['_'.$itemType.'_payment_status']; 
	$data['transaction_id'] = $data['_'.$itemType.'_transaction_id']; 
	$data['mode'] = $data['_'.$itemType.'_payment_mode']; 
	$data['item'] = $data['_'.$itemType.'_item_code']; 
	$data['amount'] = $data['_'.$itemType.'_amount']; 

	$member = Member::find($recordId);
	$member->savePayment($data);

	Flash::success(Lang::get('codalia.membership::lang.action.payment_update_success'));

	return [
	    '#save-payment-button' => '',
	    //'#payment-status-select' => '<input type="text" name="_payment_status" id="payment-status" value="'.$data['_payment_status'].'" disabled="disabled" class="form-control">'
	];
    }
}
