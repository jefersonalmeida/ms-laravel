<?php

namespace App\Auth;

use App\Models\User;
use BadMethodCallException;
use Exception;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Macroable;
use Tymon\JWTAuth\JWT;

class KeycloakGuard implements Guard
{
    use GuardHelpers, Macroable {
        __call as macroCall;
    }

    public function __construct(private JWT $jwt, private Request $request)
    {
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($token = $this->jwt->setRequest($this->request)->getToken() &&
            ($payload = $this->jwt->check(true))) {

            return $this->user = new User(
                $payload['sub'],
                $payload['name'],
                $payload['email'],
                $token
            );
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function validate(array $credentials = [])
    {
        throw new Exception('Not implemented');
    }

    /**
     * Magically call the JWT instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     * @throws BadMethodCallException
     *
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->jwt, $method)) {
            return call_user_func_array([$this->jwt, $method], $parameters);
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
