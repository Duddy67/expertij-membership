<?php namespace Codalia\Membership\Updates;

use Seeder;
use Codalia\Membership\Models\AppealCourt;
use Codalia\Membership\Models\Category;


class SeedAppealCourtsTable extends Seeder
{
    public $appealCourts = ['Agen', 'Aix en Provence', 'Amiens', 'Angers', 'Basse Terre',
			    'Bastia', 'Besançon', 'Bordeaux', 'Bourges', 'Caen', 'Cayenne',
			    'Chambéry', 'Colmar', 'Dijon', 'Douai', 'Fort de France', 'Grenoble',
			    'Limoges', 'Lyon', 'Metz', 'Montpellier', 'Nancy', 'Nîmes', 'Nouméa',
			    'Orléans', 'Papeete', 'Paris', 'Pau', 'Poitiers', 'Reims', 'Rennes',
			    'Riom', 'Rouen', 'Saint Denis', 'Saint Pierre', 'Toulouse', 'Versailles'
    ]; 

    public $categories = [['name' => 'Expert', 'slug' => 'expert'],
                          ['name' => 'CESEDA', 'slug' => 'ceseda'],
                          ['name' => 'Membre associé', 'slug' => 'membre-associe']
    ];

    public function run()
    {
        foreach ($this->appealCourts as $appealCourt) {
	    AppealCourt::create(['name' => $appealCourt]);
	}

        foreach ($this->categories as $category) {
	    Category::create(['name' => $category['name'], 'slug' => $category['slug']]);
	}
    }
}

