<?php namespace Codalia\Membership\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Membership\Models\Document;
use Codalia\Membership\Helpers\MembershipHelper;
use Codalia\Membership\Helpers\EmailHelper;
use October\Rain\Database\Models\DeferredBinding;
use BackendAuth;
use Carbon\Carbon;
use Lang;
use Flash;


/**
 * Documents Back-end Controller
 */
class Documents extends Controller
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

        BackendMenu::setContext('Codalia.Membership', 'membership', 'documents');
    }


    public function index()
    {
	$this->vars['statusIcons'] = MembershipHelper::instance()->getStatusIcons();
	$this->addCss(url('plugins/codalia/membership/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).
	MembershipHelper::instance()->checkIn((new Document)->getTable(), BackendAuth::getUser());
	// Removes orphan files from the server (ie: In case some files have been uploaded in an unsaved document).
	DeferredBinding::cleanUp(0);
	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
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
        if ($record->checked_out && $columnName == 'title') {
	    return MembershipHelper::instance()->getCheckInHtml($record, BackendAuth::findUserById($record->checked_out), $record->title);
	}
    }

    public function index_onSetStatus()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = MembershipHelper::instance()->getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $status = post('status');
	  $count = 0;
	  foreach ($checkedIds as $recordId) {
	      $document = Document::find($recordId);

	      if ($document->checked_out) {
		  Flash::error(Lang::get('codalia.membership::lang.action.checked_out_item', ['name' => $document->title]));
		  return $this->listRefresh();
	      }

	      $document->status = $status;
	      $document->published_up = Document::setPublishingDate($document);
	      // Important: Do not use the save() or update() methods here as the events (afterSave etc...) will be 
	      //            triggered as well and may have unexpected behaviors.
	      \Db::table('codalia_membership_documents')->where('id', $recordId)->update(['status' => $status,
										   'published_up' => Document::setPublishingDate($document)]);
	      $count++;
	  }

	  $toRemove = ($status == 'archived') ? 'd' : 'ed';

	  Flash::success(Lang::get('codalia.membership::lang.action.'.rtrim($status, $toRemove).'_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function index_onCheckIn()
    {
	// Needed for the status column partial.
	//$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $count = 0;
	  foreach ($checkedIds as $recordId) {
	      MembershipHelper::instance()->checkIn((new Document)->getTable(), null, $recordId);
	      $count++;
	  }

	  Flash::success(Lang::get('codalia.membership::lang.action.check_in_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function update_onSave($recordId = null, $context = null)
    {
	// Calls the original update_onSave method
	if ($redirect = $this->asExtension('FormController')->update_onSave($recordId, $context)) {
	    return $redirect;
	}

	$fieldMarkup = $this->formRenderField('updated_at', ['useContainer' => false]);

	return ['#partial-updatedAt' => $fieldMarkup];
    }

    public function update_onSendEmailToMembers($recordId = null)
    {
	EmailHelper::instance()->alertDocument($recordId);
	$document = Document::find($recordId);
	$document->last_email_sending = Carbon::now();
	$document->save();
	$this->initForm($document);
	$fieldMarkup = $this->formRenderField('last_email_sending', ['useContainer' => false]);

	Flash::success(Lang::get('codalia.membership::lang.action.email_sendings_success'));

	return ['#partial-lastEmailSending' => $fieldMarkup];
    }

    public function loadScripts()
    {
        $this->addCss(url('plugins/codalia/membership/assets/css/extra.css'));
	$this->addJs('/plugins/codalia/membership/assets/js/document.js');
    }
}
