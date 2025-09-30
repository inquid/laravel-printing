<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Service;

use Illuminate\Support\Collection;
use Rawilk\Printing\Api\PrintNode\Resources\Account;
use Rawilk\Printing\Api\PrintNode\Util\RequestOptions;

class AccountService extends AbstractService
{
    /**
     * Create a new child account.
     *
     * @param array $params Account creation parameters including:
     *                      - email (required): The contact email address
     *                      - password (required): Password of at least 8 characters
     *                      - firstname (optional): Deprecated, defaults to "-"
     *                      - lastname (optional): Deprecated, defaults to "-"
     *                      - creatorRef (optional): Unique reference to identify the account
     *                      - ApiKeys (optional): Array of API key names to generate
     *                      - Tags (optional): Object containing tag names and values
     */
    public function create(array $params, null|array|RequestOptions $opts = null): Account
    {
        // Ensure deprecated fields have default values
        $accountData = array_merge([
            'firstname' => '-',
            'lastname' => '-',
        ], $params['Account'] ?? $params);

        // Structure the request properly for PrintNode API
        $requestData = [
            'Account' => $accountData,
        ];

        // Add optional fields if provided
        if (isset($params['ApiKeys'])) {
            $requestData['ApiKeys'] = $params['ApiKeys'];
        }

        if (isset($params['Tags'])) {
            $requestData['Tags'] = $params['Tags'];
        }

        return $this->request('post', '/account', $requestData, $opts, Account::class);
    }

    /**
     * Retrieve a specific account by ID.
     */
    public function retrieve(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('get', $this->buildPath('/account/%s', $id), opts: $opts, expectedResource: Account::class);
    }

    /**
     * Update an existing account.
     *
     * @param int $id The account ID to update
     * @param array $params Update parameters (same structure as create)
     */
    public function update(int $id, array $params, null|array|RequestOptions $opts = null): Account
    {
        // Structure the request properly for PrintNode API
        $requestData = [];

        if (isset($params['Account'])) {
            $requestData['Account'] = $params['Account'];
        }

        if (isset($params['ApiKeys'])) {
            $requestData['ApiKeys'] = $params['ApiKeys'];
        }

        if (isset($params['Tags'])) {
            $requestData['Tags'] = $params['Tags'];
        }

        return $this->request('patch', $this->buildPath('/account/%s', $id), $requestData, $opts, Account::class);
    }

    /**
     * Delete an account.
     */
    public function delete(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('delete', $this->buildPath('/account/%s', $id), opts: $opts, expectedResource: Account::class);
    }

    /**
     * List all child accounts for the current integrator account.
     */
    public function all(null|array|RequestOptions $opts = null): Collection
    {
        return $this->requestCollection('get', '/account', opts: $opts, expectedResource: Account::class);
    }

    /**
     * Download child account data.
     * This method retrieves detailed information about child accounts.
     */
    public function download(null|array|RequestOptions $opts = null): Collection
    {
        return $this->requestCollection('get', '/download/accounts', opts: $opts, expectedResource: Account::class);
    }

    /**
     * Suspend an account.
     */
    public function suspend(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['Account' => ['state' => 'suspended']], $opts, Account::class);
    }

    /**
     * Activate a suspended account.
     */
    public function activate(int $id, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['Account' => ['state' => 'active']], $opts, Account::class);
    }

    /**
     * Add tags to an account.
     */
    public function addTags(int $id, array $tags, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['Tags' => $tags], $opts, Account::class);
    }

    /**
     * Generate new API keys for an account.
     */
    public function generateApiKeys(int $id, array $keyNames, null|array|RequestOptions $opts = null): Account
    {
        return $this->request('patch', $this->buildPath('/account/%s', $id), ['ApiKeys' => $keyNames], $opts, Account::class);
    }
}