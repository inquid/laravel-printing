<?php

declare(strict_types=1);

use Rawilk\Printing\Api\PrintNode\PrintNodeClient;
use Rawilk\Printing\Api\PrintNode\Service\AccountService;
use Rawilk\Printing\Api\PrintNode\Service\WhoamiService;

beforeEach(function () {
    $this->client = new PrintNodeClient('test_123');
});

it('exposes properties for services', function () {
    expect($this->client->whoami)->toBeInstanceOf(WhoamiService::class)
        ->and($this->client->accounts)->toBeInstanceOf(AccountService::class);
});
