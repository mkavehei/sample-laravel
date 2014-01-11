<?php

class BooksAuthorsTableSeeder extends Seeder {
    public function run()
    {  
        DB::table('books_authors')->delete();

        book_author::create(
           array('book_id'  => 'b1' , 'author_id'=> 'a1' ),

           array('book_id'  => 'b1' , 'author_id'=> 'a2' ),


           array('book_id'  => 'b2' , 'author_id'=> 'a1' ),


           array('book_id'  => 'b3' , 'author_id'=> 'a2' ),

           array('book_id'  => 'b3' , 'author_id'=> 'a3' ),

        );
    } 
}