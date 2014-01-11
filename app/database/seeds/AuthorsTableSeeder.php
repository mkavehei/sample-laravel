<?php

class AuthorsTableSeeder extends Seeder {
    public function run()
    {
        DB::table('authors')->delete();
        author::create(

           array('author_id'=> 'a1', 'fname' => 'author fname1' , 'lname' => 'author lname1'),

           array('author_id'=> 'a2', 'fname' => 'author fname2' , 'lname' => 'author lname2'),

           array('author_id'=> 'a3', 'fname' => 'author fname3' , 'lname' => 'author lname3'),

           array('author_id'=> 'a4', 'fname' => 'author fname4' , 'lname' => 'author lname4'), 

        );

    }
}