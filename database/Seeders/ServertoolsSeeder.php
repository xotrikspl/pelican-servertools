<?php

use Illuminate\Database\Seeder;
use Xotriks\Servertools\Database\Seeders\ServerToolConfigurationSeeder;

class ServertoolsSeeder extends Seeder
{
    public function run(): void
    {
        $seeder = new ServerToolConfigurationSeeder();

        if (method_exists($seeder, 'setCommand')) {
            $seeder->setCommand($this->command);
        }

        $seeder->run();
    }
}
