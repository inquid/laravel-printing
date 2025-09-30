<?php

declare(strict_types=1);

namespace Rawilk\Printing\Api\PrintNode\Requests;

use InvalidArgumentException;

/**
 * Request object for creating PrintNode child accounts.
 */
class AccountRequest
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        if (! empty($data)) {
            $this->setFromArray($data);
        }
    }

    /**
     * Set the first name for the account (deprecated, use "-").
     *
     * @param string $firstname
     * @return static
     */
    public function firstname(string $firstname = '-'): static
    {
        $this->data['Account']['firstname'] = $firstname;

        return $this;
    }

    /**
     * Set the last name for the account (deprecated, use "-").
     *
     * @param string $lastname
     * @return static
     */
    public function lastname(string $lastname = '-'): static
    {
        $this->data['Account']['lastname'] = $lastname;

        return $this;
    }

    /**
     * Set the email address for the account.
     *
     * @param string $email
     * @return static
     */
    public function email(string $email): static
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$email}");
        }

        $this->data['Account']['email'] = $email;

        return $this;
    }

    /**
     * Set the password for the account.
     *
     * @param string $password
     * @return static
     */
    public function password(string $password): static
    {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        $this->data['Account']['password'] = $password;

        return $this;
    }

    /**
     * Set the creator reference for the account.
     *
     * @param string $creatorRef
     * @return static
     */
    public function creatorRef(string $creatorRef): static
    {
        $this->data['Account']['creatorRef'] = $creatorRef;

        return $this;
    }

    /**
     * Add API keys to be generated for the account.
     *
     * @param array|string $apiKeys
     * @return static
     */
    public function apiKeys(array|string $apiKeys): static
    {
        if (is_string($apiKeys)) {
            $apiKeys = [$apiKeys];
        }

        $this->data['ApiKeys'] = $apiKeys;

        return $this;
    }

    /**
     * Add a single API key to be generated.
     *
     * @param string $apiKeyName
     * @return static
     */
    public function addApiKey(string $apiKeyName): static
    {
        if (! isset($this->data['ApiKeys'])) {
            $this->data['ApiKeys'] = [];
        }

        $this->data['ApiKeys'][] = $apiKeyName;

        return $this;
    }

    /**
     * Set tags for the account.
     *
     * @param array $tags
     * @return static
     */
    public function tags(array $tags): static
    {
        $this->data['Tags'] = $tags;

        return $this;
    }

    /**
     * Add a single tag to the account.
     *
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function addTag(string $name, mixed $value): static
    {
        if (! isset($this->data['Tags'])) {
            $this->data['Tags'] = [];
        }

        $this->data['Tags'][$name] = $value;

        return $this;
    }

    /**
     * Set all data from an array.
     *
     * @param array $data
     * @return static
     */
    public function setFromArray(array $data): static
    {
        // Handle flat array format
        if (isset($data['email'])) {
            $this->email($data['email']);
        }

        if (isset($data['password'])) {
            $this->password($data['password']);
        }

        if (isset($data['firstname'])) {
            $this->firstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $this->lastname($data['lastname']);
        }

        if (isset($data['creatorRef'])) {
            $this->creatorRef($data['creatorRef']);
        }

        if (isset($data['apiKeys'])) {
            $this->apiKeys($data['apiKeys']);
        }

        if (isset($data['tags'])) {
            $this->tags($data['tags']);
        }

        // Handle nested format (as used by PrintNode API)
        if (isset($data['Account'])) {
            if (isset($data['Account']['email'])) {
                $this->email($data['Account']['email']);
            }

            if (isset($data['Account']['password'])) {
                $this->password($data['Account']['password']);
            }

            if (isset($data['Account']['firstname'])) {
                $this->firstname($data['Account']['firstname']);
            }

            if (isset($data['Account']['lastname'])) {
                $this->lastname($data['Account']['lastname']);
            }

            if (isset($data['Account']['creatorRef'])) {
                $this->creatorRef($data['Account']['creatorRef']);
            }
        }

        if (isset($data['ApiKeys'])) {
            $this->apiKeys($data['ApiKeys']);
        }

        if (isset($data['Tags'])) {
            $this->tags($data['Tags']);
        }

        return $this;
    }

    /**
     * Validate the request data.
     *
     * @throws InvalidArgumentException
     */
    public function validate(): void
    {
        if (! isset($this->data['Account']['email'])) {
            throw new InvalidArgumentException('Email is required for creating an account');
        }

        if (! isset($this->data['Account']['password'])) {
            throw new InvalidArgumentException('Password is required for creating an account');
        }

        // Set defaults for deprecated fields if not set
        if (! isset($this->data['Account']['firstname'])) {
            $this->firstname();
        }

        if (! isset($this->data['Account']['lastname'])) {
            $this->lastname();
        }
    }

    /**
     * Convert to array format for API request.
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->validate();

        return $this->data;
    }

    /**
     * Create a new instance from array.
     *
     * @param array $data
     * @return static
     */
    public static function make(array $data = []): static
    {
        return new static($data);
    }
}