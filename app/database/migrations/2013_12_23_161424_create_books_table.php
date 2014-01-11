<?php
// creating schema


use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration {
   public function up()
   {
       Schema::create('books', function($table)
       {
          $table->increments('id');

          $table->string('book_id');

          $table->index('book_id');

          $table->string('title');

          $table->string('isbn')->default('');

          $table->timestamp('published_at');


          // to add created_at and updated_at
          $table->timestamps(); 
          $table->text('description')->nullable();

       });
   }

   public function down()
   {
      Schema::drop('books');
   }
}
?>