<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @mixin IdeHelperUser
 */
class User implements Authenticatable
{

    public function __construct(
        protected string $id,
        protected string $name,
        protected string $email,
        protected string $token
    ) {

    }

    public function getAuthIdentifierName(): string
    {
        return $this->email;
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * @throws Exception
     */
    public function getAuthPassword()
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function getRememberToken()
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function setRememberToken($value)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @throws Exception
     */
    public function getRememberTokenName()
    {
        throw new Exception('Not implemented');
    }
}
