<?php

namespace Phantom\Database\Seeders;

abstract class Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    abstract public function run();

    /**
     * Call the given seeder class.
     *
     * @param  string  $class
     * @return void
     */
    public function call($class)
    {
        $seeder = new $class;
        $seeder->run();
    }
}
