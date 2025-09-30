---
title: Account Service
sort: 2
---

## Introduction

The `AccountService` can be used to manage child accounts under an Integrator account. This service allows you to create, retrieve, modify, and delete child accounts for your customers, as well as manage their credits, tags, and API keys.

**Note:** To use this service, your PrintNode account must be upgraded to an Integrator account. See the [PrintNode documentation](https://www.printnode.com/en/docs/api/curl#account-download-management) for more information.

All methods are callable from the `PrintNodeClient` class.

```php
$accounts = $client->accounts->all();
```

See the [API Overview](/docs/laravel-printing/{version}/printnode/api) for more information on interacting with the PrintNode API.

## Reference

### Methods

<hr>

#### all

_Collection<int, Rawilk\Printing\Api\PrintNode\Resources\Account>_

Retrieves all child accounts under your Integrator account.

| param     | type                        | default |
| --------- | --------------------------- | ------- |
| `$params` | array\|null                 | null    |
| `$opts`   | null\|array\|RequestOptions | null    |

**Example:**

```php
$accounts = $client->accounts->all();
```

<hr>

#### retrieve

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Retrieve a specific child account by ID.

| param     | type                        | default | description                    |
| --------- | --------------------------- | ------- | ------------------------------ |
| `$id`     | int                         |         | the account's ID               |
| `$params` | array\|null                 | null    | not applicable to this request |
| `$opts`   | null\|array\|RequestOptions | null    |                                |

**Example:**

```php
$account = $client->accounts->retrieve(12345);
```

<hr>

#### create

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Create a new child account under your Integrator account.

| param     | type                        | default | description                        |
| --------- | --------------------------- | ------- | ---------------------------------- |
| `$params` | array                       |         | the account creation parameters    |
| `$opts`   | null\|array\|RequestOptions | null    |                                    |

**Parameters:**

- `Account[firstname]` - First name (deprecated, use "-" and rely on creatorRef)
- `Account[lastname]` - Last name (deprecated, use "-" and rely on creatorRef)
- `Account[email]` - Contact email address (required)
- `Account[password]` - Password (required, min 8 characters)
- `Account[creatorRef]` - Your unique reference for this account (recommended)
- `ApiKeys[]` - Array of API key names to create (max 10, max 16 bytes each)
- `Tags[]` - Object with tag names as keys and tag values as values (max 1024 bytes per value)

**Example:**

```php
$account = $client->accounts->create([
    'Account' => [
        'firstname' => '-',
        'lastname' => '-',
        'email' => 'customer@example.com',
        'password' => 'securepassword',
        'creatorRef' => 'customer_123',
    ],
    'ApiKeys' => ['production', 'development'],
    'Tags' => [
        'plan' => 'premium',
        'region' => 'us-east',
    ],
]);
```

<hr>

#### modify

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Modify an existing child account.

| param     | type                        | default | description                       |
| --------- | --------------------------- | ------- | --------------------------------- |
| `$id`     | int                         |         | the account's ID                  |
| `$params` | array                       |         | the account modification parameters |
| `$opts`   | null\|array\|RequestOptions | null    |                                   |

**Parameters:**

- `Account[email]` - New email address
- `Account[password]` - New password (min 8 characters)
- `Account[creatorRef]` - New creator reference
- `ApiKeys[]` - Array of API key names to create
- `Tags[]` - Object with tag names as keys and tag values as values

**Example:**

```php
$account = $client->accounts->modify(12345, [
    'Account' => [
        'email' => 'newemail@example.com',
    ],
    'Tags' => [
        'plan' => 'enterprise',
    ],
]);
```

<hr>

#### delete

_array_

Delete a child account. Method will return an array of affected account IDs.

| param     | type                        | default | description                    |
| --------- | --------------------------- | ------- | ------------------------------ |
| `$id`     | int                         |         | the account's ID               |
| `$params` | array\|null                 | null    | not applicable to this request |
| `$opts`   | null\|array\|RequestOptions | null    |                                |

**Example:**

```php
$deletedIds = $client->accounts->delete(12345);
```

<hr>

#### deleteMany

_array_

Delete multiple child accounts. Method will return an array of affected IDs.

| param     | type                        | default | description                       |
| --------- | --------------------------- | ------- | --------------------------------- |
| `$ids`    | array                       |         | the IDs of the accounts to delete |
| `$params` | array\|null                 | null    |                                   |
| `$opts`   | null\|array\|RequestOptions | null    |                                   |

**Example:**

```php
$deletedIds = $client->accounts->deleteMany([12345, 12346]);
```

<hr>

#### addCredits

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Add credits to a child account.

| param      | type                        | default | description                     |
| ---------- | --------------------------- | ------- | ------------------------------- |
| `$id`      | int                         |         | the account's ID                |
| `$credits` | int                         |         | the number of credits to add    |
| `$opts`    | null\|array\|RequestOptions | null    |                                 |

**Example:**

```php
$account = $client->accounts->addCredits(12345, 1000);
```

<hr>

#### setState

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Set the state of a child account (e.g., suspend or activate).

| param    | type                        | default | description                                |
| -------- | --------------------------- | ------- | ------------------------------------------ |
| `$id`    | int                         |         | the account's ID                           |
| `$state` | string                      |         | the state to set (e.g., 'active', 'suspended') |
| `$opts`  | null\|array\|RequestOptions | null    |                                            |

**Example:**

```php
$account = $client->accounts->setState(12345, 'suspended');
```

<hr>

#### suspend

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Suspend a child account.

| param   | type                        | default | description      |
| ------- | --------------------------- | ------- | ---------------- |
| `$id`   | int                         |         | the account's ID |
| `$opts` | null\|array\|RequestOptions | null    |                  |

**Example:**

```php
$account = $client->accounts->suspend(12345);
```

<hr>

#### activate

_Rawilk\Printing\Api\PrintNode\Resources\Account_

Activate a child account.

| param   | type                        | default | description      |
| ------- | --------------------------- | ------- | ---------------- |
| `$id`   | int                         |         | the account's ID |
| `$opts` | null\|array\|RequestOptions | null    |                  |

**Example:**

```php
$account = $client->accounts->activate(12345);
```

<hr>

#### deleteTag

_array_

Delete a tag from a child account.

| param      | type                        | default | description                |
| ---------- | --------------------------- | ------- | -------------------------- |
| `$id`      | int                         |         | the account's ID           |
| `$tagName` | string                      |         | the name of the tag to delete |
| `$opts`    | null\|array\|RequestOptions | null    |                            |

**Example:**

```php
$client->accounts->deleteTag(12345, 'plan');
```

<hr>

#### deleteApiKey

_array_

Delete an API key from a child account.

| param     | type                        | default | description            |
| --------- | --------------------------- | ------- | ---------------------- |
| `$id`     | int                         |         | the account's ID       |
| `$apiKey` | string                      |         | the API key to delete  |
| `$opts`   | null\|array\|RequestOptions | null    |                        |

**Example:**

```php
$client->accounts->deleteApiKey(12345, 'api_key_abc123');
```

<hr>

## Account Resource

`Rawilk\Printing\Api\PrintNode\Resources\Account`

An Account represents a child account created under an Integrator account. Child accounts allow you to manage separate PrintNode accounts for your customers while maintaining control through your Integrator account.

### Properties

<hr>

#### id

_int_

The account's ID.

<hr>

#### firstname

_string_

The account holder's first name (deprecated, use creatorRef instead).

<hr>

#### lastname

_string_

The account holder's last name (deprecated, use creatorRef instead).

<hr>

#### email

_string_

The account holder's email address.

<hr>

#### creatorEmail

_string_

The email address of the integrator account that created this account.

<hr>

#### creatorRef

_?string_

The creation reference set when the account was created (your unique identifier).

<hr>

#### createTimestamp

_string_

Time and date the account was created.

<hr>

#### ApiKeys

_array_

A collection of all the API keys set on this account.

<hr>

#### Tags

_array_

A collection of tags set on this account.

<hr>

#### state

_string_

The status of the account (e.g., 'active', 'suspended').

<hr>

#### credits

_?int_

The number of print credits remaining on this account.

<hr>

#### numComputers

_int_

The number of computers active on this account.

<hr>

#### totalPrints

_int_

Total number of prints made on this account.

<hr>

### Methods

<hr>

#### isActive

_bool_

Indicates if the account is considered active.

```php
if ($account->isActive()) {
    // Account is active
}
```

<hr>

#### isSuspended

_bool_

Indicates if the account is suspended.

```php
if ($account->isSuspended()) {
    // Account is suspended
}
```

<hr>

#### createdAt

_?CarbonInterface_

A date object representing the time and date the account was created.

```php
$createdAt = $account->createdAt();
```

<hr>

## Usage Example

Here's a complete example of managing child accounts:

```php
use Rawilk\Printing\Api\PrintNode\PrintNodeClient;

// Initialize the client with your Integrator account API key
$client = new PrintNodeClient(['api_key' => 'your-integrator-api-key']);

// Create a new child account
$newAccount = $client->accounts->create([
    'Account' => [
        'firstname' => '-',
        'lastname' => '-',
        'email' => 'customer@example.com',
        'password' => 'securepassword123',
        'creatorRef' => 'customer_unique_id',
    ],
    'ApiKeys' => ['production'],
    'Tags' => [
        'plan' => 'premium',
        'customer_id' => '12345',
    ],
]);

// Add credits to the account
$client->accounts->addCredits($newAccount->id, 1000);

// List all child accounts
$accounts = $client->accounts->all();

foreach ($accounts as $account) {
    echo "Account: {$account->email} - Credits: {$account->credits}\n";
    
    if ($account->isActive()) {
        echo "Status: Active\n";
    }
}

// Suspend an account if needed
$client->accounts->suspend($newAccount->id);

// Activate it again
$client->accounts->activate($newAccount->id);

// Delete a tag
$client->accounts->deleteTag($newAccount->id, 'plan');

// Delete the account when no longer needed
$client->accounts->delete($newAccount->id);
```