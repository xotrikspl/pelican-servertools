<?php

namespace Xotriks\Servertools\Commands;

use Illuminate\Console\Command;
use Xotriks\Servertools\Database\Seeders\ServerToolConfigurationSeeder;

class SeedServerToolConfigurationsCommand extends Command
{
    protected $signature = 'servertools:seeder';
    protected $description = 'Seed server tool configurations from available profiles';

    public function handle()
    {
        $this->info('🌱 Seeding server tool configurations...');

        $seeder = new ServerToolConfigurationSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }
}
