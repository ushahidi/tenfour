<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class ReplyTransformer extends TransformerAbstract
{
    public function transform(array $reply)
    {
        $reply['uri'] = '/rollcalls/' . $reply['roll_call_id'] . '/replies/' . $reply['id'];

        $reply['rollcall']['id'] = (int) $reply['roll_call_id'];
        $reply['rollcall']['uri'] = '/rollcalls/' . $reply['roll_call_id'];
        unset($reply['roll_call_id']);

        $reply['contact']['id'] = (int) $reply['contact_id'];
        $reply['contact']['uri'] = '/contacts/' . $reply['contact_id'];
        unset($reply['contact_id']);

        return $reply;
    }
}
