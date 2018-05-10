<?php

namespace TenFour\Jobs;

use TenFour\Contracts\Repositories\OrganizationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App;

class ExpireCredits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Execute the job.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->creditService = App::make('TenFour\Services\CreditService');
        $this->creditService->expireCreditsOnUnpaid();
    }
}
