<?php
namespace TenFour\Messaging;

use TenFour\Contracts\Repositories\PersonRepository;
use TenFour\Jobs\SendInvite;
use Illuminate\Foundation\Bus\DispatchesJobs;

class InviteDispatcher
{
    use DispatchesJobs;

    public function __construct(PersonRepository $member)
    {
        $this->member = $member;
    }

    /**
    * Queue invite for member
    *
    * @param User $member
    * @param Array $recipient
    *
    * @return void
    */
   public function queueInvite($member)
   {
     // This Laravel's method for token generation, it could maybe be 60

   }
}
