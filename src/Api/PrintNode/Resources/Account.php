<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Resources;

use Rawilk\Printing\Api\PrintNode\PrintNodeApiResource;

/**
 * The `Account` object represents a PrintNode account, typically used
 * for managing child accounts in an integrator setup.
 *
 * @property int $id The account's ID
 * @property string $firstname The account holder's first name (deprecated, usually set to "-")
 * @property string $lastname The account holder's last name (deprecated, usually set to "-")
 * @property string $email The account holder's email address
 * @property bool $canCreateSubAccounts Determines if this account can create sub-accounts
 * @property null|string $creatorEmail The email address of the account that created this sub-account
 * @property null|string $creatorRef The creation reference set when the account was created
 * @property array $childAccounts Any child accounts present on this account
 * @property int|null $credits The number of print credits remaining on this account
 * @property int $numComputers The number of computers active on this account
 * @property int $totalPrints Total number of prints made on this account
 * @property array $versions A collection of versions set on this account
 * @property array $connected A collection of computer IDs signed in on this account
 * @property array $Tags A collection of tags set on this account
 * @property array $ApiKeys A collection of all the api keys set on this account
 * @property string $state The status of the account (active, suspended, etc.)
 * @property array $permissions The permissions set on this account
 * @property string $password Write-only property for setting account password during creation
 */
class Account extends PrintNodeApiResource
{
    public static function classUrl(): string
    {
        return '/account';
    }

    public static function resourceUrl(?int $id = null): string
    {
        if ($id === null) {
            return static::classUrl();
        }

        return static::classUrl() . '/' . $id;
    }

    /**
     * Indicates if the account is considered active.
     */
    public function isActive(): bool
    {
        return $this->_values['state'] === 'active';
    }

    /**
     * Indicates if the account can create sub-accounts (integrator account).
     */
    public function canCreateSubAccounts(): bool
    {
        return $this->_values['canCreateSubAccounts'] ?? false;
    }

    /**
     * Get the child accounts for this account.
     */
    public function getChildAccounts(): array
    {
        return $this->_values['childAccounts'] ?? [];
    }

    /**
     * Check if this account has any child accounts.
     */
    public function hasChildAccounts(): bool
    {
        return !empty($this->getChildAccounts());
    }

    /**
     * Get the creator reference for this account.
     */
    public function getCreatorRef(): ?string
    {
        return $this->_values['creatorRef'] ?? null;
    }

    /**
     * Get the tags associated with this account.
     */
    public function getTags(): array
    {
        return $this->_values['Tags'] ?? [];
    }

    /**
     * Get the API keys associated with this account.
     */
    public function getApiKeys(): array
    {
        return $this->_values['ApiKeys'] ?? [];
    }
}