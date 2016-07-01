<?php
namespace Codeception\Module;

class DbHelper extends \Codeception\Module\Db
{
    /**
     * Truncate tables instead of dropping them.
     *
     * See http://matthewturland.com/2014/05/09/customizing-codeception-database-cleanup/
     **/
    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();

        $dbh->exec('SET FOREIGN_KEY_CHECKS=0');

        // Clear OAuth tokens and scopes
        $dbh->exec('TRUNCATE TABLE oauth_access_token_scopes');
        $dbh->exec('TRUNCATE TABLE oauth_sessions');
        $dbh->exec('TRUNCATE TABLE oauth_session_scopes');
        $dbh->exec('TRUNCATE TABLE oauth_access_tokens');
        $dbh->exec('TRUNCATE TABLE oauth_scopes');
        $dbh->exec('TRUNCATE TABLE oauth_clients');

        // Delete test users
        $dbh->exec('TRUNCATE TABLE users');

        //Delete test roles_users
        $dbh->exec('TRUNCATE TABLE roles_users');

        //Delete roles;
        $dbh->exec('TRUNCATE TABLE roles');

        //Delete test organizations
        $dbh->exec('TRUNCATE TABLE organizations');

        //Delete test organization groups
        $dbh->exec('TRUNCATE TABLE organization_groups');

        //Delete test organization_admins
        $dbh->exec('TRUNCATE TABLE organization_admins');

        //Delete test contacts
        $dbh->exec('TRUNCATE TABLE contacts');

        $dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }
}
