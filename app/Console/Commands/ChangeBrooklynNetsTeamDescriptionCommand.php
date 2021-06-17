<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class ChangeBrooklynNetsTeamDescriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BrooklynNetsTeamDescription:Change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change Brooklyn Nets team description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $str = '';
        for ($i=0; $i < 10; $i++) { 
            $str .= rand(0, 100) . '-';
        }

        $t = Team::find(1);
        $t->description = $str;
        $t->save();

        return 0;
    }
}
