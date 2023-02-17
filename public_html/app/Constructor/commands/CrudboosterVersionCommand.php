<?php namespace App\Constructor\commands;

use App;
use Illuminate\Console\Command;

class CrudboosterVersionCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'crudbooster:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BTBooster Version Command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public static $version = "5.5.7";

    public function handle()
    {
        $this->info(static::$version);
    }
}
