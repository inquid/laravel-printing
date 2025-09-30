<?php

use Rawilk\Printing\Api\PrintNode\Resources\Account;

beforeEach(function () {
    $this->accountData = [
        'id' => 12345,
        'firstname' => '-',
        'lastname' => '-',
        'email' => 'customer@example.com',
        'canCreateSubAccounts' => false,
        'creatorEmail' => 'integrator@example.com',
        'creatorRef' => 'customer-ref-001',
        'childAccounts' => [],
        'credits' => 1000,
        'numComputers' => 1,
        'totalPrints' => 50,
        'versions' => [],
        'connected' => [],
        'Tags' => [
            'customerType' => 'premium',
            'region' => 'us-west',
        ],
        'ApiKeys' => [
            [
                'name' => 'development',
                'key' => 'dev-api-key-123',
            ],
            [
                'name' => 'production',
                'key' => 'prod-api-key-456',
            ],
        ],
        'state' => 'active',
        'permissions' => ['Restricted'],
    ];

    $this->account = new Account();
    $this->account->refreshFrom($this->accountData);
});

it('returns correct class URL', function () {
    expect(Account::classUrl())->toBe('/account');
});

it('returns correct resource URL without ID', function () {
    expect(Account::resourceUrl())->toBe('/account');
});

it('returns correct resource URL with ID', function () {
    expect(Account::resourceUrl(123))->toBe('/account/123');
});

it('correctly identifies active account', function () {
    expect($this->account->isActive())->toBeTrue();
});

it('correctly identifies inactive account', function () {
    $this->account->refreshFrom(['state' => 'suspended']);
    expect($this->account->isActive())->toBeFalse();
});

it('correctly identifies if account can create sub-accounts', function () {
    expect($this->account->canCreateSubAccounts())->toBeFalse();

    $this->account->refreshFrom(['canCreateSubAccounts' => true]);
    expect($this->account->canCreateSubAccounts())->toBeTrue();
});

it('returns child accounts', function () {
    expect($this->account->getChildAccounts())->toBe([]);

    $childAccounts = [
        ['id' => 123, 'email' => 'child@example.com'],
        ['id' => 124, 'email' => 'child2@example.com'],
    ];
    $this->account->refreshFrom(['childAccounts' => $childAccounts]);
    expect($this->account->getChildAccounts())->toBe($childAccounts);
});

it('correctly identifies if account has child accounts', function () {
    expect($this->account->hasChildAccounts())->toBeFalse();

    $this->account->refreshFrom(['childAccounts' => [['id' => 123]]]);
    expect($this->account->hasChildAccounts())->toBeTrue();
});

it('returns creator reference', function () {
    expect($this->account->getCreatorRef())->toBe('customer-ref-001');

    $this->account->refreshFrom(['creatorRef' => null]);
    expect($this->account->getCreatorRef())->toBeNull();
});

it('returns tags', function () {
    expect($this->account->getTags())->toBe([
        'customerType' => 'premium',
        'region' => 'us-west',
    ]);

    $this->account->refreshFrom(['Tags' => []]);
    expect($this->account->getTags())->toBe([]);
});

it('returns API keys', function () {
    $expectedApiKeys = [
        [
            'name' => 'development',
            'key' => 'dev-api-key-123',
        ],
        [
            'name' => 'production',
            'key' => 'prod-api-key-456',
        ],
    ];

    expect($this->account->getApiKeys())->toBe($expectedApiKeys);

    $this->account->refreshFrom(['ApiKeys' => []]);
    expect($this->account->getApiKeys())->toBe([]);
});

it('handles missing optional properties gracefully', function () {
    $minimalData = [
        'id' => 123,
        'email' => 'test@example.com',
        'state' => 'active',
    ];

    $account = new Account();
    $account->refreshFrom($minimalData);

    expect($account->canCreateSubAccounts())->toBeFalse();
    expect($account->getChildAccounts())->toBe([]);
    expect($account->hasChildAccounts())->toBeFalse();
    expect($account->getCreatorRef())->toBeNull();
    expect($account->getTags())->toBe([]);
    expect($account->getApiKeys())->toBe([]);
});