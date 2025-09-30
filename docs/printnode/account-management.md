# Account Management

PrintNode offers Integrator Accounts, enabling you to programmatically create and manage separate PrintNode accounts for your customers, referred to as Child Accounts. Each Child Account operates independently with its own credentials but remains under the control of the Integrator Account that created it.

## Prerequisites

To utilize account management features, you need to upgrade your existing PrintNode account to an Integrator Account. This upgrade can be initiated through the [PrintNode web application](https://api.printnode.com/app/integrators/upgrade).

## Basic Usage

### Creating a Child Account

```php
use Rawilk\Printing\Facades\Printing;

$client = Printing::driver('printnode')->client();

$childAccount = $client->accounts->create([
    'Account' => [
        'email' => 'customer@example.com',
        'password' => 'securepassword123',
        'creatorRef' => 'unique-customer-reference',
    ],
    'ApiKeys' => ['development', 'production'],
    'Tags' => [
        'customerType' => 'premium',
        'region' => 'us-west',
    ],
]);

echo "Created child account with ID: " . $childAccount->id;
```

### Retrieving an Account

```php
$accountId = 12345;
$account = $client->accounts->retrieve($accountId);

echo "Account email: " . $account->email;
echo "Account state: " . $account->state;
echo "Can create sub-accounts: " . ($account->canCreateSubAccounts() ? 'Yes' : 'No');
```

### Updating an Account

```php
$accountId = 12345;
$updatedAccount = $client->accounts->update($accountId, [
    'Account' => [
        'email' => 'newemail@example.com',
    ],
    'Tags' => [
        'customerType' => 'enterprise',
        'priority' => 'high',
    ],
]);
```

### Listing Child Accounts

```php
// Get all child accounts
$childAccounts = $client->accounts->all();

foreach ($childAccounts as $account) {
    echo "Account ID: {$account->id}, Email: {$account->email}\n";
}

// Download detailed account data
$detailedAccounts = $client->accounts->download();
```

### Account Management Operations

#### Suspending an Account

```php
$accountId = 12345;
$suspendedAccount = $client->accounts->suspend($accountId);

echo "Account state: " . $suspendedAccount->state; // 'suspended'
```

#### Activating an Account

```php
$accountId = 12345;
$activeAccount = $client->accounts->activate($accountId);

echo "Account state: " . $activeAccount->state; // 'active'
```

#### Adding Tags

```php
$accountId = 12345;
$account = $client->accounts->addTags($accountId, [
    'newTag' => 'newValue',
    'category' => 'special',
]);
```

#### Generating API Keys

```php
$accountId = 12345;
$account = $client->accounts->generateApiKeys($accountId, ['staging', 'testing']);

// Access the generated API keys
$apiKeys = $account->getApiKeys();
foreach ($apiKeys as $key) {
    echo "Key name: {$key['name']}, Key: {$key['key']}\n";
}
```

#### Deleting an Account

```php
$accountId = 12345;
$deletedAccount = $client->accounts->delete($accountId);
```

## Account Resource Properties

The `Account` resource provides access to the following properties:

- `id`: The account's unique identifier
- `firstname`: Account holder's first name (deprecated, usually "-")
- `lastname`: Account holder's last name (deprecated, usually "-") 
- `email`: Account holder's email address
- `canCreateSubAccounts`: Whether this account can create sub-accounts
- `creatorEmail`: Email of the account that created this sub-account
- `creatorRef`: Creation reference set when the account was created
- `childAccounts`: Array of child accounts (for integrator accounts)
- `credits`: Number of print credits remaining
- `numComputers`: Number of active computers
- `totalPrints`: Total number of prints made
- `Tags`: Collection of tags set on the account
- `ApiKeys`: Collection of API keys for the account
- `state`: Account status ('active', 'suspended', etc.)
- `permissions`: Account permissions array

## Helper Methods

The `Account` resource provides several helper methods:

```php
// Check if account is active
if ($account->isActive()) {
    echo "Account is active";
}

// Check if account can create sub-accounts (integrator account)
if ($account->canCreateSubAccounts()) {
    echo "This is an integrator account";
}

// Get child accounts
$children = $account->getChildAccounts();

// Check if account has child accounts
if ($account->hasChildAccounts()) {
    echo "This account has " . count($account->getChildAccounts()) . " child accounts";
}

// Get creator reference
$creatorRef = $account->getCreatorRef();

// Get tags
$tags = $account->getTags();

// Get API keys
$apiKeys = $account->getApiKeys();
```

## Account Creation Parameters

When creating a child account, you can specify the following parameters:

### Required Parameters (Account object)
- `email`: Contact email address for the customer
- `password`: Password of at least 8 characters

### Optional Parameters (Account object)
- `firstname`: Deprecated field, defaults to "-"
- `lastname`: Deprecated field, defaults to "-"
- `creatorRef`: Unique reference to identify the account

### Optional Top-level Parameters
- `ApiKeys`: Array of API key names to generate upon creation
- `Tags`: Object containing tag names and corresponding values

## Error Handling

Account operations may throw exceptions for various reasons:

```php
use Rawilk\Printing\Api\PrintNode\Exceptions\PrintNodeApiRequestFailed;
use Rawilk\Printing\Api\PrintNode\Exceptions\AuthenticationFailure;

try {
    $account = $client->accounts->create([
        'Account' => [
            'email' => 'customer@example.com',
            'password' => 'weak', // Too short
        ],
    ]);
} catch (PrintNodeApiRequestFailed $e) {
    echo "Account creation failed: " . $e->getMessage();
} catch (AuthenticationFailure $e) {
    echo "Authentication failed - check your API key";
}
```

## Best Practices

1. **Use meaningful creator references**: Set unique `creatorRef` values to easily identify and manage child accounts
2. **Implement proper error handling**: Always wrap account operations in try-catch blocks
3. **Use tags for organization**: Leverage tags to categorize and organize child accounts
4. **Manage API keys securely**: Generate separate API keys for different environments (development, production)
5. **Monitor account states**: Regularly check account states and handle suspended accounts appropriately
6. **Implement proper authentication**: Ensure your integrator API key is kept secure and has appropriate permissions

## Integration with Whoami

The existing `Whoami` service will show integrator account information including child accounts:

```php
$whoami = $client->whoami->check();

if ($whoami->canCreateSubAccounts) {
    echo "This is an integrator account with " . count($whoami->childAccounts) . " child accounts";
    
    foreach ($whoami->childAccounts as $child) {
        echo "Child account: {$child['email']} (ID: {$child['id']})\n";
    }
}
```