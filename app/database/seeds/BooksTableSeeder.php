<?php

class BooksTableSeeder extends Seeder {
    public function run()
    {
        DB::table('books')->delete();
        book::create(
           array('book_id' => 'b1', 'title' => 'test title 1' , 'description' => 'description 1' , 'published_at' => 1387865982 , 'isbn' => '9781903436592'),

           array('book_id' => 'b2', 'title' => 'test title 2' , 'description' => 'description 2' , 'published_at' => 1387866004 , 'isbn' => '9781604501483'),

           array('book_id' => 'b3', 'title' => 'test title 3' , 'description' => 'description 3' , 'published_at' => 1387866015 , 'isbn' => '9781905921058'),

        );
    }
}