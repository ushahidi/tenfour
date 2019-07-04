<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLegacyOauth extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('oauth_access_token_scopes');
        Schema::drop('oauth_auth_code_scopes');
        Schema::drop('oauth_auth_codes');
        Schema::drop('oauth_client_endpoints');
        Schema::drop('oauth_client_grants');
        Schema::drop('oauth_client_scopes');
        Schema::drop('oauth_grant_scopes');
        Schema::drop('oauth_grants');
        Schema::drop('oauth_refresh_tokens');
        Schema::drop('oauth_session_scopes');
        Schema::drop('oauth_scopes');
        Schema::drop('oauth_access_tokens');
        Schema::drop('oauth_sessions');
        Schema::drop('oauth_clients');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::statement('CREATE TABLE `oauth_clients` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `secret` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `oauth_clients_id_secret_unique` (`id`,`secret`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement("CREATE TABLE `oauth_sessions` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `owner_type` enum('client','user') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
          `owner_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `client_redirect_uri` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_sessions_client_id_owner_type_owner_id_index` (`client_id`,`owner_type`,`owner_id`),
          CONSTRAINT `oauth_sessions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

        DB::statement('CREATE TABLE `oauth_access_tokens` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `session_id` int(10) unsigned NOT NULL,
          `expire_time` int(11) NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `oauth_access_tokens_id_session_id_unique` (`id`,`session_id`),
          KEY `oauth_access_tokens_session_id_index` (`session_id`),
          CONSTRAINT `oauth_access_tokens_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `oauth_sessions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_scopes` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_session_scopes` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `session_id` int(10) unsigned NOT NULL,
          `scope_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_session_scopes_session_id_index` (`session_id`),
          KEY `oauth_session_scopes_scope_id_index` (`scope_id`),
          CONSTRAINT `oauth_session_scopes_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `oauth_scopes` (`id`) ON DELETE CASCADE,
          CONSTRAINT `oauth_session_scopes_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `oauth_sessions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_refresh_tokens` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `access_token_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `expire_time` int(11) NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`access_token_id`),
          UNIQUE KEY `oauth_refresh_tokens_id_unique` (`id`),
          CONSTRAINT `oauth_refresh_tokens_access_token_id_foreign` FOREIGN KEY (`access_token_id`) REFERENCES `oauth_access_tokens` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_grants` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_grant_scopes` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `grant_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `scope_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_grant_scopes_grant_id_index` (`grant_id`),
          KEY `oauth_grant_scopes_scope_id_index` (`scope_id`),
          CONSTRAINT `oauth_grant_scopes_grant_id_foreign` FOREIGN KEY (`grant_id`) REFERENCES `oauth_grants` (`id`) ON DELETE CASCADE,
          CONSTRAINT `oauth_grant_scopes_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `oauth_scopes` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_client_scopes` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `scope_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_client_scopes_client_id_index` (`client_id`),
          KEY `oauth_client_scopes_scope_id_index` (`scope_id`),
          CONSTRAINT `oauth_client_scopes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE,
          CONSTRAINT `oauth_client_scopes_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `oauth_scopes` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_client_grants` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `grant_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_client_grants_client_id_index` (`client_id`),
          KEY `oauth_client_grants_grant_id_index` (`grant_id`),
          CONSTRAINT `oauth_client_grants_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          CONSTRAINT `oauth_client_grants_grant_id_foreign` FOREIGN KEY (`grant_id`) REFERENCES `oauth_grants` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_client_endpoints` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `client_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `redirect_uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `oauth_client_endpoints_client_id_redirect_uri_unique` (`client_id`,`redirect_uri`),
          CONSTRAINT `oauth_client_endpoints_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_auth_codes` (
          `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `session_id` int(10) unsigned NOT NULL,
          `redirect_uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `expire_time` int(11) NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_auth_codes_session_id_index` (`session_id`),
          CONSTRAINT `oauth_auth_codes_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `oauth_sessions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_auth_code_scopes` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `auth_code_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `scope_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_auth_code_scopes_auth_code_id_index` (`auth_code_id`),
          KEY `oauth_auth_code_scopes_scope_id_index` (`scope_id`),
          CONSTRAINT `oauth_auth_code_scopes_auth_code_id_foreign` FOREIGN KEY (`auth_code_id`) REFERENCES `oauth_auth_codes` (`id`) ON DELETE CASCADE,
          CONSTRAINT `oauth_auth_code_scopes_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `oauth_scopes` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        DB::statement('CREATE TABLE `oauth_access_token_scopes` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `access_token_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `scope_id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `oauth_access_token_scopes_access_token_id_index` (`access_token_id`),
          KEY `oauth_access_token_scopes_scope_id_index` (`scope_id`),
          CONSTRAINT `oauth_access_token_scopes_access_token_id_foreign` FOREIGN KEY (`access_token_id`) REFERENCES `oauth_access_tokens` (`id`) ON DELETE CASCADE,
          CONSTRAINT `oauth_access_token_scopes_scope_id_foreign` FOREIGN KEY (`scope_id`) REFERENCES `oauth_scopes` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');


    }
}
