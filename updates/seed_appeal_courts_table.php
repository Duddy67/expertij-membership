<?php namespace Codalia\Membership\Updates;

use Seeder;
use Codalia\Membership\Models\AppealCourt;


class SeedAppealCourtsTable extends Seeder
{
    public $appealCourts = ['Agen', 'Aix en Provence', 'Amiens', 'Angers', 'Basse Terre',
			    'Bastia', 'Besançon', 'Bordeaux', 'Bourges', 'Caen', 'Cayenne',
			    'Chambéry', 'Colmar', 'Dijon', 'Douai', 'Fort de France', 'Grenoble',
			    'Limoges', 'Lyon', 'Metz', 'Montpellier', 'Nancy', 'Nîmes', 'Nouméa',
			    'Orléans', 'Papeete', 'Paris', 'Pau', 'Poitiers', 'Reims', 'Rennes',
			    'Riom', 'Rouen', 'Saint Denis', 'Saint Pierre', 'Toulouse', 'Versailles'
    ]; 


    public function run()
    {
        foreach ($this->appealCourts as $appealCourt) {
	    AppealCourt::create(['name' => $appealCourt]);
	}
    }
}

