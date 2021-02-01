<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Models\Settings;
use Codalia\Profile\Models\Profile;
use Codalia\Profile\Models\Licence;
use Flash;
use Lang;


class MemberList extends ComponentBase
{
    public $members = null;
    public $profiles = null;


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

	//$this->members = $this->listMembers();
	$this->profiles = $this->listMembers();
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
	$thumbSize = explode(':', Settings::get('photo_thumbnail', '100:100'));
	$this->page['thumbSize'] = ['width' => $thumbSize[0], 'height' => $thumbSize[1]];
	$this->page['languages'] = $this->getLanguages();
	$this->page['licenceTypes'] = $this->getLicenceTypes();
	$this->page['appealCourts'] = Profile::getAppealCourts();
	$this->page['courts'] = Profile::getCourts();

	$profiles = Profile::whereHas('licences', function($query) {
				 $query->where('type', 'ceseda')->whereHas('attestations', function($query) {
				     $query->whereHas('languages', function($query) { 
					 $query->where('alpha_2', 'ba');
				     });
				  });
			      })->whereHas('member', function($query) {
				      $query->where('member_list', 1);
			      })->get();

	foreach ($profiles as $profile) {
	  echo $profile->last_name;
	}
    }

    protected function listMembers()
    {
        //$members = MemberModel::where('member_list', 1)->get();
        // Searches from the Profile relationship as it contained most of the relevant data.
	$profiles = Profile::whereHas('member', function($query) {
		        $query->where('member_list', 1);
		    })->get();

	return $profiles;
    }

    public function onFilterMembers()
    {
        $data = post();
	$thumbSize = explode(':', Settings::get('photo_thumbnail', '100:100'));
	$this->page['thumbSize'] = ['width' => $thumbSize[0], 'height' => $thumbSize[1]];

	\DB::enableQueryLog(); // Enable query log
	$this->profiles = Profile::whereHas('licences', function($query) use($data) {

			      if (!empty($data['licence_type'])) {
				 $query->where('type', $data['licence_type']);
				 if (!empty($data['appeal_court_id']) || !empty($data['court_id'])) {
				     $attributeName = ($data['licence_type'] == 'expert') ? 'appeal_court_id' : 'court_id';
				     //$attributeValue = (!empty($data['appeal_court_id'])) ? $data['appeal_court_id'] : 'court_id';
//file_put_contents('debog_file.txt', print_r($attributeName.' '.$attributeValue, true));
				    //$query->where('type', 'expert');
				    $query->where($attributeName, $data[$attributeName]);
				 }
			      }

			      $query->whereHas('attestations', function($query) use($data) {
				 //
				  $query->whereHas('languages', function($query) use($data) { 
				      if (!empty($data['languages'])) {
					  $query->where('alpha_2', $data['languages']);
				      }
				  });
			      });
			  })->whereHas('member', function($query) {
			      $query->where('member_list', 1);
			  })->get();
	//var_dump(\DB::getQueryLog()); // Show results of log
	/*$this->profiles = Profile::whereHas('licences', function($query) use($data) {
					   $query->where('type', 'expert')->where('appeal_court_id', 14);
	})->get();*/

        return ['#members' => $this->renderPartial('@members')];
    }

    public function getLanguages()
    {
        $codes = Profile::getLanguages();
	$languages = [];

	foreach ($codes as $code) {
	    $languages[$code] = Lang::get('codalia.profile::lang.language.'.$code);
	}

	return $languages;
    }

    public function getLicenceTypes()
    {
	$types = Licence::getTypes();
	$licenceTypes = [];

	foreach ($types as $type) {
	    $licenceTypes[$type] = Lang::get('codalia.profile::lang.licence.'.$type);
	}

	return $licenceTypes;
    }
}
