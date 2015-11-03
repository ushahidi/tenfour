<?php

class OrganizationTest extends ApiTester {

    /** @test */

    public function it_fetches_organizations()
    {
        $this->makeOrganization();

        $this->getJson('api/v1/organizations');

        $this->assertResponseOk();
    }

    private function makeOrganization($organizationfields = [])
    {
        $organization = array_merge([
            'name' => $this->fake->word,
            'sub_domain' => $this->fake->word

        ], $organizationfields);

        Organization::create($organization);

    }

	
}
