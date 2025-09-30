<?php

declare(strict_types=1);

use Carbon\CarbonInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Rawilk\Printing\Api\PrintNode\PrintNodeClient;
use Rawilk\Printing\Api\PrintNode\Resources\Account;
use Rawilk\Printing\Api\PrintNode\Service\AccountService;
use Rawilk\Printing\Tests\Feature\Api\PrintNode\FakesPrintNodeRequests;

uses(FakesPrintNodeRequests::class);

beforeEach(function () {
    Http::preventStrayRequests();

    $this->fakeRequests();

    $client = new PrintNodeClient(['api_key' => 'my-key']);
    $this->service = new AccountService($client);
});

it('retrieves all child accounts', function () {
    $this->fakeRequest('accounts');

    $response = $this->service->all();

    expect($response)->toHaveCount(2)
        ->toContainOnlyInstancesOf(Account::class);
});

it('can retrieve a specific account', function () {
    $this->fakeRequest('account_single', expectation: function (Request $request) {
        expect($request->url())->toEndWith('/account/12345');
    });

    $account = $this->service->retrieve(12345);

    expect($account)
        ->not->toBeNull()
        ->id->toBe(12345)
        ->email->toBe('customer1@example.com')
        ->creatorEmail->toBe('integrator@example.com')
        ->creatorRef->toBe('customer_123')
        ->state->toBe('active')
        ->credits->toBe(1000)
        ->isActive()->toBeTrue()
        ->isSuspended()->toBeFalse()
        ->createdAt()->toBeInstanceOf(CarbonInterface::class)
        ->createdAt()->toBe(Date::parse('2024-01-15 10:30:00'));
});

it('can create a child account', function () {
    $this->fakeRequest('account_created', expectation: function (Request $request) {
        expect($request->method())->toBe('POST')
            ->and($request->url())->toEndWith('/account')
            ->and($request->data())->toMatchArray([
                'Account' => [
                    'firstname' => '-',
                    'lastname' => '-',
                    'email' => 'newcustomer@example.com',
                    'password' => 'securepass123',
                    'creatorRef' => 'customer_789',
                ],
                'ApiKeys' => ['production'],
                'Tags' => [
                    'plan' => 'trial',
                    'source' => 'api',
                ],
            ]);
    });

    $account = $this->service->create([
        'Account' => [
            'firstname' => '-',
            'lastname' => '-',
            'email' => 'newcustomer@example.com',
            'password' => 'securepass123',
            'creatorRef' => 'customer_789',
        ],
        'ApiKeys' => ['production'],
        'Tags' => [
            'plan' => 'trial',
            'source' => 'api',
        ],
    ]);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->id->toBe(12347)
        ->email->toBe('newcustomer@example.com')
        ->creatorRef->toBe('customer_789')
        ->state->toBe('active')
        ->credits->toBe(100);
});

it('can modify a child account', function () {
    $this->fakeRequest('account_single', expectation: function (Request $request) {
        expect($request->method())->toBe('PATCH')
            ->and($request->url())->toEndWith('/account/12345')
            ->and($request->data())->toMatchArray([
                'Account' => [
                    'email' => 'newemail@example.com',
                ],
            ]);
    });

    $account = $this->service->modify(12345, [
        'Account' => [
            'email' => 'newemail@example.com',
        ],
    ]);

    expect($account)->toBeInstanceOf(Account::class);
});

it('can delete a child account', function () {
    $this->fakeRequest(
        callback: fn () => [12345],
        expectation: function (Request $request) {
            expect($request->method())->toBe('DELETE')
                ->and($request->url())->toEndWith('/account/12345');
        },
    );

    $response = $this->service->delete(12345);

    expect($response)->toBeArray()
        ->toEqualCanonicalizing([12345]);
});

it('can delete multiple child accounts', function () {
    $this->fakeRequest(
        callback: fn () => [12345, 12346],
        expectation: function (Request $request) {
            expect($request->method())->toBe('DELETE')
                ->and($request->url())->toContain('/account/12345,12346');
        },
    );

    $response = $this->service->deleteMany([12345, 12346]);

    expect($response)->toBeArray()
        ->toEqualCanonicalizing([12345, 12346]);
});

it('can add credits to a child account', function () {
    $this->fakeRequest('account_single', expectation: function (Request $request) {
        expect($request->method())->toBe('PATCH')
            ->and($request->url())->toEndWith('/account/12345')
            ->and($request->data())->toMatchArray([
                'Account[credits]' => 500,
            ]);
    });

    $account = $this->service->addCredits(12345, 500);

    expect($account)->toBeInstanceOf(Account::class);
});

it('can suspend a child account', function () {
    $this->fakeRequest('account_suspended', expectation: function (Request $request) {
        expect($request->method())->toBe('PATCH')
            ->and($request->url())->toEndWith('/account/12345')
            ->and($request->data())->toMatchArray([
                'Account[state]' => 'suspended',
            ]);
    });

    $account = $this->service->suspend(12345);

    expect($account)
        ->toBeInstanceOf(Account::class)
        ->state->toBe('suspended')
        ->isSuspended()->toBeTrue()
        ->isActive()->toBeFalse();
});

it('can activate a child account', function () {
    $this->fakeRequest('account_single', expectation: function (Request $request) {
        expect($request->method())->toBe('PATCH')
            ->and($request->url())->toEndWith('/account/12345')
            ->and($request->data())->toMatchArray([
                'Account[state]' => 'active',
            ]);
    });

    $account = $this->service->activate(12345);

    expect($account)->toBeInstanceOf(Account::class);
});

it('can delete a tag from a child account', function () {
    $this->fakeRequest(
        callback: fn () => [],
        expectation: function (Request $request) {
            expect($request->method())->toBe('DELETE')
                ->and($request->url())->toContain('/account/12345/tag/plan');
        },
    );

    $response = $this->service->deleteTag(12345, 'plan');

    expect($response)->toBeArray();
});

it('can delete an API key from a child account', function () {
    $this->fakeRequest(
        callback: fn () => [],
        expectation: function (Request $request) {
            expect($request->method())->toBe('DELETE')
                ->and($request->url())->toContain('/account/12345/apikey/');
        },
    );

    $response = $this->service->deleteApiKey(12345, 'api_key_abc123');

    expect($response)->toBeArray();
});