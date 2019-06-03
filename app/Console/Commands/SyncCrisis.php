<?php

namespace TenFour\Console\Commands;

use Illuminate\Console\Command;

use TenFour\Models\User;
use TenFour\Models\Organization;
use TenFour\Models\Contact;
use League\Csv\Reader;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;

class SyncCrisis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aws:crisis:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check against an aws lambda function for potential crisis alerts affecting each user ';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $users = User::all();
        $microservice = config('services.tenfour.lambda_api_url_sync_crisis');
        $guzzle = new \GuzzleHttp\Client();
        try {

            $response = $guzzle->request(
                "GET",
                $microservice, 
                [
                    'query' => [
                        'lat' => 70.86240663480157,
                        'lon' => 22.4053386588057
                    ],
                    'headers' => [
                        'Cache-Control', 'no-cache',
                        'x-api-key' =>  config('services.tenfour.lambda_api_key_sync_crisis')
                    ]
                ]
            );
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();
            if ($statusCode === 200) {
                echo $content;
            } else {
                echo "Error : " . $statusCode . '\n' . $content;
            }
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }
    }
    // }
}
