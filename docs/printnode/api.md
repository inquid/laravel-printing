---
title: API
sort: 4
---

## Introduction

The functionality provided by the PrintNode driver should work for most applications, however you may interact with our api wrapper directly if necessary.

To get started, you will need to resolve the client out of the container:

```php
use Rawilk\Printing\Api\PrintNode\PrintNodeClient;

$client = app(PrintNodeClient::class, [
    'config' => ['api_key' => 'my-key'],
]);
```

> {note} Providing an api key to the constructor here is optional, however it will need to be set manually on the client before a request is made. The client **will not** resolve your api key from the config value in the package configuration file.

## Setting an API Key

There are a few ways to set the api key for requests on the client. The first way is shown above in the [Introduction](#user-content-introduction).

Another way is to set it using request options when calling a method on a [Service](#user-content-services).

```php
$client->printers->retrieve($printerId, opts: ['api_key' => 'my-key']);
```

You may also choose to set the api key on the `PrintNode` class itself. When the client does not detect an api key on the request, it will defer to this value. This is typically done in a service provider in your application.

```php
use Rawilk\Printing\Api\PrintNode\PrintNode;

PrintNode::setApiKey('my-key');
```

> {note} An api key set on the client itself, or passed through as a request option (via `$opts` arguments) will take precedence over this.

> {tip} You can also set the api key globally for PrintNode like this when using the `Printing` facade. Keep in mind though that the package configuration value will take precedence over this, unless you set the `key` value to `null` in the config.

## Services

The PrintNode API implementation for this package splits the calls out into service classes, depending on the resource you're creating or retrieving from the api.

For example, to retrieve all printers for an account, you would use the `printers` service class on the client.

```php
$client->printers->all();
```

More information about each service can be found on that service's doc page.

## Resources

A resource class represents some kind of resource retrieved from the PrintNode API, such as a printer or computer. When the `Printing` facade is used, the PrintNode entity objects will contain a reference to their relevant api resource objects as well.

All the resource objects supported by the package can be found here: https://github.com/rawilk/laravel-printing/tree/{branch}/src/Api/PrintNode/Resources

## Request Options

Most requests performed by the client accept request options, which can be used to set the api key or certain headers on the request, such as an idempotency key. Any time request options are supported, you can specify them through the `$opts` argument on method calls.

We recommend using an array, as the package will parse through that and create the `RequestOptions` object for you.

```php
$client->printJobs->create([...], opts: [
    'api_key' => 'my-key',
    'idempotency_key' => 'foo',
]);
```

> {tip} The `$opts` argument is accepted in most method calls to the API when using the `Printing` facade as well.

### Integrator / Child Accounts

If your PrintNode account is an Integrator account, you can act on behalf of a child account by passing one of these keys in `$opts` (the client will translate them to the appropriate `X-Child-Account-*` headers):

- `child_account_by_id` → sets `X-Child-Account-By-Id`
- `child_account_by_email` → sets `X-Child-Account-By-Email`
- `child_account_by_creator_ref` → sets `X-Child-Account-By-CreatorRef`

Example: list printers for a child account identified by email:

```php
$client->printers->all(opts: [
    'child_account_by_email' => 'customer@example.com',
]);
```

### Managing Accounts (Integrator only)

Use the `accounts` service for account creation and management:

```php
// Create a child account
$created = $client->accounts->create([
    'Account' => [
        'firstname' => '-',
        'lastname' => '-',
        'email' => 'customer@example.com',
        'password' => 'securepassword',
        'creatorRef' => 'customer-123',
    ],
    'ApiKeys' => ['development', 'production'],
    'Tags' => ['plan' => 'pro'],
]);

// Update a child account (targeted by email)
$updated = $client->accounts->update([
    'email' => 'new.email@example.com',
], opts: [
    'child_account_by_email' => 'customer@example.com',
]);

// Suspend a child account
$client->accounts->setState('suspended', opts: [
    'child_account_by_id' => 123,
]);

// Delete a child account
$client->accounts->delete(opts: [
    'child_account_by_creator_ref' => 'customer-123',
]);
```

## Pagination Params

For requests that can be paginated, here are the supported array key values that can be sent through with a `$params` argument:

- `limit`: The max number of rows that will be returned - default is 100.
- `dir`: Sort direction, `asc` for Ascending, `desc` for Descending.
- `after`: A resource ID to offset the pagination by.

Example:

```php
$client->computers->all([
    'limit' => 5,
    'dir' => 'desc',
    'after' => 1010,
]);
```

> {tip} These pagination params can also be used when using the `Printing` facade.
