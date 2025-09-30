<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Service;

use Illuminate\Support\Collection;
use Rawilk\Printing\Api\PrintNode\Resources\Account;
use Rawilk\Printing\Api\PrintNode\Util\RequestOptions;

class AccountService extends AbstractService
{
    /**
     * Retrieve all child accounts under the integrator account.
     *
     * @param  null|array  $params
     *                              `limit` => the max number of rows that will be returned - default is 100
     *                              `dir` => `asc` for ascending, `desc` for descending - default is `desc`
     *                              `after` => retrieve records with an ID after the provided value
     * @return Collection<int, Account>
     */
    public function all(?array $params = null, null|array|RequestOptions $opts = null): Collection
    {
        return $this->requestCollection('get', '/account', $params, opts: $opts, expectedResource: Account::class);
    }

    /**
     * Retrieve a specific child account by ID.
     */
    public function retrieve(int $id, ?array $params = null, null|array|RequestOptions $opts = null): ?Account
    {
        $accounts = $this->requestCollection('get', $this->buildPath('/account/%s', $id), $params, opts: $opts, expectedResource: Account::class);

        return $accounts->first();
    }

    /**
     * Create a new child account under the integrator account.
     *
     * @param  array  $params
     *                       `Account[firstname]` => First name (deprecated, use "-" and rely on creatorRef)
     *                       `Account[lastname]` => Last name (deprecated, use "-" and rely on creatorRef)
     *                       `Account[email]` => Contact email address (required)
     *                       `Account[password]` => Password (required, min 8 characters)
     *                       `Account[creatorRef]` => Your unique reference for this account (recommended)
     *                       `ApiKeys[]` => Array of API key names to create (max 10, max 16 bytes each)
     *                       `Tags[]` => Object with tag names as keys and tag values as values (max 1024 bytes per value)
     */
    public function create(array $params, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('post', '/account', $params, opts: $opts, expectedResource: Account::class);
    }

    /**
     * Modify an existing child account.
     *
     * @param  int  $id
     * @param  array  $params
     *                       `Account[email]` => New email address
     *                       `Account[password]` => New password (min 8 characters)
     *                       `Account[creatorRef]` => New creator reference
     *                       `ApiKeys[]` => Array of API key names to create
     *                       `Tags[]` => Object with tag names as keys and tag values as values
     */
    public function modify(int $id, array $params, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), $params, opts: $opts, expectedResource: Account::class);
    }

    /**
     * Delete a child account. Returns an array of affected IDs.
     */
    public function delete(int $id, ?array $params = null, null|array|RequestOptions $opts = null): array
    {
        return $this->request('delete', $this->buildPath('/account/%s', $id), $params, opts: $opts);
    }

    /**
     * Delete multiple child accounts. Returns an array of affected IDs.
     *
     * @param  array  $ids  Array of account IDs to delete
     */
    public function deleteMany(array $ids, ?array $params = null, null|array|RequestOptions $opts = null): array
    {
        return $this->request('delete', $this->buildPath('/account/%s', ...$ids), $params, opts: $opts);
    }

    /**
     * Add credits to a child account.
     *
     * @param  int  $id  The account ID
     * @param  int  $credits  The number of credits to add
     */
    public function addCredits(int $id, int $credits, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['Account[credits]' => $credits], opts: $opts, expectedResource: Account::class);
    }

    /**
     * Set the state of a child account (e.g. suspend or activate).
     *
     * @param  int  $id  The account ID
     * @param  string  $state  The state to set (e.g. 'active', 'suspended')
     */
    public function setState(int $id, string $state, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['Account[state]' => $state], opts: $opts, expectedResource: Account::class);
    }

    /**
     * Suspend a child account.
     */
    public function suspend(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->setState($id, 'suspended', $opts);
    }

    /**
     * Activate a child account.
     */
    public function activate(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->setState($id, 'active', $opts);
    }

    /**
     * Delete a tag from a child account.
     *
     * @param  int  $id  The account ID
     * @param  string  $tagName  The name of the tag to delete
     */
    public function deleteTag(int $id, string $tagName, null|array|RequestOptions $opts = null): array
    {
        return $this->request('delete', $this->buildPath('/account/%s/tag/%s', $id) . '/' . urlencode($tagName), opts: $opts);
    }

    /**
     * Delete an API key from a child account.
     *
     * @param  int  $id  The account ID
     * @param  string  $apiKey  The API key to delete
     */
    public function deleteApiKey(int $id, string $apiKey, null|array|RequestOptions $opts = null): array
    {
        return $this->request('delete', $this->buildPath('/account/%s/apikey/%s', $id) . '/' . urlencode($apiKey), opts: $opts);
    }
}