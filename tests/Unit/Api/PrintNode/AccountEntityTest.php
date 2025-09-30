<?php

declare(strict_types=1);

use Rawilk\Printing\Api\PrintNode\Entity\Account;

it('can check if account is active', function () {
    $activeAccount = new Account(['suspended' => false]);
    $suspendedAccount = new Account(['suspended' => true]);
    $defaultAccount = new Account([]);

    expect($activeAccount->isActive())->toBeTrue();
    expect($suspendedAccount->isActive())->toBeFalse();
    expect($defaultAccount->isActive())->toBeTrue(); // Default is active
});

it('can check if account is suspended', function () {
    $activeAccount = new Account(['suspended' => false]);
    $suspendedAccount = new Account(['suspended' => true]);

    expect($activeAccount->isSuspended())->toBeFalse();
    expect($suspendedAccount->isSuspended())->toBeTrue();
});

it('can check if account can create sub-accounts', function () {
    $canCreate = new Account(['canCreateSubAccounts' => true]);
    $cannotCreate = new Account(['canCreateSubAccounts' => false]);
    $defaultAccount = new Account([]);

    expect($canCreate->canCreateSubAccounts())->toBeTrue();
    expect($cannotCreate->canCreateSubAccounts())->toBeFalse();
    expect($defaultAccount->canCreateSubAccounts())->toBeFalse();
});

it('can calculate remaining sub-accounts', function () {
    // Account that can't create sub-accounts
    $cannotCreate = new Account(['canCreateSubAccounts' => false]);
    expect($cannotCreate->remainingSubAccounts())->toBe(0);

    // Account with unlimited sub-accounts
    $unlimited = new Account([
        'canCreateSubAccounts' => true,
        'maxSubAccounts' => null,
        'childAccounts' => 5,
    ]);
    expect($unlimited->remainingSubAccounts())->toBeNull();

    // Account with limited sub-accounts
    $limited = new Account([
        'canCreateSubAccounts' => true,
        'maxSubAccounts' => 10,
        'childAccounts' => 3,
    ]);
    expect($limited->remainingSubAccounts())->toBe(7);

    // Account at limit
    $atLimit = new Account([
        'canCreateSubAccounts' => true,
        'maxSubAccounts' => 5,
        'childAccounts' => 5,
    ]);
    expect($atLimit->remainingSubAccounts())->toBe(0);

    // Account over limit (shouldn't happen but handle gracefully)
    $overLimit = new Account([
        'canCreateSubAccounts' => true,
        'maxSubAccounts' => 5,
        'childAccounts' => 7,
    ]);
    expect($overLimit->remainingSubAccounts())->toBe(0);
});

it('can check if account has API keys', function () {
    $withKeys = new Account([
        'apiKeys' => [
            ['key' => 'key1', 'description' => 'Dev'],
            ['key' => 'key2', 'description' => 'Prod'],
        ],
    ]);
    $withoutKeys = new Account(['apiKeys' => []]);
    $noKeysProperty = new Account([]);

    expect($withKeys->hasApiKeys())->toBeTrue();
    expect($withoutKeys->hasApiKeys())->toBeFalse();
    expect($noKeysProperty->hasApiKeys())->toBeFalse();
});

it('can get API key by description', function () {
    $account = new Account([
        'apiKeys' => [
            ['key' => 'dev-key', 'description' => 'Development'],
            ['key' => 'prod-key', 'description' => 'Production'],
        ],
    ]);

    $devKey = $account->getApiKey('Development');
    expect($devKey)->toBe(['key' => 'dev-key', 'description' => 'Development']);

    $prodKey = $account->getApiKey('Production');
    expect($prodKey)->toBe(['key' => 'prod-key', 'description' => 'Production']);

    $notFound = $account->getApiKey('Staging');
    expect($notFound)->toBeNull();
});

it('can check if account has tags', function () {
    $account = new Account([
        'tags' => [
            'plan' => 'premium',
            'region' => 'us-west',
        ],
    ]);

    expect($account->hasTag('plan'))->toBeTrue();
    expect($account->hasTag('region'))->toBeTrue();
    expect($account->hasTag('nonexistent'))->toBeFalse();
});

it('can get tag values', function () {
    $account = new Account([
        'tags' => [
            'plan' => 'premium',
            'priority' => 1,
            'features' => ['feature1', 'feature2'],
        ],
    ]);

    expect($account->getTag('plan'))->toBe('premium');
    expect($account->getTag('priority'))->toBe(1);
    expect($account->getTag('features'))->toBe(['feature1', 'feature2']);
    expect($account->getTag('nonexistent'))->toBeNull();
    expect($account->getTag('nonexistent', 'default'))->toBe('default');
});

it('can check if account is an integrator', function () {
    $integrator = new Account([
        'integrator' => [
            'maxChildAccounts' => 100,
            'features' => ['api_access'],
        ],
    ]);
    $regular = new Account([]);

    expect($integrator->isIntegrator())->toBeTrue();
    expect($regular->isIntegrator())->toBeFalse();
});

it('can check if account is a child account', function () {
    $childByEmail = new Account(['creatorEmail' => 'parent@example.com']);
    $childByRef = new Account(['creatorRef' => 'parent-ref']);
    $childByBoth = new Account([
        'creatorEmail' => 'parent@example.com',
        'creatorRef' => 'parent-ref',
    ]);
    $parentAccount = new Account([]);

    expect($childByEmail->isChildAccount())->toBeTrue();
    expect($childByRef->isChildAccount())->toBeTrue();
    expect($childByBoth->isChildAccount())->toBeTrue();
    expect($parentAccount->isChildAccount())->toBeFalse();
});

it('can get account statistics summary', function () {
    $account = new Account([
        'numComputers' => 5,
        'numPrinters' => 10,
        'numPrintJobs' => 100,
        'totalPrints' => 500,
        'childAccounts' => 3,
        'connectedClients' => 2,
    ]);

    $stats = $account->getStatsSummary();

    expect($stats)->toBe([
        'computers' => 5,
        'printers' => 10,
        'print_jobs' => 100,
        'total_prints' => 500,
        'child_accounts' => 3,
        'connected_clients' => 2,
    ]);

    // Test with missing properties
    $emptyAccount = new Account([]);
    $emptyStats = $emptyAccount->getStatsSummary();

    expect($emptyStats)->toBe([
        'computers' => 0,
        'printers' => 0,
        'print_jobs' => 0,
        'total_prints' => 0,
        'child_accounts' => 0,
        'connected_clients' => 0,
    ]);
});