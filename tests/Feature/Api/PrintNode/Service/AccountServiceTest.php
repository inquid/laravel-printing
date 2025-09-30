<?php

use Rawilk\Printing\Api\PrintNode\PrintNodeClient;
use Rawilk\Printing\Api\PrintNode\Resources\Account;
use Rawilk\Printing\Api\PrintNode\Service\AccountService;

beforeEach(function () {
    $this->client = new PrintNodeClient;
    $this->service = new AccountService($this->client);
});

it('can create a new child account', function () {
    $params = [
        'Account' => [
            'email' => 'test@example.com',
            'password' => 'password123',
            'creatorRef' => 'test-ref-001',
        ],
        'ApiKeys' => ['development', 'production'],
        'Tags' => [
            'customerType' => 'premium',
            'region' => 'us-west',
        ],
    ];

    $response = $this->service->create($params);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can retrieve an account by id', function () {
    $accountId = 123;

    $response = $this->service->retrieve($accountId);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can update an existing account', function () {
    $accountId = 123;
    $params = [
        'Account' => [
            'email' => 'updated@example.com',
        ],
        'Tags' => [
            'customerType' => 'enterprise',
        ],
    ];

    $response = $this->service->update($accountId, $params);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can delete an account', function () {
    $accountId = 123;

    $response = $this->service->delete($accountId);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can list all child accounts', function () {
    $response = $this->service->all();

    expect($response)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('can download child account data', function () {
    $response = $this->service->download();

    expect($response)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('can suspend an account', function () {
    $accountId = 123;

    $response = $this->service->suspend($accountId);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can activate a suspended account', function () {
    $accountId = 123;

    $response = $this->service->activate($accountId);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can add tags to an account', function () {
    $accountId = 123;
    $tags = [
        'newTag' => 'newValue',
        'anotherTag' => 'anotherValue',
    ];

    $response = $this->service->addTags($accountId, $tags);

    expect($response)->toBeInstanceOf(Account::class);
});

it('can generate new API keys for an account', function () {
    $accountId = 123;
    $keyNames = ['staging', 'testing'];

    $response = $this->service->generateApiKeys($accountId, $keyNames);

    expect($response)->toBeInstanceOf(Account::class);
});

it('sets default values for deprecated firstname and lastname fields', function () {
    $params = [
        'email' => 'test@example.com',
        'password' => 'password123',
    ];

    // Mock the request to verify the parameters
    $this->client->shouldReceive('request')
        ->once()
        ->with(
            'post',
            '/account',
            [
                'Account' => [
                    'firstname' => '-',
                    'lastname' => '-',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                ]
            ],
            null,
            Account::class
        )
        ->andReturn(new Account);

    $this->service->create($params);
});

it('preserves custom firstname and lastname if provided', function () {
    $params = [
        'Account' => [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]
    ];

    // Mock the request to verify the parameters
    $this->client->shouldReceive('request')
        ->once()
        ->with(
            'post',
            '/account',
            [
                'Account' => [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                ]
            ],
            null,
            Account::class
        )
        ->andReturn(new Account);

    $this->service->create($params);
});