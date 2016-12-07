<?php
$I = new ApiTester($scenario);
$I->wantTo('Get a list of users without proper credentials');
$I->sendGET('/api/v1/users');
$I->seeResponseCodeIs(401);
