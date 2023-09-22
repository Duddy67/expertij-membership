<?php namespace Codalia\Membership\Components;

use Cms\Classes\ComponentBase;
use Codalia\Membership\Models\Member as MemberModel;
use Codalia\Membership\Helpers\RenewalHelper;
use Codalia\Profile\Models\Profile;
use Codalia\Profile\Models\Licence;
use System\Models\File;
use Auth;
use Flash;
use Lang;


class MemberList extends ComponentBase
{
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
	return [
            'membersPerPage' => [
                'title'             => 'Members per page',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'codalia.membership::lang.settings.members_per_page_validation',
                'showExternalParam' => false
            ],
            'memberType' => [
                'title'       => 'Member type',
                'type'        => 'dropdown',
                'default'     => 'regular',
                'placeholder' => 'Select type',
                'options'     => ['honorary' => 'Honorary', 'regular' => 'Regular']
            ],
	];
    }

    public function onRun()
    {
        $this->prepareVars();
	$this->listMembers();
    }

    /**
     * Executed when this component is initialized
     */
    public function prepareVars()
    {
	$this->page['thumbSize'] = Profile::getThumbnailSize();
	$this->page['blankProfile'] = Profile::getBlankProfileUrl();
	$this->page['languages'] = $this->getLanguages();
	$this->page['licenceTypes'] = $this->getLicenceTypes();
	$this->page['appealCourts'] = Profile::getAppealCourts();
	$this->page['courts'] = Profile::getCourts();
	$this->page['texts'] = $this->getTexts();
	$this->page['memberType'] = $this->property('memberType');
    }

    protected function loadMember()
    {
        // Gets the current user.
        $user = Auth::getUser();
	// Loads the corresponding member through the profile_id attribute.
	$profileId = Profile::where('user_id', $user->id)->pluck('id');
	$member = new MemberModel;
	$member = $member->where('profile_id', $profileId);

	if (($member = $member->first()) === null) {
	    return null;
	}

	return $member;
    }

    private function getTexts()
    {
        $langVars = require 'plugins/codalia/membership/lang/en/lang.php';
	$texts = [];
	$sections = ['professional_status', 'profile', 'action', 'filter', 'membership'];

	foreach ($langVars as $level1 => $section1) {
	    if (in_array($level1, $sections)) {
		foreach ($section1 as $level2 => $section2) {
		    $texts[$level1.'.'.$level2] = Lang::get('codalia.membership::lang.'.$level1.'.'.$level2);
		}
	    }
	}

	return $texts;
    }

    protected function listMembers($pageNumber = null)
    {
        $pageNumber = ($pageNumber !== null) ? $pageNumber : 0;
	$membersPerPage = $this->property('membersPerPage');
	$memberType = ($this->property('memberType') == 'regular') ? 0 : 1;

	// Loads members from the Profile relationship as it contained most of the relevant data to search for.
	$this->page['members'] = $this->profiles = Profile::whereHas('member', function($query) {  
		        $query->where('member_list', 1)->where(function ($query) {
			    $query->where('status', 'member');

			    $now = new \DateTime(date('Y-m-d'));
			    $renewalDate = RenewalHelper::instance()->getRenewalDate();

			    if ($now->format('Y-m-d') < $renewalDate->format('Y-m-d')) {
				$query->orWhere('status', 'pending_renewal');
			    }
			});
		     })->where('honorary_member', $memberType)  
                       ->orderBy('last_name')
                       ->paginate($membersPerPage, $pageNumber);
    }

    public function onFilterMembers()
    {
        $data = post();
	$this->prepareVars();
	// Sets the page number according to the passed variables.
	$pageNumber = (isset($data['reset_filters']) || !isset($data['page_number'])) ? 0 : $data['page_number'];

	// No filters or filters have been reset.
	if (isset($data['reset_filters']) || (!isset($data['languages']) && empty($data['licence_type']))) {
	    $this->listMembers($pageNumber);
	}
	// Apply filters.
	else {
	    $membersPerPage = $this->property('membersPerPage');
	    // Several languages are selected.
	    $multiLanguages = (isset($data['languages']) && count($data['languages']) > 1) ? true : false;

	    if ($multiLanguages) {
		// Searches members from the Profile relationship as it contained most of the relevant data to search for.
		$query = Profile::where(function($query) use($data) {
		    foreach ($data['languages'] as $language) {
			// - When no licence type is selected, the query ensures that all the selected languages are contained in
			//   the licences (even separately).
			// - When a licence type is selected, the query ensures that all the selected languages are contained in 
			//   the licences of the same type (even separately).
			// - Now when a licence type and a court (or appeal court) are selected, the query ensures that the
			//   selected languages are all contained in (at least) one licence, as court (or appeal court) are unique 
			//   for each licence.
			$query->whereHas('licences', function($query) use($data, $language) {
			    if (!empty($data['licence_type'])) {
				$query->where('type', $data['licence_type']);

				if (isset($data['appeal_court_ids']) || isset($data['court_ids'])) {
				    $attributeName = ($data['licence_type'] == 'expert') ? 'appeal_court_id' : 'court_id';
				    $ids = (isset($data['appeal_court_ids'])) ? $data['appeal_court_ids'] : $data['court_ids'];
				    $query->whereIn($attributeName, $ids);
				}
			    }

			    $query->whereHas('languages', function($query) use($data, $language) {
				$query->where('alpha_3', $language);

				if (!empty($data['expert_skill'])) {
				    // Uses a raw query or the bindings will come in the wrong order.
				    $query->whereRaw($data['expert_skill'].' = 1');
				}
			    });
			});
		    }
		});
	    }
	    else {
		// Searches members from the Profile relationship as it contained most of the relevant data to search for.
		$query = Profile::whereHas('licences', function($query) use($data) {

		    if (!empty($data['licence_type'])) {
			$query->where('type', $data['licence_type']);

			if (isset($data['appeal_court_ids']) || isset($data['court_ids'])) {
			    $attributeName = ($data['licence_type'] == 'expert') ? 'appeal_court_id' : 'court_id';
			    $ids = (isset($data['appeal_court_ids'])) ? $data['appeal_court_ids'] : $data['court_ids'];
			    $query->whereIn($attributeName, $ids);
			}
		    }

		    if (isset($data['languages'])) {
			$query->whereHas('languages', function($query) use($data) {
			    $query->whereIn('alpha_3', $data['languages']);

                            if (!empty($data['expert_skill'])) {
                                // Uses a raw query or the bindings will come in the wrong order.
                                $query->whereRaw($data['expert_skill'].' = 1');
                            }
			});
		    }
		});
	    }

	    // Filters the search against some member attributes.
	    $query->whereHas('member', function($query) {
		$query->where('member_list', 1)->where(function ($query) {
		    $query->where('status', 'member');

		    $now = new \DateTime(date('Y-m-d'));
		    $renewalDate = RenewalHelper::instance()->getRenewalDate();

		    if ($now->format('Y-m-d') < $renewalDate->format('Y-m-d')) {
			$query->orWhere('status', 'pending_renewal');
		    }
		});
	    });

            // Rule out the honorary members from the results.
            $query->where('honorary_member', 0);

	    $this->page['members'] = $this->profiles = $query->paginate($membersPerPage, $pageNumber);
	}

	return [
	    '#members' => $this->renderPartial('@members'), 
	    '#pagination' => $this->renderPartial('@pagination'), 
	];
    }

    public function onExport()
    {
	$this->onFilterMembers();

	$columns = [
	    Lang::get('codalia.profile::lang.profile.civility'), 
	    Lang::get('codalia.profile::lang.profile.first_name'), 
	    Lang::get('codalia.profile::lang.profile.last_name'), 
	    Lang::get('codalia.profile::lang.profile.birth_name'), 
	    Lang::get('codalia.profile::lang.profile.birth_date'), 
	    Lang::get('codalia.profile::lang.profile.birth_location'), 
	    Lang::get('codalia.profile::lang.profile.citizenship'), 
	    Lang::get('codalia.profile::lang.profile.street'), 
	    Lang::get('codalia.profile::lang.profile.city'), 
	    Lang::get('codalia.profile::lang.profile.postcode'), 
	    Lang::get('codalia.profile::lang.profile.phone'), 
	    Lang::get('codalia.profile::lang.profile.email'), 
	    Lang::get('codalia.profile::lang.licence.expert'), 
	    Lang::get('codalia.membership::lang.member.languages_expert'), 
	    Lang::get('codalia.profile::lang.licence.ceseda'), 
	    Lang::get('codalia.membership::lang.member.languages_ceseda'), 
	];

	$list = [$columns];

	foreach ($this->profiles as $profile) {
	    $data = [
	        Lang::get('codalia.profile::lang.profile.'.$profile->civility),
	        $profile->first_name,
	        $profile->last_name,
	        $profile->birth_name,
	        date('d/m/Y', strtotime($profile->birth_date)),
	        $profile->birth_location,
	        Lang::get('codalia.profile::lang.citizenship.'.$profile->citizenship),
	        $profile->street,
	        $profile->city,
	        $profile->postcode,
	        $profile->phone,
	        $profile->user->email,
		'', '', '', ''
	    ];

	    foreach ($profile->licences as $licence) {
	        if ($licence->type == 'expert') {
		    $data[12] = $this->page['appealCourts'][$licence->appeal_court_id];
		}
		else {
		    $data[14] .= $this->page['courts'][$licence->court_id].',';
		}

		foreach ($licence->attestations as $attestation) {
		    foreach ($attestation->languages as $language) {
			if ($licence->type == 'expert') {
			    $data[13] .= $this->page['languages'][$language->alpha_3].',';
			}
			else {
			    $data[15] .= $this->page['languages'][$language->alpha_3].',';
			}
		    }
		}
	    }

	    // Removes possible comma from the end of the strings from $data[13] to $data[15].
	    for ($i = 13; $i < 16; $i++) {
		$data[$i] = (substr($data[$i], -1) == ',') ? substr($data[$i], 0, -1) : $data[$i];
	    }

	    $list[] = $data;
	}

	// Creates a temporary csv file.
	$fileName = 'export-members-'.date('Y-m-d-H-i-s').'.csv';
	$fp = fopen('storage/temp/public/'.$fileName, 'w');

	foreach ($list as $fields) {
	    fputcsv($fp, $fields, ';');
	}

	fclose($fp);

	// Attaches the newly created file.
	$file = (new File)->fromFile('storage/temp/public/'.$fileName);
	$file->save();

	$member = $this->loadMember();
	$member->export = $file;
	$member->save();

	// Deletes the temporary file from the directory.
	unlink('storage/temp/public/'.$fileName);

	return \Redirect::to('export.php')->with('file', $member->export);
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
