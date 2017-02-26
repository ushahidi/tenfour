<?php
namespace RollCall\Http\Controllers;

use Log;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

trait ValidatesMessages
{
    protected function validateSnsMessageOrAbort($message)
    {
        $validator = new MessageValidator();
        
        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            Log::info('SNS Message Validation Error: ' . $e->getMessage());
            // Pretend we're not here if the message is invalid.
            abort(404);
        }
    }
    
}
