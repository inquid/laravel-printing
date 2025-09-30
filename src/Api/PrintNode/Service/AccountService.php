<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Service;

use Illuminate\Support\Collection;
use Rawilk\Printing\Api\PrintNode\Entity\Account;
use Rawilk\Printing\Api\PrintNode\Requests\AccountRequest;

/**
 * Service for managing PrintNode Integrator accounts and child accounts.
 * 
 * This service provides functionality for creating, managing, and deleting
 * child accounts under an Integrator account.
 */
class AccountService extends AbstractService
{
    /**
     * Create a new child account under the current Integrator account.
     *
     * @param AccountRequest|array $data
     * @return Account
     */
    public function create(AccountRequest|array $data): Account
    {
        if (is_array($data)) {
            $data = new AccountRequest($data);
        }

        $response = $this->client->request(
            'POST',
            '/account',
            $data->toArray(),
            [],
            Account::class
        );

        return $response;
    }

    /**
     * Get information about a specific child account.
     *
     * @param int $accountId
     * @return Account
     */
    public function get(int $accountId): Account
    {
        return $this->client->request(
            'GET',
            "/account/{$accountId}",
            [],
            [],
            Account::class
        );
    }

    /**
     * Get all child accounts for the current Integrator account.
     *
     * @return Collection<Account>
     */
    public function all(): Collection
    {
        return $this->client->requestCollection(
            'GET',
            '/account',
            [],
            [],
            Account::class
        );
    }

    /**
     * Update a child account.
     *
     * @param int $accountId
     * @param array $data
     * @return Account
     */
    public function update(int $accountId, array $data): Account
    {
        return $this->client->request(
            'PATCH',
            "/account/{$accountId}",
            $data,
            [],
            Account::class
        );
    }

    /**
     * Delete a child account.
     *
     * @param int $accountId
     * @return bool
     */
    public function delete(int $accountId): bool
    {
        $response = $this->client->request(
            'DELETE',
            "/account/{$accountId}"
        );

        return $response === true || $response === null;
    }

    /**
     * Suspend a child account.
     *
     * @param int $accountId
     * @return Account
     */
    public function suspend(int $accountId): Account
    {
        return $this->update($accountId, ['suspended' => true]);
    }

    /**
     * Activate a suspended child account.
     *
     * @param int $accountId
     * @return Account
     */
    public function activate(int $accountId): Account
    {
        return $this->update($accountId, ['suspended' => false]);
    }

    /**
     * Get account statistics.
     *
     * @param int|null $accountId If null, gets stats for all child accounts
     * @return array
     */
    public function stats(?int $accountId = null): array
    {
        $path = $accountId ? "/account/{$accountId}/stats" : '/account/stats';

        return $this->client->request(
            'GET',
            $path
        );
    }

    /**
     * Download account data.
     *
     * @param int $accountId
     * @return array
     */
    public function download(int $accountId): array
    {
        return $this->client->request(
            'GET',
            "/account/{$accountId}/download"
        );
    }

    /**
     * Get API keys for a child account.
     *
     * @param int $accountId
     * @return Collection
     */
    public function apiKeys(int $accountId): Collection
    {
        return $this->client->requestCollection(
            'GET',
            "/account/{$accountId}/apikeys"
        );
    }

    /**
     * Create a new API key for a child account.
     *
     * @param int $accountId
     * @param string $description
     * @return array
     */
    public function createApiKey(int $accountId, string $description): array
    {
        return $this->client->request(
            'POST',
            "/account/{$accountId}/apikey",
            ['description' => $description]
        );
    }

    /**
     * Delete an API key from a child account.
     *
     * @param int $accountId
     * @param string $apiKey
     * @return bool
     */
    public function deleteApiKey(int $accountId, string $apiKey): bool
    {
        $response = $this->client->request(
            'DELETE',
            "/account/{$accountId}/apikey/{$apiKey}"
        );

        return $response === true || $response === null;
    }

    /**
     * Get tags for a child account.
     *
     * @param int $accountId
     * @return array
     */
    public function tags(int $accountId): array
    {
        return $this->client->request(
            'GET',
            "/account/{$accountId}/tags"
        );
    }

    /**
     * Update tags for a child account.
     *
     * @param int $accountId
     * @param array $tags
     * @return array
     */
    public function updateTags(int $accountId, array $tags): array
    {
        return $this->client->request(
            'PATCH',
            "/account/{$accountId}/tags",
            $tags
        );
    }

    /**
     * Delete a tag from a child account.
     *
     * @param int $accountId
     * @param string $tagName
     * @return bool
     */
    public function deleteTag(int $accountId, string $tagName): bool
    {
        $response = $this->client->request(
            'DELETE',
            "/account/{$accountId}/tag/{$tagName}"
        );

        return $response === true || $response === null;
    }

    /**
     * Perform actions on behalf of a child account.
     * This sets the X-Child-Account-By-Id header for subsequent requests.
     *
     * @param int $accountId
     * @return static
     */
    public function actAsChildAccount(int $accountId): static
    {
        $this->client->setDefaultHeader('X-Child-Account-By-Id', (string) $accountId);
        
        return $this;
    }

    /**
     * Perform actions on behalf of a child account using creator reference.
     * This sets the X-Child-Account-By-CreatorRef header for subsequent requests.
     *
     * @param string $creatorRef
     * @return static
     */
    public function actAsChildAccountByRef(string $creatorRef): static
    {
        $this->client->setDefaultHeader('X-Child-Account-By-CreatorRef', $creatorRef);
        
        return $this;
    }

    /**
     * Stop acting on behalf of a child account.
     *
     * @return static
     */
    public function stopActingAsChildAccount(): static
    {
        $this->client->removeDefaultHeader('X-Child-Account-By-Id');
        $this->client->removeDefaultHeader('X-Child-Account-By-CreatorRef');
        
        return $this;
    }
}