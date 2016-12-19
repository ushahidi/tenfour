<?php
namespace RollCall\Messaging;

use RollCall\Contracts\Repositories\UserRepository;
use RollCall\Jobs\SendInvite;
use Illuminate\Foundation\Bus\DispatchesJobs;

class InviteDispatcher
{
    use DispatchesJobs;

    public function __construct(UserRepository $member)
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
