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

        // Delete test role_user pivot data
        $dbh->exec('TRUNCATE TABLE role_user');

        // Delete roles;
        $dbh->exec('TRUNCATE TABLE roles');

        // Delete test organizations
        $dbh->exec('TRUNCATE TABLE organizations');

        // Delete test organization groups
        $dbh->exec('TRUNCATE TABLE organization_groups');

        // Delete test organization_users pivot data
        $dbh->exec('TRUNCATE TABLE organization_user');

        // Delete test contacts
        $dbh->exec('TRUNCATE TABLE contacts');

        // Delete test roll calls
        $dbh->exec('TRUNCATE TABLE roll_calls');

        // Delete contact_roll_call pivot data
        $dbh->exec('TRUNCATE TABLE contact_roll_call');

        // Delete roll call replies
        $dbh->exec('TRUNCATE TABLE replies');

        $dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }
}
