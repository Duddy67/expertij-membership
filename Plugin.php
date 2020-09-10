<?php namespace Codalia\Membership;

use Backend;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use RainLab\User\Models\User as UserModel;
use Codalia\Profile\Models\Profile as ProfileModel;
use Codalia\Membership\Models\Member as MemberModel;
use Event;

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
	    MemberModel::getFromUser($user, $profile);
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
        return []; // Remove this line to activate

        return [
            'Codalia\Membership\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

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
                'icon'        => 'icon-leaf',
                'permissions' => ['codalia.membership.*'],
                'order'       => 500,
		'sideMenu' => [
		    'new_article' => [
			'label'       => 'codalia.journal::lang.articles.new_article',
			'icon'        => 'icon-plus',
			'url'         => '#'
		    ],
		]
            ],
        ];
    }
}
