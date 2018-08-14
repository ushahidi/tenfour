<?php
namespace TenFour\Contracts\Repositories;

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
     * Get a list of organizations by a member's email
     * @param  $email
     * @return array
     */
    public function findByEmail($email);

    /**
     * Get an organization's setting by key
     * @param  $organization_id
     * @param  $setting_key
     * @return array
     */
    public function getSetting($id, $key);

    /**
     * Set an organization's setting by key
     * @param  $organization_id
     * @param  $setting_key
     * @param  $setting_value
     * @return array
     */
    public function setSetting($id, $key, $value);
}
