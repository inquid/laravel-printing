<?php

declare(strict_types=1);

use Rawilk\Printing\Api\PrintNode\Requests\AccountRequest;

it('can create an account request with fluent interface', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123')
        ->creatorRef('ref-123')
        ->apiKeys(['dev', 'prod'])
        ->tags(['plan' => 'premium']);

    $data = $request->toArray();

    expect($data['Account']['email'])->toBe('test@example.com');
    expect($data['Account']['password'])->toBe('password123');
    expect($data['Account']['creatorRef'])->toBe('ref-123');
    expect($data['Account']['firstname'])->toBe('-');
    expect($data['Account']['lastname'])->toBe('-');
    expect($data['ApiKeys'])->toBe(['dev', 'prod']);
    expect($data['Tags']['plan'])->toBe('premium');
});

it('can create an account request from array', function () {
    $request = new AccountRequest([
        'email' => 'test@example.com',
        'password' => 'password123',
        'creatorRef' => 'ref-456',
        'apiKeys' => ['staging'],
        'tags' => ['tier' => 'gold'],
    ]);

    $data = $request->toArray();

    expect($data['Account']['email'])->toBe('test@example.com');
    expect($data['Account']['password'])->toBe('password123');
    expect($data['Account']['creatorRef'])->toBe('ref-456');
    expect($data['ApiKeys'])->toBe(['staging']);
    expect($data['Tags']['tier'])->toBe('gold');
});

it('can handle nested array format', function () {
    $request = new AccountRequest([
        'Account' => [
            'email' => 'nested@example.com',
            'password' => 'nestedpass123',
            'creatorRef' => 'nested-ref',
        ],
        'ApiKeys' => ['api1', 'api2'],
        'Tags' => ['nested' => 'true'],
    ]);

    $data = $request->toArray();

    expect($data['Account']['email'])->toBe('nested@example.com');
    expect($data['Account']['password'])->toBe('nestedpass123');
    expect($data['Account']['creatorRef'])->toBe('nested-ref');
    expect($data['ApiKeys'])->toBe(['api1', 'api2']);
    expect($data['Tags']['nested'])->toBe('true');
});

it('validates email format', function () {
    $request = AccountRequest::make();

    expect(fn() => $request->email('invalid-email'))
        ->toThrow(InvalidArgumentException::class, 'Invalid email address: invalid-email');
});

it('validates password length', function () {
    $request = AccountRequest::make();

    expect(fn() => $request->password('short'))
        ->toThrow(InvalidArgumentException::class, 'Password must be at least 8 characters long');
});

it('requires email and password for validation', function () {
    $request = AccountRequest::make();

    expect(fn() => $request->toArray())
        ->toThrow(InvalidArgumentException::class, 'Email is required for creating an account');

    $request->email('test@example.com');

    expect(fn() => $request->toArray())
        ->toThrow(InvalidArgumentException::class, 'Password is required for creating an account');
});

it('sets default firstname and lastname if not provided', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123');

    $data = $request->toArray();

    expect($data['Account']['firstname'])->toBe('-');
    expect($data['Account']['lastname'])->toBe('-');
});

it('can add individual API keys', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123')
        ->addApiKey('key1')
        ->addApiKey('key2')
        ->addApiKey('key3');

    $data = $request->toArray();

    expect($data['ApiKeys'])->toBe(['key1', 'key2', 'key3']);
});

it('can add individual tags', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123')
        ->addTag('tag1', 'value1')
        ->addTag('tag2', 'value2');

    $data = $request->toArray();

    expect($data['Tags']['tag1'])->toBe('value1');
    expect($data['Tags']['tag2'])->toBe('value2');
});

it('can handle string apiKeys parameter', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123')
        ->apiKeys('single-key');

    $data = $request->toArray();

    expect($data['ApiKeys'])->toBe(['single-key']);
});

it('can override firstname and lastname', function () {
    $request = AccountRequest::make()
        ->email('test@example.com')
        ->password('password123')
        ->firstname('John')
        ->lastname('Doe');

    $data = $request->toArray();

    expect($data['Account']['firstname'])->toBe('John');
    expect($data['Account']['lastname'])->toBe('Doe');
});