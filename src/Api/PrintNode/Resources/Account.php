<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Resources;

use Carbon\CarbonInterface;
use Rawilk\Printing\Api\PrintNode\PrintNodeApiResource;

/**
 * An `Account` represents a child account created under an Integrator account.
 * Child accounts allow you to manage separate PrintNode accounts for your
 * customers while maintaining control through your Integrator account.
 *
 * @property-read int $id The account's ID
 * @property-read string $firstname The account holder's first name (deprecated, use creatorRef instead)
 * @property-read string $lastname The account holder's last name (deprecated, use creatorRef instead)
 * @property-read string $email The account holder's email address
 * @property-read string $creatorEmail The email address of the integrator account that created this account
 * @property-read null|string $creatorRef The creation reference set when the account was created (your unique identifier)
 * @property-read string $createTimestamp Time and date the account was created
 * @property-read array $ApiKeys A collection of all the API keys set on this account
 * @property-read array $Tags A collection of tags set on this account
 * @property-read string $state The status of the account (e.g. 'active', 'suspended')
 * @property-read int|null $credits The number of print credits remaining on this account
 * @property-read int $numComputers The number of computers active on this account
 * @property-read int $totalPrints Total number of prints made on this account
 */
class Account extends PrintNodeApiResource
{
    use ApiOperations\Request;
    use Concerns\HasDateAttributes;

    public static function classUrl(): string
    {
        return '/account';
    }

    public static function resourceUrl(?int $id = null): string
    {
        if ($id !== null) {
            return static::classUrl() . '/' . $id;
        }

        return static::classUrl();
    }

    /**
     * Indicates if the account is considered active.
     */
    public function isActive(): bool
    {
        return $this->_values['state'] === 'active';
    }

    /**
     * Indicates if the account is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->_values['state'] === 'suspended';
    }

    /**
     * Get the account creation timestamp as a Carbon instance.
     */
    public function createdAt(): ?CarbonInterface
    {
        return $this->parseDate($this->createTimestamp);
    }
}