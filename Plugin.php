<?php namespace Codalia\Membership;

use Backend;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use RainLab\User\Models\User as UserModel;
use Codalia\Profile\Models\Profile as ProfileModel;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Controllers\Members as MembersController;
use Codalia\Membership\Helpers\MembershipHelper;
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
		    Flash::error(Lang::get('codalia.profile::lang.action.check_out_do_not_match'));
		    return redirect('backend/codalia/membership/members');
		}

		// Locks the item for this user.
		MembershipHelper::instance()->checkOut((new MemberModel)->getTable(), $user, $params[0]);
	    }
	});

	// Ensures first that the RainLab User plugin is installed and activated.
	if (!PluginManager::instance()->exists('RainLab.User')) {
	    return;
	}

        UserModel::extend(function($model) {
	    // Sets the relationship.
	    $model->hasOne['member'] = ['Codalia\Membership\Models\Member'];

	    $model->bindEvent('model.afterDelete', function () use ($model) {
		// Deletes the member model linked to the deleted user.
		MemberModel::where('user_id', $model->id)->delete();
	    });
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
	
	Event::listen('codalia.profile.registerMember', function($user, $profile, $data) {
	    // Ensures that a member model always exists.
	    $member = MemberModel::getFromUser($user, $profile);

	    if (Input::hasFile('attestation')) {
		$member->attestations = Input::file('attestation');
		$member->save();
	    }

	    //$name = Input::file('siret')->getClientOriginalName();
	    //$file = Input::file('siret')->move('plugins/codalia/membership/documents', $name);
	    //file_put_contents('debog_file_data.txt', print_r($data, true));
	    //file_put_contents('debog_file_file.txt', print_r($file, true));
	});


	// Events fired by the User plugin.
	
	Event::listen('rainlab.user.beforeRegister', function(&$data) {
	    // 
	});

	Event::listen('rainlab.user.register', function($user, $data) {
	});

	Event::listen('rainlab.user.beforeAuthenticate', function($model, $credentials) {
	    //
	});

	Event::listen('rainlab.user.logout', function($user) {
	  //file_put_contents('debog_file.txt', print_r($_POST, true));
	    //
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
            'Codalia\Membership\Components\Account' => 'account',
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
                'label' => 'codalia.membership::lang.membership.access_articles',
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

        return [
            'codalia.membership.some_permission' => [
                'tab' => 'Membership',
                'label' => 'Some permission'
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
        //return []; // Remove this line to activate

        return [
            'membership' => [
                'label'       => 'Membership',
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
		    'categories' => [
			'label'       => 'codalia.membership::lang.membership.categories',
			'icon'        => 'icon-sitemap',
			'url'         => Backend::url('codalia/membership/categories'),
		    ],
		    'documents' => [
			'label'       => 'codalia.membership::lang.membership.documents',
			'icon'        => 'icon-files-o',
			'url'         => Backend::url('codalia/membership/documents'),
		    ],
		]
            ],
        ];
    }

    public function registerSettings()
    {
	return [
	    'membership' => [
		'label'       => 'Journal',
		'description' => 'A simple plugin to manage articles.',
		'category'    => 'Journal',
		'icon'        => 'icon-newspaper-o',
		'class' => 'Codalia\Membership\Models\Settings',
		'order'       => 500,
		'keywords'    => 'geography place placement',
		'permissions' => ['codalia.membership.manage_settings']
	    ]
	];
    }
}
