<?php

class DatabaseSeeder extends Seeder {
    public function run()
    {
        $this->call('BooksTableSeeder');
        $this->command->info('Books table seeded!');


        $this->call('AuthorsTableSeeder');
        $this->command->info('Authors table seeded!');


        $this->call('BooksAuthorsTableSeeder');
        $this->command->info('Books - Authors table seeded!'); 

    }
}

?>