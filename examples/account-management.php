<?php

use Rawilk\Printing\Facades\Printing;

// Example: Managing PrintNode Integrator and Child Accounts

// Get the PrintNode client
$client = Printing::driver('printnode')->client();

// Check if current account is an integrator account
$whoami = $client->whoami->check();

if ($whoami->canCreateSubAccounts()) {
    echo "This is an integrator account!\n";
    echo "Current child accounts: " . count($whoami->childAccounts) . "\n";
} else {
    echo "This account cannot create sub-accounts. Upgrade to an integrator account.\n";
    exit;
}

// Create a new child account
try {
    $newAccount = $client->accounts->create([
        'Account' => [
            'email' => 'customer@example.com',
            'password' => 'securepassword123',
            'creatorRef' => 'customer-' . uniqid(),
        ],
        'ApiKeys' => ['development', 'production'],
        'Tags' => [
            'customerType' => 'premium',
            'region' => 'us-west',
            'createdAt' => date('Y-m-d H:i:s'),
        ],
    ]);

    echo "Created new child account:\n";
    echo "- ID: {$newAccount->id}\n";
    echo "- Email: {$newAccount->email}\n";
    echo "- Creator Ref: {$newAccount->getCreatorRef()}\n";
    echo "- State: {$newAccount->state}\n";
    echo "- API Keys: " . count($newAccount->getApiKeys()) . "\n";
    
    $accountId = $newAccount->id;

} catch (Exception $e) {
    echo "Failed to create account: " . $e->getMessage() . "\n";
    exit;
}

// List all child accounts
echo "\n--- All Child Accounts ---\n";
$childAccounts = $client->accounts->all();

foreach ($childAccounts as $account) {
    echo "Account {$account->id}: {$account->email} ({$account->state})\n";
    
    if ($account->getCreatorRef()) {
        echo "  Creator Ref: {$account->getCreatorRef()}\n";
    }
    
    $tags = $account->getTags();
    if (!empty($tags)) {
        echo "  Tags: " . json_encode($tags) . "\n";
    }
}

// Update the account we just created
echo "\n--- Updating Account ---\n";
try {
    $updatedAccount = $client->accounts->update($accountId, [
        'Tags' => [
            'customerType' => 'enterprise', // Upgrade customer
            'region' => 'us-west',
            'lastUpdated' => date('Y-m-d H:i:s'),
        ],
    ]);
    
    echo "Updated account tags\n";
    echo "New tags: " . json_encode($updatedAccount->getTags()) . "\n";
    
} catch (Exception $e) {
    echo "Failed to update account: " . $e->getMessage() . "\n";
}

// Demonstrate account management operations
echo "\n--- Account Management Operations ---\n";

// Add additional tags
try {
    $client->accounts->addTags($accountId, [
        'priority' => 'high',
        'support' => '24/7',
    ]);
    echo "Added additional tags\n";
} catch (Exception $e) {
    echo "Failed to add tags: " . $e->getMessage() . "\n";
}

// Generate new API keys
try {
    $accountWithNewKeys = $client->accounts->generateApiKeys($accountId, ['staging', 'testing']);
    echo "Generated new API keys: staging, testing\n";
    echo "Total API keys: " . count($accountWithNewKeys->getApiKeys()) . "\n";
} catch (Exception $e) {
    echo "Failed to generate API keys: " . $e->getMessage() . "\n";
}

// Suspend and reactivate account (be careful with this!)
echo "\n--- Suspend/Activate Demo ---\n";
try {
    // Suspend account
    $suspendedAccount = $client->accounts->suspend($accountId);
    echo "Account suspended: {$suspendedAccount->state}\n";
    
    // Reactivate account
    $activeAccount = $client->accounts->activate($accountId);
    echo "Account reactivated: {$activeAccount->state}\n";
    
} catch (Exception $e) {
    echo "Failed to suspend/activate account: " . $e->getMessage() . "\n";
}

// Retrieve specific account details
echo "\n--- Account Details ---\n";
try {
    $accountDetails = $client->accounts->retrieve($accountId);
    
    echo "Account Details:\n";
    echo "- ID: {$accountDetails->id}\n";
    echo "- Email: {$accountDetails->email}\n";
    echo "- State: {$accountDetails->state}\n";
    echo "- Active: " . ($accountDetails->isActive() ? 'Yes' : 'No') . "\n";
    echo "- Credits: {$accountDetails->credits}\n";
    echo "- Total Prints: {$accountDetails->totalPrints}\n";
    echo "- Computers: {$accountDetails->numComputers}\n";
    echo "- Can Create Sub-Accounts: " . ($accountDetails->canCreateSubAccounts() ? 'Yes' : 'No') . "\n";
    
    if ($accountDetails->hasChildAccounts()) {
        echo "- Child Accounts: " . count($accountDetails->getChildAccounts()) . "\n";
    }
    
} catch (Exception $e) {
    echo "Failed to retrieve account: " . $e->getMessage() . "\n";
}

// Download detailed account data
echo "\n--- Download Account Data ---\n";
try {
    $detailedAccounts = $client->accounts->download();
    echo "Downloaded detailed data for " . $detailedAccounts->count() . " accounts\n";
} catch (Exception $e) {
    echo "Failed to download account data: " . $e->getMessage() . "\n";
}

// Clean up - delete the test account (optional)
$deleteAccount = false; // Set to true to actually delete

if ($deleteAccount) {
    echo "\n--- Cleanup ---\n";
    try {
        $client->accounts->delete($accountId);
        echo "Deleted test account\n";
    } catch (Exception $e) {
        echo "Failed to delete account: " . $e->getMessage() . "\n";
    }
}

echo "\n--- Example Complete ---\n";
echo "Account management features are now available!\n";