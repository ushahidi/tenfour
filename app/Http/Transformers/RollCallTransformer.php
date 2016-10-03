<?php
namespace RollCall\Http\Transformers;

use League\Fractal\TransformerAbstract;

class RollCallTransformer extends TransformerAbstract
{
    public function transform(array $rollCall)
    {
        // Format contacts if they are present
        if (isset($rollCall['contacts'])) {
            foreach ($rollCall['contacts'] as &$contact)
            {
                $contact['id'] = (int) $contact['id'];
                $contact['uri'] = '/contacts/' . $contact['id'];
            }
        }

        $rollCall['organization'] = [
            'id'  => (int) $rollCall['organization_id'],
            'uri' => '/organizations/' . $rollCall['organization_id'],
        ];

        unset($rollCall['organization_id']);

        $rollCall['id'] = (int) $rollCall['id'];
        $rollCall['uri'] = '/rollcalls/' . $rollCall['id'];

        return $rollCall;
    }
}
