<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCatDocumentsTable extends Migration
{
    public function up()
    {
	Schema::create('codalia_membership_cat_documents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('document_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['document_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_cat_documents');
    }
}
