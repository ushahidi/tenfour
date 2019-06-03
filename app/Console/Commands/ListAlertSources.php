<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Models\AlertSource;
use TenFour\Contracts\Repositories\AlertSourceRepository;

class ListAlertSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:list';

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
        $header = [
            'name', 'feed_id', 'protocol', 'url', 'authentication_options', 'enabled', 'created_at', 'updated_at'
        ];
        $this->table($header, $source->all());
    }
}
