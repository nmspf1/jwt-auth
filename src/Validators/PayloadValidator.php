<?php

namespace Tymon\JWTAuth\Validators;

use Tymon\JWTAuth\Support\Utils;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

/**
 * Class PayloadValidator
 *
 * @package Tymon\JWTAuth\Validators
 */
class PayloadValidator extends Validator
{
    /**
     * @var array
     */
    protected $requiredClaims = ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'];

    /**
     * @var integer
     */
    protected $refreshTTL = 20160;

    /**
     * Run the validations on the payload array
     *
     * @param  array  $value
     *
     * @return void
     */
    public function check($value)
    {
        $this->validateStructure($value);

        if (! $this->refreshFlow) {
            $this->validateTimestamps($value);
        } else {
            $this->validateRefresh($value);
        }
    }

    /**
     * Ensure the payload contains the required claims and
     * the claims have the relevant type
     *
     * @param array  $payload
     *
     * @throws \Tymon\JWTAuth\Exceptions\TokenInvalidException
     *
     * @return boolean
     */
    protected function validateStructure(array $payload)
    {
        if (count(array_diff_key($this->requiredClaims, array_keys($payload))) !== 0) {
            throw new TokenInvalidException('JWT payload does not contain the required claims');
        }

        return true;
    }

    /**
     * Validate the payload timestamps
     *
     * @param  array  $payload
     *
     * @throws \Tymon\JWTAuth\Exceptions\TokenExpiredException
     * @throws \Tymon\JWTAuth\Exceptions\TokenInvalidException
     *
     * @return boolean
     */
    protected function validateTimestamps(array $payload)
    {
        if (isset($payload['nbf']) && Utils::timestamp($payload['nbf'])->isFuture()) {
            throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future', 400);
        }

        if (isset($payload['iat']) && Utils::timestamp($payload['iat'])->isFuture()) {
            throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future', 400);
        }

        if (Utils::timestamp($payload['exp'])->isPast()) {
            throw new TokenExpiredException('Token has expired');
        }

        return true;
    }

    /**
     * Check the token in the refresh flow context
     *
     * @param  $payload
     *
     * @throws \Tymon\JWTAuth\Exceptions\TokenExpiredException
     *
     * @return boolean
     */
    protected function validateRefresh(array $payload)
    {
        if (isset($payload['iat']) && Utils::timestamp($payload['iat'])->diffInMinutes(Utils::now()) >= $this->refreshTTL) {
            throw new TokenExpiredException('Token has expired and can no longer be refreshed', 400);
        }

        return true;
    }

    /**
     * Set the required claims
     *
     * @param array  $claims
     *
     * @return PayloadValidator
     */
    public function setRequiredClaims(array $claims)
    {
        $this->requiredClaims = $claims;

        return $this;
    }

    /**
     * Set the refresh ttl
     *
     * @param integer  $ttl
     */
    public function setRefreshTTL($ttl)
    {
        $this->refreshTTL = $ttl;

        return $this;
    }
}
