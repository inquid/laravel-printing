# Integrator Accounts

Integrator accounts allow you to create and manage child accounts for your customers or end-users. This feature is particularly useful for SaaS applications that need to provide isolated printing capabilities to their customers.

## Prerequisites

Before using integrator accounts, you need to:

1. Sign up for a PrintNode account
2. Upgrade your account to an Integrator Account through the PrintNode dashboard
3. Obtain your Integrator Account API key

## Basic Usage

### Creating a Child Account

You can create child accounts programmatically using the `accounts` service:

```php
use Rawilk\Printing\Facades\Printing;
use Rawilk\Printing\Api\PrintNode\Requests\AccountRequest;

$client = Printing::driver('printnode')->getClient();

// Create a child account using an array
$account = $client->accounts->create([
    'email' => 'customer@example.com',
    'password' => 'securepassword123',
    'creatorRef' => 'customer-123',  // Your internal customer ID
    'apiKeys' => ['development', 'production'],  // Auto-generate API keys
    'tags' => [
        'plan' => 'premium',
        'region' => 'us-west'
    ]
]);

// Or use the AccountRequest builder for a more fluent interface
$request = AccountRequest::make()
    ->email('customer@example.com')
    ->password('securepassword123')
    ->creatorRef('customer-123')
    ->apiKeys(['development', 'production'])
    ->addTag('plan', 'premium')
    ->addTag('region', 'us-west');

$account = $client->accounts->create($request);
```

### Managing Child Accounts

#### Listing All Child Accounts

```php
$accounts = $client->accounts->all();

foreach ($accounts as $account) {
    echo "Account ID: {$account->id}, Email: {$account->email}\n";
    echo "Status: " . ($account->isActive() ? 'Active' : 'Suspended') . "\n";
}
```

#### Getting a Specific Account

```php
$accountId = 12345;
$account = $client->accounts->get($accountId);

// Check account status
if ($account->isSuspended()) {
    echo "Account is suspended\n";
}

// Get account statistics
$stats = $account->getStatsSummary();
echo "Printers: {$stats['printers']}, Print Jobs: {$stats['print_jobs']}\n";
```

#### Updating Account Information

```php
$accountId = 12345;
$account = $client->accounts->update($accountId, [
    'email' => 'newemail@example.com'
]);
```

#### Suspending and Activating Accounts

```php
// Suspend an account
$account = $client->accounts->suspend($accountId);

// Activate a suspended account
$account = $client->accounts->activate($accountId);
```

#### Deleting an Account

```php
$success = $client->accounts->delete($accountId);
```

### Managing API Keys

#### Listing API Keys

```php
$apiKeys = $client->accounts->apiKeys($accountId);

foreach ($apiKeys as $apiKey) {
    echo "Key: {$apiKey['key']}, Description: {$apiKey['description']}\n";
}
```

#### Creating a New API Key

```php
$newKey = $client->accounts->createApiKey($accountId, 'Staging Environment');
echo "New API Key: {$newKey['key']}\n";
```

#### Deleting an API Key

```php
$success = $client->accounts->deleteApiKey($accountId, 'api-key-to-delete');
```

### Managing Tags

Tags allow you to store metadata about child accounts, such as subscription plans, features, or custom identifiers.

#### Getting Tags

```php
$tags = $client->accounts->tags($accountId);
echo "Plan: {$tags['plan']}\n";
```

#### Updating Tags

```php
$tags = $client->accounts->updateTags($accountId, [
    'plan' => 'enterprise',
    'support' => '24/7',
    'custom_field' => 'value'
]);
```

#### Deleting a Tag

```php
$success = $client->accounts->deleteTag($accountId, 'obsolete-tag');
```

### Account Statistics

Get detailed statistics about child accounts:

```php
// Statistics for a specific account
$stats = $client->accounts->stats($accountId);

// Statistics for all child accounts
$allStats = $client->accounts->stats();
```

### Downloading Account Data

Download all data associated with a child account:

```php
$data = $client->accounts->download($accountId);

// The download includes:
// - Account information
// - Computers
// - Printers
// - Print jobs
// - API keys
// - Tags
```

## Acting on Behalf of Child Accounts

One of the most powerful features of integrator accounts is the ability to perform actions on behalf of child accounts. This allows you to manage printers, submit print jobs, and perform other operations as if you were the child account.

### Using Account ID

```php
// Start acting as a child account
$client->accounts->actAsChildAccount($accountId);

// Now all subsequent API calls will be made on behalf of the child account
$printers = $client->printers->all();  // Gets the child account's printers
$printJob = $client->printJobs->create(...);  // Creates a print job for the child account

// Stop acting as the child account
$client->accounts->stopActingAsChildAccount();
```

### Using Creator Reference

If you prefer to use your own internal customer IDs:

```php
// Act as a child account using creator reference
$client->accounts->actAsChildAccountByRef('customer-123');

// Perform operations...
$computers = $client->computers->all();

// Stop acting as the child account
$client->accounts->stopActingAsChildAccount();
```

### Chaining Operations

You can chain the acting methods for a more fluent interface:

```php
$printers = $client->accounts
    ->actAsChildAccount($accountId)
    ->printers
    ->all();

// Don't forget to stop acting when done
$client->accounts->stopActingAsChildAccount();
```

## Working with the Account Entity

The `Account` entity provides several helper methods:

