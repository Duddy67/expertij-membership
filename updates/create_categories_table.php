<?php namespace Codalia\Membership\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('codalia_membership_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
	    $table->string('name')->nullable();
            $table->string('slug')->nullable()->index();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->timestamps();
        });

	Schema::create('codalia_membership_cat_members', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('member_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->primary(['member_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('codalia_membership_categories');
        Schema::dropIfExists('codalia_membership_cat_members');
    }
}
