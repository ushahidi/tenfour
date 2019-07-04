<?php
namespace TenFour\Http\Transformers;

use League\Fractal\TransformerAbstract;

class FileUploadTransformer extends TransformerAbstract
{
    public function transform(array $file)
    {
        $file['id'] =  (int) $file['id'];
        $file['uri'] = 'organizations/' . $file['organization_id'] . '/files/' . $file['id'];

        unset($file['organization_id']);
        unset($file['filename']);

        return $file;
    }

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'organization'
    ];

    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        'organization'
    ];

    /**
     * Include Organization
     *
     * @return League\Fractal\ItemResource
     */
    public function includeOrganization(array $file)
    {
        $organization = [];
        $organization['id'] = $file['organization_id'];

        return $this->item($organization, new OrganizationTransformer);
    }

}
