<?php namespace Codalia\Membership;

use Backend;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Codalia\Profile\Models\Profile as ProfileModel;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Controllers\Members as MembersController;
use Codalia\Membership\Helpers\MembershipHelper;
use Codalia\Membership\Helpers\EmailHelper;
use Codalia\Membership\Helpers\RenewalHelper;
use BackendAuth;
use Event;
use Input;
use Lang;
use Flash;

/**
 * Membership Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Membership',
            'description' => 'No description provided yet...',
            'author'      => 'Codalia',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
	Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            // Only for the Members controller
	    if (!$controller instanceof MembersController) {
		return;
	    } 

	    if ($action == 'update') {
		//$member = Member::find($params[0]);
		$member = $controller->formFindModelObject($params[0]);
		$user = BackendAuth::getUser();

		// Checks for check out matching.
		if ($member->checked_out && $user->id != $member->checked_out) {
		    Flash::error(Lang::get('codalia.membership::lang.action.check_out_do_not_match'));
		    return redirect('backend/codalia/membership/members');
		}

		// Locks the item for this user.
		MembershipHelper::instance()->checkOut((new MemberModel)->getTable(), $user, $params[0]);
	    }
	});

	Event::listen('cms.page.beforeDisplay', function ($controller, $url, $page) {
	    if (!$page || $url === '404' || $url === '403') {
		return $page;
	    }

	    $paymentProcedures = ['paypal/subscription/notify', 'paypal/insurance/notify'];

	    // The application is called by an external payment plateform.
	    if (in_array($url, $paymentProcedures)) {
	        // No layouts must be displayed as it prevents to reply with an empty 200 response. (ie: header("HTTP/1.1 200 OK");)
		$page->layout = null;
	    }

	    return $page;
	});

	// Ensures that the Codalia Profile plugin is installed and activated.
	if (!PluginManager::instance()->exists('Codalia.Profile')) {
	    return;
	}

        ProfileModel::extend(function($model) {
	    // Sets the relationship.
	    $model->hasOne['member'] = ['Codalia\Membership\Models\Member'];
	});

	// Events fired by the Profile plugin.
	
	// A user has been registered as member.
	Event::listen('codalia.profile.registerMember', function($profile, $data) {
	    // Ensures that a profile model always exists.
	    $member = MemberModel::getFromProfile($profile, $data);

	    if (Input::hasFile('membership__attestation') && !$profile->honorary_member) {
		$member->attestation = Input::file('membership__attestation');
		$member->save();
	    }

	    EmailHelper::instance()->afterRegistration($member);
	});

	// After sending the update member form, the user is updated first then the corresponding
	// member is updated afterward.
	Event::listen('codalia.profile.updateMember', function($profileId, $data) {
	   Flash::success(Lang::get('codalia.membership::lang.action.update_success'));
	});

	// A user has been deleted.
	Event::listen('codalia.profile.userDeletion', function($profileId) {
	    // Checks for the corresponding member (if any).
	    $member = MemberModel::where('profile_id', $profileId)->first();

	    if ($member !== null) {
		$member->delete();
	    }
	});
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        //return []; // Remove this line to activate

        return [
            'Codalia\Membership\Components\Member' => 'member',
            'Codalia\Membership\Components\Paypal' => 'paypal',
            'Codalia\Membership\Components\Sherlocks' => 'sherlocks',
            'Codalia\Membership\Components\MemberList' => 'memberList',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
	return [
            'codalia.membership.manage_settings' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.manage_settings',
		'order' => 200
	      ],
            'codalia.membership.access_members' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.access_members',
		'order' => 201
            ],
            'codalia.membership.access_categories' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.access_categories',
		'order' => 202
            ],
            'codalia.membership.access_publish' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.access_publish'
            ],
            'codalia.membership.access_delete' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.access_delete'
            ],
            'codalia.membership.access_check_in' => [
                'tab' => 'codalia.membership::lang.membership.tab',
                'label' => 'codalia.membership::lang.membership.access_check_in'
            ],
		];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        $navigation = [
            'membership' => [
                'label'       => 'codalia.membership::lang.membership.menu_label',
                'url'         => Backend::url('codalia/membership/members'),
                'icon'        => 'icon-address-card-o',
                'permissions' => ['codalia.membership.*'],
                'order'       => 500,
		'sideMenu' => [
		    'members' => [
			'label'       => 'codalia.membership::lang.membership.members',
			'icon'        => 'icon-users',
			'url'         => Backend::url('codalia/membership/members'),
		    ],
		    'documents' => [
			'label'       => 'codalia.membership::lang.membership.documents',
			'icon'        => 'icon-files-o',
			'url'         => Backend::url('codalia/membership/documents'),
		    ],
		]
            ],
        ];

	$user = BackendAuth::getUser();

	// Limited access for decision maker.
	if ($user->role->code == 'decision-maker') {
	    unset($navigation['membership']['sideMenu']['documents']);
	}

	return $navigation;
    }

    public function registerSettings()
    {
	return [
	    'membership' => [
		'label'       => 'codalia.membership::lang.membership.menu_label',
		'description' => 'codalia.membership::lang.plugin.description',
		'category'    => 'codalia.membership::lang.membership.menu_label',
		'icon'        => 'icon-address-card-o',
		'class' => 'Codalia\Membership\Models\Settings',
		'order'       => 500,
		'keywords'    => 'geography place placement',
		'permissions' => ['codalia.membership.manage_settings']
	    ]
	];
    }

    public function registerMailTemplates()
    {
	return [
	    'codalia.membership::mail.alert_office',
	    'codalia.membership::mail.alert_decision_makers',
	    'codalia.membership::mail.alert_vote',
	    'codalia.membership::mail.alert_document',
	    'codalia.membership::mail.candidate_application',
	    'codalia.membership::mail.cancelled',
	    'codalia.membership::mail.cancellation',
	    'codalia.membership::mail.cancellation_admin',
	    'codalia.membership::mail.new_member',
	    'codalia.membership::mail.pending_renewal',
	    'codalia.membership::mail.pending_renewal_reminder',
	    'codalia.membership::mail.pending_renewal_last_reminder',
	    'codalia.membership::mail.pending_subscription',
	    'codalia.membership::mail.refused',
	    'codalia.membership::mail.renewal_subscription',
	    'codalia.membership::mail.revoked',
	    'codalia.membership::mail.cheque_payment',
	    'codalia.membership::mail.cheque_payment_subscription',
	    'codalia.membership::mail.cheque_payment_insurance',
	    'codalia.membership::mail.cheque_payment_subscription_insurance',
	    'codalia.membership::mail.alert_cheque_payment',
	    'codalia.membership::mail.alert_cheque_payment_subscription',
	    'codalia.membership::mail.alert_cheque_payment_insurance',
	    'codalia.membership::mail.alert_cheque_payment_subscription_insurance',
	    'codalia.membership::mail.payment_completed',
	    'codalia.membership::mail.payment_completed_admin',
	    'codalia.membership::mail.free_period_validated',
	    'codalia.membership::mail.free_period_validated_admin',
	    'codalia.membership::mail.payment_error',
	    'codalia.membership::mail.payment_error_admin',
	    'codalia.membership::mail.payment_cancelled',
	    'codalia.membership::mail.payment_cancelled_admin',
	];
    }

    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
	    //RenewalHelper::instance()->checkRenewal();
	    //RenewalHelper::instance()->_testScheduler();
        })->daily();
    }
}
