<?php
namespace RollCall\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RollCall\Models\User;

class OauthTableSeeder extends Seeder
{
    public function run()
    {
        $this->container->db->table('oauth_clients')->insert([
            [
                'id'         => $this->container->config->get('rollcall.app_client.client_id'),
                'secret'     => $this->container->config->get('rollcall.app_client.client_secret'),
                'name'       => 'RollCall',
                'created_at' => time(),
                'updated_at' => time()
            ],
        ]);
        $this->container->db->table('oauth_scopes')->insert([
            ['id' => 'basic', 'description' => 'The base API scope', 'created_at' => time(), 'updated_at' => time()],
            ['id' => 'user', 'description' => 'user', 'created_at' => time(), 'updated_at' => time()],
            ['id' => 'organization', 'description' => 'organization', 'created_at' => time(), 'updated_at' => time()],
        ]);
    }
}
