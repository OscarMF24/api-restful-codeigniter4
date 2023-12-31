<?php

namespace App\Models;

use Exception;
use CodeIgniter\Model;
use App\Models\LoginLogsModel;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name', 'last_name', 'phone', 'email', 'photo', 'password', 'type_user'];

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    /**
     * Prepare data before inserting.
     *
     * @param array $data The data to be inserted.
     * @return array The modified data with hashed password.
     */
    protected function beforeInsert(array $data): array
    {
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    /**
     * Prepare data before updating.
     *
     * @param array $data The data to be updated.
     * @return array The modified data with hashed password.
     */
    protected function beforeUpdate(array $data): array
    {
        return $this->getUpdatedDataWithHashedPassword($data);
    }

    /**
     * Update data with a hashed password if it exists.
     *
     * @param array $data The data to be updated.
     * @return array The modified data with hashed password.
     */
    private function getUpdatedDataWithHashedPassword(array $data): array
    {
        if (isset($data['data']['password'])) {
            $plaintextPassword = $data['data']['password'];
            $data['data']['password'] = password_hash($plaintextPassword, PASSWORD_BCRYPT);
        }

        return $data;
    }

    /**
     * Finds a user by phone number.
     *
     * @param string $phone The phone number to search for.
     * @return array The user data as an array.
     * @throws Exception If the user does not exist for the specified phone number.
     */
    public function findUserByPhone(string $phone): array
    {
        $user = $this->asArray()->where(['phone' => $phone])->first();

        if (!$user) {
            throw new Exception('User does not exist for specified phone');
        }

        return $user;
    }

    /**
     * Find a user by their ID.
     *
     * @param int $id The ID of the user to find.
     * @return array The data of the found user.
     * @throws \Exception If no user is found for the specified ID.
     */
    public function findUserById(int $id): array
    {
        $user = $this->asArray()->where(['id' => $id])->first();

        if (!$user) {
            throw new \Exception('Could not find user for specified ID');
        }

        return $user;
    }

    /**
     * Restore a softly deleted record in the database.
     *
     * @param int $id The ID of the record to restore.
     * @return bool True if the restoration was successful, false if it failed.
     */
    public function restoreDeleted(int $id): bool
    {
        return $this->asArray()->withDeleted()->update($id, [$this->deletedField => null]);
    }

    /**
     * Get the last login record for a specific user.
     *
     * @param int $userId The ID of the user.
     * @return array|null The last login record as an array, or null if no record is found.
     */
    public function getLastLogin(int $userId): ?array
    {
        $loginLogsModel = new LoginLogsModel();

        return $loginLogsModel->select('login_time')
            ->where('user_id', $userId)
            ->orderBy('login_time', 'DESC')
            ->first();
    }
}
