<?php
use Illuminate\Database\Migrations\Migration;

class CreateAuthorsTable extends Migration {
   public function up()
   {
       Schema::create('authors', function($table)
       {
          $table->increments('id');

          $table->string('author_id');

          $table->string('fname')->default('');

          $table->string('lname')->default('');


          // to add created_at and updated_at
          $table->timestamps();        });
   }

   public function down()
   {
      Schema::drop('authors');
   }
}
?>