<?php
namespace TenFour\Contracts\Repositories;

interface AlertFeedEntryRepository extends CrudRepository
{

    /**
     * Get all
     * @param  [int] $org_id
     * @param  [int] $owner_id
     * @param  [int] $source_type
     * @param  [int] $offset
     * @param  [int] $limit
     * @return [Array]
     */
    public function all($org_id = null, $owner_id = null, $source_type = null, $enabled = null, $offset = 0, $limit = 0);

    /**
     * mysql> desc alert_feed;
     * +-----------------+---------------------+------+-----+---------+----------------+
     * | Field           | Type                | Null | Key | Default | Extra          |
     * +-----------------+---------------------+------+-----+---------+----------------+
     * | id              | bigint(20) unsigned | NO   | PRI | NULL    | auto_increment |
     * | owner_id        | int(10) unsigned    | NO   | MUL | 0       |                |
     * | organization_id | int(10) unsigned    | NO   | MUL | 0       |                |
     * | country         | varchar(255)        | NO   |     | NULL    |                |
     * | city            | varchar(255)        | NO   |     | NULL    |                |
     * | source_id       | varchar(255)        | NO   | MUL | NULL    |                |
     * | enabled         | text                | NO   |     | NULL    |                |
     * | created_at      | timestamp           | YES  |     | NULL    |                |
     * | updated_at      | timestamp           | YES  |     | NULL    |                |
     * +-----------------+---------------------+------+-----+---------+----------------+
     *
     * @param array $input
     * @return void
     */ 
    public function create(array $input);
}
