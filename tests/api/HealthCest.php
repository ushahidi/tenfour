<?php

class HealthCest
{
    protected $endpoint = '/health';

    /*
     * Check the health (shallow)
     *
     */
    public function checkHealth(ApiTester $I)
    {
        $I->wantTo('Check the health (shallow) of the API');
        $I->sendGET($this->endpoint);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "health" => "OK"
        ]);
    }

    /*
     * Check the health (deep)
     *
     */
    public function checkHealthDeep(ApiTester $I)
    {
        $I->wantTo('Check the health (deep) of the API');
        $I->sendGET($this->endpoint . "/deep");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "health" => "OK",
            "database" => "OK"
        ]);
    }
}
