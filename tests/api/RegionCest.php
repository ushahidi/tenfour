<?php


class RegionCest
{
    protected $endpoint = '/api/v1/organizations';

    public function getSupportedRegions(ApiTester $I)
    {
        $org_id = 2;
        $I->wantTo('Get a list of regions that are supported for an organization');
        $I->amAuthenticatedAsOrgAdmin();
        $I->sendGET($this->endpoint . "/$org_id/regions");
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesXpath('//regions/name');
        $I->seeResponseJsonMatchesXpath('//regions/country_code');
    }
}
