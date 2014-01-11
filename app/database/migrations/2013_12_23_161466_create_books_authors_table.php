<?php

use Illuminate\Database\Migrations\Migration;

class CreateBooksAuthorsTable extends Migration {
   public function up()
   {
       Schema::create('books_authors', function($table)
       {
          $table->increments('id');

          $table->string('author_id');

          $table->string('book_id');

       });
   }

   public function down()
   {
      Schema::drop('books_authors');
   }
}
?>