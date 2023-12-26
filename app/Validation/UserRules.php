<?php

namespace App\Validation;

use App\Models\UserModel;

class UserRules
{
    /**
     * Validate user credentials.
     *
     * @param string $str The validation field value (password).
     * @param string $fields The other validation field (phone).
     * @param array $data The entire data array being validated.
     * @return bool True if the user credentials are valid, false otherwise.
     */
    public function validateUser(string $str, string $fields, array $data): bool
    {
        try {
            $model = new UserModel();
            $user = $model->findUserByPhone($data['phone']);
            return password_verify($data['password'], $user['password']);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
