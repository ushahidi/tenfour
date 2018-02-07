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

        // Delete roles;
        // $dbh->exec('TRUNCATE TABLE roles');


        $dbh->exec('TRUNCATE TABLE credit_adjustments');
        $dbh->exec('TRUNCATE TABLE subscriptions');
        $dbh->exec('TRUNCATE TABLE addons');

        // Delete test organizations
        $dbh->exec('TRUNCATE TABLE organizations');

        // Delete test contacts
        $dbh->exec('TRUNCATE TABLE contacts');

        // Delete test check-ins
        $dbh->exec('TRUNCATE TABLE check_ins');

        // Delete check_in_messages, check_in_recipients pivot data
        $dbh->exec('TRUNCATE TABLE check_in_messages');
        $dbh->exec('TRUNCATE TABLE check_in_recipients');

        // Delete check-in replies
        $dbh->exec('TRUNCATE TABLE replies');

        // Delete settings
        $dbh->exec('TRUNCATE TABLE settings');

        // Delete contact_files
        $dbh->exec('TRUNCATE TABLE contact_files');

        // Delete unverified addresses
        $dbh->exec('TRUNCATE TABLE unverified_addresses');

        //Delete groups
        $dbh->exec('TRUNCATE TABLE groups');

        //Delete group_users
        $dbh->exec('TRUNCATE TABLE group_users');

        $dbh->exec('SET FOREIGN_KEY_CHECKS=1;');

    }
}
