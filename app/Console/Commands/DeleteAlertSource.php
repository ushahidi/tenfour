<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Models\AlertSource;
use TenFour\Contracts\Repositories\AlertSourceRepository;

class DeleteAlertSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:delete {feed_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an alert source system/webhook';

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
        $feed_id = $this->argument('feed_id');
        if (!$feed_id) {
            $this->error("Could not delete the source subscription, missing feed_id");
            return;
        }
        $sourceObj = $source->delete($feed_id);
        if ($sourceObj) {
            $this->info("Successfully deleted a source with id $feed_id");
        } else {
            $this->error("Could not delete the source subscription");
            $this->error(var_export($sourceObj, true));
               
        }
        
    }
}
