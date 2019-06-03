<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Models\AlertSource;
use TenFour\Contracts\Repositories\AlertSourceRepository;

class CreateAlertSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:subscribe {feed_id} {name} {protocol} {url} {authentication_options} {enabled} {organization_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to an alerts system/webhook';

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
     * @return mixed
     */
    public function handle(AlertSourceRepository $source)
    {
        $input = $this->arguments();
        $sourceObj  = $source->create($input);
        if ($sourceObj) {
            $this->info("Successfully added a new source with id $sourceObj->feed_id");
        } else {
            $this->error("Could not create the source subscription");
            $this->error(var_export($sourceObj, true));
               
        }
        
    }
}