```php
$account = $client->accounts->get($accountId);

// Check account status
$isActive = $account->isActive();
$isSuspended = $account->isSuspended();

// Check account type
$isIntegrator = $account->isIntegrator();
$isChildAccount = $account->isChildAccount();

// Check sub-account capabilities
if ($account->canCreateSubAccounts()) {
    $remaining = $account->remainingSubAccounts();
    echo "Can create {$remaining} more sub-accounts\n";
}

// Work with API keys
if ($account->hasApiKeys()) {
    $prodKey = $account->getApiKey('Production');
    if ($prodKey) {
        echo "Production key: {$prodKey['key']}\n";
    }
}

// Work with tags
if ($account->hasTag('plan')) {
    $plan = $account->getTag('plan');
    echo "Current plan: {$plan}\n";
}

// Get statistics summary
$stats = $account->getStatsSummary();
```

## Best Practices

### 1. Use Creator References

Always set a `creatorRef` when creating child accounts. This allows you to link PrintNode accounts with your internal customer records:

```php
$account = $client->accounts->create([
    'email' => 'customer@example.com',
    'password' => 'securepassword',
    'creatorRef' => "customer-{$customerId}",  // Your internal ID
]);
```

### 2. Generate API Keys Automatically

Instead of sharing your integrator API key, generate unique API keys for each child account:

```php
$account = $client->accounts->create([
    // ... other fields
    'apiKeys' => ['default', 'backup'],  // Auto-generate keys
]);

// Or create them later
$apiKey = $client->accounts->createApiKey($account->id, 'production');
```

### 3. Use Tags for Metadata

Tags are perfect for storing subscription information, feature flags, or other metadata:

```php
$client->accounts->updateTags($accountId, [
    'subscription_plan' => 'premium',
    'subscription_expires' => '2024-12-31',
    'features' => json_encode(['advanced_printing', 'priority_support']),
    'monthly_limit' => 1000,
]);
```

### 4. Handle Account Limits

Check if you can create more child accounts before attempting to create one:

```php
$integratorAccount = $client->whoami->get();
if ($integratorAccount->remainingSubAccounts() > 0) {
    // Safe to create a new child account
    $account = $client->accounts->create(...);
} else {
    // Handle limit reached
    throw new Exception('Child account limit reached');
}
```

### 5. Clean Up When Acting as Child

Always stop acting as a child account when you're done:

```php
try {
    $client->accounts->actAsChildAccount($accountId);
    
    // Perform operations...
    
} finally {
    // Ensure we stop acting as the child account
    $client->accounts->stopActingAsChildAccount();
}
```

### 6. Monitor Account Activity

Regularly check account statistics to monitor usage:

```php
$stats = $client->accounts->stats($accountId);

if ($stats['print_jobs'] > 1000) {
    // Maybe upgrade their plan or notify them
}
```

## Error Handling

When working with integrator accounts, you should handle common errors:

```php
use Rawilk\Printing\Api\PrintNode\Exceptions\AuthenticationFailure;
use Rawilk\Printing\Api\PrintNode\Exceptions\UnexpectedValue;

try {
    $account = $client->accounts->create([
        'email' => 'customer@example.com',
        'password' => 'pass',  // Too short
    ]);
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    echo "Validation error: {$e->getMessage()}\n";
} catch (AuthenticationFailure $e) {
    // Handle authentication issues
    echo "Authentication failed: {$e->getMessage()}\n";
} catch (UnexpectedValue $e) {
    // Handle API response errors
    echo "API error: {$e->getMessage()}\n";
}
```

## Complete Example

Here's a complete example of creating and managing a child account:

```php
use Rawilk\Printing\Facades\Printing;
use Rawilk\Printing\Api\PrintNode\Requests\AccountRequest;

// Initialize the client
$client = Printing::driver('printnode')->getClient();

// Create a child account for a new customer
$request = AccountRequest::make()
    ->email('customer@example.com')
    ->password('securePassword123!')
    ->creatorRef('customer-456')
    ->apiKeys(['production'])
    ->addTag('plan', 'premium')
    ->addTag('created_at', date('Y-m-d'));

$account = $client->accounts->create($request);

echo "Created account ID: {$account->id}\n";

// Get the generated API key
$apiKeys = $client->accounts->apiKeys($account->id);
$productionKey = $apiKeys->firstWhere('description', 'production');

echo "Production API Key: {$productionKey['key']}\n";

// Act as the child account to set up their printers
$client->accounts->actAsChildAccount($account->id);

// Get available computers (the customer needs to install PrintNode client)
$computers = $client->computers->all();

if ($computers->isNotEmpty()) {
    // Get printers from the first computer
    $printers = $client->printers->all();
    
    foreach ($printers as $printer) {
        echo "Found printer: {$printer->name}\n";
        
        // Create a test print job
        $printJob = $printer->print(
            content: 'Test page from integrator account',
            title: 'Test Print'
        );
        
        echo "Created print job: {$printJob->id}\n";
    }
}

// Stop acting as the child account
$client->accounts->stopActingAsChildAccount();

// Later, check account statistics
$stats = $client->accounts->stats($account->id);
echo "Total print jobs: {$stats['print_jobs']}\n";

// Update account tags based on usage
if ($stats['print_jobs'] > 100) {
    $client->accounts->updateTags($account->id, [
        'usage_tier' => 'high',
    ]);
}

// Suspend account if needed (e.g., payment failed)
if ($paymentFailed) {
    $client->accounts->suspend($account->id);
    echo "Account suspended due to payment failure\n";
}

// Reactivate when payment is resolved
if ($paymentResolved) {
    $client->accounts->activate($account->id);
    echo "Account reactivated\n";
}
```

## Additional Resources

- [PrintNode API Documentation](https://www.printnode.com/en/docs/api/curl#account-download-management)
- [PrintNode Pricing](https://www.printnode.com/en/pricing) - Information about Integrator Account pricing
- [PrintNode Support](https://www.printnode.com/en/support) - Get help with your Integrator Account