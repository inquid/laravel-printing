<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Service;

use Rawilk\Printing\Api\PrintNode\Resources\Whoami;
use Rawilk\Printing\Api\PrintNode\Util\RequestOptions;

class AccountService extends AbstractService
{
    /**
     * Create a child account (Integrator accounts only).
     *
     * @param  array{Account: array<string, mixed>, ApiKeys?: array<int, string>, Tags?: array<string, mixed>}  $params
     */
    public function create(array $params, null|array|RequestOptions $opts = null): Whoami
    {
        return $this->request('post', '/account', $params, opts: $opts, expectedResource: Whoami::class);
    }

    /**
     * Update a child account's profile (target via child account headers in $opts).
     */
    public function update(array $params, null|array|RequestOptions $opts = null): Whoami
    {
        return $this->request('patch', '/account', $params, opts: $opts, expectedResource: Whoami::class);
    }

    /**
     * Set account state for a child account. Send body as a JSON string: "active" or "suspended".
     */
    public function setState(string $state, null|array|RequestOptions $opts = null): array
    {
        // The API expects a raw JSON string body, e.g., "active" or "suspended"
        return $this->request('put', '/account/state', json_encode($state, JSON_THROW_ON_ERROR), opts: $opts);
    }

    /**
     * Delete a child account. Target via child account headers in $opts.
     * Returns an array of affected IDs per API conventions.
     */
    public function delete(null|array|RequestOptions $opts = null): array
    {
        return $this->request('delete', '/account', [], opts: $opts);
    }
}

