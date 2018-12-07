<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;
use TenFour\Contracts\Repositories\OrganizationRepository;
use TenFour\Models\Organization;
use TenFour\Models\CheckIn;
use App;

class AddTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add default templates to all orgs';

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
    public function handle(OrganizationRepository $organizations)
    {
        foreach ($organizations->all() as $org) {
            $org = Organization::findOrFail($org['id']);

            if (!CheckIn::where('organization_id', $org['id'])->where('template', true)->count()) {
                $this->info('Org ' . $org['subdomain'] . ' has no default templates - adding now');

                App::make('TenFour\Http\Controllers\Api\First\OrganizationController')->createZeroStateTemplates($org->id, $org->owner()->id);
            }
        }
    }
}
