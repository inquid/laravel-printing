<?php

declare(strict_types=1);

use Rawilk\Printing\Api\PrintNode\Entity\Account;
use Rawilk\Printing\Api\PrintNode\PrintNodeClient;
use Rawilk\Printing\Api\PrintNode\Requests\AccountRequest;
use Rawilk\Printing\Api\PrintNode\Service\AccountService;

beforeEach(function () {
    $this->client = Mockery::mock(PrintNodeClient::class);
    $this->service = new AccountService($this->client);
});

it('can create a child account', function () {
    $accountData = [
        'email' => 'child@example.com',
        'password' => 'securepassword123',
        'creatorRef' => 'customer-123',
        'apiKeys' => ['development', 'production'],
        'tags' => ['plan' => 'premium'],
    ];

    $expectedResponse = new Account([
        'id' => 12345,
        'email' => 'child@example.com',
        'creatorRef' => 'customer-123',
        'state' => 'active',
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with(
            'POST',
            '/account',
            Mockery::on(function ($data) {
                return $data['Account']['email'] === 'child@example.com'
                    && $data['Account']['password'] === 'securepassword123'
                    && $data['Account']['creatorRef'] === 'customer-123'
                    && $data['ApiKeys'] === ['development', 'production']
                    && $data['Tags']['plan'] === 'premium';
            }),
            [],
            Account::class
        )
        ->andReturn($expectedResponse);

    $result = $this->service->create($accountData);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->id)->toBe(12345);
    expect($result->email)->toBe('child@example.com');
});

it('can create a child account using AccountRequest', function () {
    $request = AccountRequest::make()
        ->email('child@example.com')
        ->password('securepassword123')
        ->creatorRef('customer-456')
        ->apiKeys(['api-key-1', 'api-key-2'])
        ->addTag('tier', 'gold');

    $expectedResponse = new Account([
        'id' => 67890,
        'email' => 'child@example.com',
        'creatorRef' => 'customer-456',
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with(
            'POST',
            '/account',
            Mockery::on(function ($data) {
                return $data['Account']['email'] === 'child@example.com'
                    && $data['Account']['password'] === 'securepassword123'
                    && $data['Account']['creatorRef'] === 'customer-456'
                    && $data['ApiKeys'] === ['api-key-1', 'api-key-2']
                    && $data['Tags']['tier'] === 'gold';
            }),
            [],
            Account::class
        )
        ->andReturn($expectedResponse);

    $result = $this->service->create($request);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->id)->toBe(67890);
});

it('can get a specific child account', function () {
    $accountId = 12345;
    $expectedResponse = new Account([
        'id' => $accountId,
        'email' => 'child@example.com',
        'state' => 'active',
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with('GET', "/account/{$accountId}", [], [], Account::class)
        ->andReturn($expectedResponse);

    $result = $this->service->get($accountId);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->id)->toBe($accountId);
});

it('can get all child accounts', function () {
    $accounts = [
        new Account(['id' => 1, 'email' => 'child1@example.com']),
        new Account(['id' => 2, 'email' => 'child2@example.com']),
        new Account(['id' => 3, 'email' => 'child3@example.com']),
    ];

    $this->client->shouldReceive('requestCollection')
        ->once()
        ->with('GET', '/account', [], [], Account::class)
        ->andReturn(collect($accounts));

    $result = $this->service->all();

    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($result)->toHaveCount(3);
    expect($result->first())->toBeInstanceOf(Account::class);
});

it('can update a child account', function () {
    $accountId = 12345;
    $updateData = ['suspended' => true];
    
    $expectedResponse = new Account([
        'id' => $accountId,
        'suspended' => true,
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with('PATCH', "/account/{$accountId}", $updateData, [], Account::class)
        ->andReturn($expectedResponse);

    $result = $this->service->update($accountId, $updateData);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->suspended)->toBeTrue();
});

it('can suspend a child account', function () {
    $accountId = 12345;
    
    $expectedResponse = new Account([
        'id' => $accountId,
        'suspended' => true,
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with('PATCH', "/account/{$accountId}", ['suspended' => true], [], Account::class)
        ->andReturn($expectedResponse);

    $result = $this->service->suspend($accountId);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->suspended)->toBeTrue();
});

it('can activate a suspended child account', function () {
    $accountId = 12345;
    
    $expectedResponse = new Account([
        'id' => $accountId,
        'suspended' => false,
    ]);

    $this->client->shouldReceive('request')
        ->once()
        ->with('PATCH', "/account/{$accountId}", ['suspended' => false], [], Account::class)
        ->andReturn($expectedResponse);

    $result = $this->service->activate($accountId);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->suspended)->toBeFalse();
});

it('can delete a child account', function () {
    $accountId = 12345;

    $this->client->shouldReceive('request')
        ->once()
        ->with('DELETE', "/account/{$accountId}")
        ->andReturn(null);

    $result = $this->service->delete($accountId);

    expect($result)->toBeTrue();
});

it('can get account statistics', function () {
    $accountId = 12345;
    $stats = [
        'computers' => 5,
        'printers' => 10,
        'print_jobs' => 100,
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('GET', "/account/{$accountId}/stats")
        ->andReturn($stats);

    $result = $this->service->stats($accountId);

    expect($result)->toBe($stats);
});

it('can get statistics for all child accounts', function () {
    $stats = [
        'total_accounts' => 10,
        'active_accounts' => 8,
        'suspended_accounts' => 2,
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('GET', '/account/stats')
        ->andReturn($stats);

    $result = $this->service->stats();

    expect($result)->toBe($stats);
});

it('can download account data', function () {
    $accountId = 12345;
    $downloadData = [
        'account' => ['id' => $accountId],
        'computers' => [],
        'printers' => [],
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('GET', "/account/{$accountId}/download")
        ->andReturn($downloadData);

    $result = $this->service->download($accountId);

    expect($result)->toBe($downloadData);
});

it('can get API keys for a child account', function () {
    $accountId = 12345;
    $apiKeys = [
        ['key' => 'key1', 'description' => 'Development'],
        ['key' => 'key2', 'description' => 'Production'],
    ];

    $this->client->shouldReceive('requestCollection')
        ->once()
        ->with('GET', "/account/{$accountId}/apikeys")
        ->andReturn(collect($apiKeys));

    $result = $this->service->apiKeys($accountId);

    expect($result)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($result)->toHaveCount(2);
});

it('can create an API key for a child account', function () {
    $accountId = 12345;
    $description = 'Staging Environment';
    $apiKeyResponse = [
        'key' => 'new-api-key-123',
        'description' => $description,
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('POST', "/account/{$accountId}/apikey", ['description' => $description])
        ->andReturn($apiKeyResponse);

    $result = $this->service->createApiKey($accountId, $description);

    expect($result)->toBe($apiKeyResponse);
});

it('can delete an API key from a child account', function () {
    $accountId = 12345;
    $apiKey = 'api-key-to-delete';

    $this->client->shouldReceive('request')
        ->once()
        ->with('DELETE', "/account/{$accountId}/apikey/{$apiKey}")
        ->andReturn(null);

    $result = $this->service->deleteApiKey($accountId, $apiKey);

    expect($result)->toBeTrue();
});

it('can get tags for a child account', function () {
    $accountId = 12345;
    $tags = [
        'plan' => 'premium',
        'region' => 'us-west',
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('GET', "/account/{$accountId}/tags")
        ->andReturn($tags);

    $result = $this->service->tags($accountId);

    expect($result)->toBe($tags);
});

it('can update tags for a child account', function () {
    $accountId = 12345;
    $tags = [
        'plan' => 'enterprise',
        'support' => '24/7',
    ];

    $this->client->shouldReceive('request')
        ->once()
        ->with('PATCH', "/account/{$accountId}/tags", $tags)
        ->andReturn($tags);

    $result = $this->service->updateTags($accountId, $tags);

    expect($result)->toBe($tags);
});

it('can delete a tag from a child account', function () {
    $accountId = 12345;
    $tagName = 'obsolete-tag';

    $this->client->shouldReceive('request')
        ->once()
        ->with('DELETE', "/account/{$accountId}/tag/{$tagName}")
        ->andReturn(null);

    $result = $this->service->deleteTag($accountId, $tagName);

    expect($result)->toBeTrue();
});

it('can act as a child account by ID', function () {
    $accountId = 12345;

    $this->client->shouldReceive('setDefaultHeader')
        ->once()
        ->with('X-Child-Account-By-Id', '12345')
        ->andReturn($this->client);

    $result = $this->service->actAsChildAccount($accountId);

    expect($result)->toBe($this->service);
});

it('can act as a child account by creator reference', function () {
    $creatorRef = 'customer-123';

    $this->client->shouldReceive('setDefaultHeader')
        ->once()
        ->with('X-Child-Account-By-CreatorRef', 'customer-123')
        ->andReturn($this->client);

    $result = $this->service->actAsChildAccountByRef($creatorRef);

    expect($result)->toBe($this->service);
});

it('can stop acting as a child account', function () {
    $this->client->shouldReceive('removeDefaultHeader')
        ->once()
        ->with('X-Child-Account-By-Id')
        ->andReturn($this->client);

    $this->client->shouldReceive('removeDefaultHeader')
        ->once()
        ->with('X-Child-Account-By-CreatorRef')
        ->andReturn($this->client);

    $result = $this->service->stopActingAsChildAccount();

    expect($result)->toBe($this->service);
});