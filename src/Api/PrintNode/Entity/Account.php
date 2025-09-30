<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Entity;

use Rawilk\Printing\Api\PrintNode\PrintNodeObject;

/**
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property bool $emailVerified
 * @property string $creatorRef
 * @property array $apiKeys
 * @property array $tags
 * @property string $state
 * @property array $permissions
 * @property string $createdAt
 * @property string $updatedAt
 * @property int|null $childAccounts
 * @property int|null $totalPrints
 * @property int|null $versions
 * @property int|null $connectedClients
 * @property string|null $credits
 * @property int|null $numComputers
 * @property int|null $numPrinters
 * @property int|null $numPrintJobs
 * @property bool $canCreateSubAccounts
 * @property int|null $maxSubAccounts
 * @property int|null $creatorEmail
 * @property string|null $creatorRef
 * @property array|null $integrator
 * @property string|null $development
 * @property bool $suspended
 * @property string|null $suspendedAt
 * @property string|null $suspendedBy
 * @property string|null $suspendedReason
 */
class Account extends PrintNodeObject
{
    /**
     * Check if the account is active (not suspended).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ! ($this->suspended ?? false);
    }

    /**
     * Check if the account is suspended.
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->suspended ?? false;
    }

    /**
     * Check if the account can create sub-accounts.
     *
     * @return bool
     */
    public function canCreateSubAccounts(): bool
    {
        return $this->canCreateSubAccounts ?? false;
    }

    /**
     * Get the number of remaining sub-accounts that can be created.
     *
     * @return int|null
     */
    public function remainingSubAccounts(): ?int
    {
        if (! $this->canCreateSubAccounts()) {
            return 0;
        }

        if ($this->maxSubAccounts === null) {
            return null; // unlimited
        }

        $childAccounts = $this->childAccounts ?? 0;

        return max(0, $this->maxSubAccounts - $childAccounts);
    }

    /**
     * Check if the account has API keys.
     *
     * @return bool
     */
    public function hasApiKeys(): bool
    {
        return ! empty($this->apiKeys);
    }

    /**
     * Get a specific API key by description.
     *
     * @param string $description
     * @return array|null
     */
    public function getApiKey(string $description): ?array
    {
        if (! $this->hasApiKeys()) {
            return null;
        }

        foreach ($this->apiKeys as $apiKey) {
            if (($apiKey['description'] ?? '') === $description) {
                return $apiKey;
            }
        }

        return null;
    }

    /**
     * Check if the account has a specific tag.
     *
     * @param string $tagName
     * @return bool
     */
    public function hasTag(string $tagName): bool
    {
        return isset($this->tags[$tagName]);
    }

    /**
     * Get a specific tag value.
     *
     * @param string $tagName
     * @param mixed $default
     * @return mixed
     */
    public function getTag(string $tagName, mixed $default = null): mixed
    {
        return $this->tags[$tagName] ?? $default;
    }

    /**
     * Check if the account is an integrator account.
     *
     * @return bool
     */
    public function isIntegrator(): bool
    {
        return ! empty($this->integrator);
    }

    /**
     * Check if the account is a child account.
     *
     * @return bool
     */
    public function isChildAccount(): bool
    {
        return ! empty($this->creatorEmail) || ! empty($this->creatorRef);
    }

    /**
     * Get account statistics summary.
     *
     * @return array
     */
    public function getStatsSummary(): array
    {
        return [
            'computers' => $this->numComputers ?? 0,
            'printers' => $this->numPrinters ?? 0,
            'print_jobs' => $this->numPrintJobs ?? 0,
            'total_prints' => $this->totalPrints ?? 0,
            'child_accounts' => $this->childAccounts ?? 0,
            'connected_clients' => $this->connectedClients ?? 0,
        ];
    }
}