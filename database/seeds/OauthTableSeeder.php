<?php
namespace TenFour\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use TenFour\Models\User;

class OauthTableSeeder extends Seeder
{
    public function run()
    {
        $clients = $this->container->db->table('oauth_clients');
        if ($clients->where('id', '=', $this->container->config->get('tenfour.app_client.client_id'))->first() === null) {
            $clients->insert([
                [
                    'id'         => $this->container->config->get('tenfour.app_client.client_id'),
                    'secret'     => $this->container->config->get('tenfour.app_client.client_secret'),
                    'password_client' => true,
                    'revoked'    => false,
                    'name'       => 'TenFour',
                    // 'created_at' => datetime(),
                    // 'updated_at' => time()
                ]
            ]);
        }

    }
}
