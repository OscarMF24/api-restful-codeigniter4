<?php

use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserModel;

/**
 * Extracts the JWT token from the authentication header.
 *
 * @param string|null $authenticationHeader The authentication header.
 * @return string The extracted JWT token.
 * @throws Exception If the JWT is missing or invalid in the request.
 */
function getJWTFromRequest($authenticationHeader): string
{
    if (is_null($authenticationHeader)) {
        throw new Exception('Missing or invalid JWT in request');
    }

    return explode(' ', $authenticationHeader)[1];
}

/**
 * Validates a JWT token from an encoded string.
 *
 * @param string $encodedToken The encoded JWT token.
 * @return object The decoded JWT token as an object.
 * @throws Exception If the token is invalid or the user cannot be found.
 */
function validateJWTFromRequest(string $encodedToken): object
{
    $key = Services::getSecretKey();
    $algorithm = Services::getAlgorithm();
    $decodedToken = JWT::decode($encodedToken, new Key($key, $algorithm));
    $userModel = new UserModel();
    $user = $userModel->findUserByPhone($decodedToken->phone);

    if (!$user) {
        throw new Exception('User not found');
    }

    return $decodedToken;
}

/**
 * Generates and signs a JWT token for a user.
 *
 * @param string $phone The phone number of the user.
 * @return string The signed JWT token.
 */
function getSignedJWTForUser(string $phone, string $typeUser): string
{
    $issuedAtTime = time();
    $tokenTimeToLive = Services::getTimeToLive();
    $tokenExpiration = $issuedAtTime + $tokenTimeToLive;
    $payload = [
        'phone' => $phone,
        'type_user' => $typeUser,
        'issued' => $issuedAtTime,
        'expiration' => $tokenExpiration
    ];

    $key = Services::getSecretKey();
    $algorithm = Services::getAlgorithm();

    $jwt = JWT::encode($payload, $key, $algorithm);

    return $jwt;
}
