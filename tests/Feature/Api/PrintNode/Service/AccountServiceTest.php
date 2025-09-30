<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Rawilk\Printing\Api\PrintNode\PrintNodeClient;
use Rawilk\Printing\Api\PrintNode\Resources\Whoami;
use Rawilk\Printing\Api\PrintNode\Service\AccountService;
use Rawilk\Printing\Tests\Feature\Api\PrintNode\FakesPrintNodeRequests;

uses(FakesPrintNodeRequests::class);

beforeEach(function () {
    Http::preventStrayRequests();
    $this->fakeRequests();

    $client = new PrintNodeClient(['api_key' => 'my-key']);
    $this->service = new AccountService($client);
});

it('creates a child account', function () {
    $this->fakeRequest('whoami');

    $result = $this->service->create([
        'Account' => [
            'firstname' => '-',
            'lastname' => '-',
            'email' => 'child@example.com',
            'password' => 'password1234',
            'creatorRef' => 'child-1',
        ],
        'ApiKeys' => ['dev'],
        'Tags' => ['tier' => 'premium'],
    ]);

    expect($result)->toBeInstanceOf(Whoami::class);
});

it('updates a child account with headers', function () {
    $this->fakeRequest('whoami', expectation: function (Request $request) {
        expect($request->method())->toBe('PATCH')
            ->and($request->header('X-Child-Account-By-Email'))->toBe(['child@example.com']);
    });

    $result = $this->service->update([
        'email' => 'new.email@example.com',
    ], opts: [
        'child_account_by_email' => 'child@example.com',
    ]);

    expect($result)->toBeInstanceOf(Whoami::class);
});

it('sets account state with raw body', function () {
    $this->fakeRequest(function () {
        return [];
    }, code: 204, expectation: function (Request $request) {
        expect($request->method())->toBe('PUT')
            ->and($request->url())->toEndWith('/account/state')
            ->and($request->body())->toBe('"suspended"');
    });

    $response = $this->service->setState('suspended', opts: [
        'child_account_by_id' => 123,
    ]);

    expect($response)->toBeArray()->toBeEmpty();
});

it('deletes a child account', function () {
    $this->fakeRequest(function () {
        return ['affected' => [123]];
    }, expectation: function (Request $request) {
        expect($request->method())->toBe('DELETE');
    });

    $response = $this->service->delete(opts: [
        'child_account_by_creator_ref' => 'child-1',
    ]);

    expect($response)->toBeArray();
});

