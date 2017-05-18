<?php
namespace RollCall\Contracts\Repositories;

interface OrganizationRepository extends CrudRepository
{

    /**
     * Get list of organizations, optionally filtered by user or subdomain
     *
     * @param string $subdomain
     *
     * @return array
     */
    public function all($subdomain = false);

    /**
     * Get an organization by subdomain
     * @param  $subdomain
     * @return array
     */
    public function findBySubdomain($subdomain);

    /**
     * Get an organization's setting by key
     * @param  $subdomain
     * @return array
     */
    public function getSetting($id, $key);
}
