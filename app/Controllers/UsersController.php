<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UsersController extends BaseController
{
    /**
     * Retrieve a list of  active users.
     *
     * @return ResponseInterface The HTTP response containing user details.
     */
    public function index(): ResponseInterface
    {
        $model = new UserModel();
        $users = $model->findAll();

        $users = array_map(function ($user) {
            unset($user['password'], $user['type_user']);
            return $user;
        }, $users);

        return $this->getResponse([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ]);
    }

    /**
     * Show the details of a user by their ID.
     *
     * @param int $id The ID of the user to retrieve.
     * @return ResponseInterface The HTTP response containing user details.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $model = new UserModel();
            $user = $model->findUserById($id);

            unset($user['password'], $user['type_user']);

            return $this->getResponse([
                'message' => 'User retrieved successfully',
                'user' => $user
            ]);
        } catch (\Exception $exception) {
            return $this->getResponse([
                'message' => 'Could not find user for specified ID'
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update a user's details.
     *
     * @param int $id The ID of the user to update.
     * @return ResponseInterface The HTTP response.
     */
    public function update(int $id): ResponseInterface
    {
        try {
            $model = new UserModel();
            $model->findUserById($id);

            $input = $this->getRequestInput($this->request);


            $model->update($id, $input);
            $user = $model->findUserById($id);

            unset($user['password'], $user['type_user']);

            return $this->getResponse([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $exception) {

            return $this->getResponse([
                'message' => $exception->getMessage()
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete a user by ID.
     *
     * @param int $id The ID of the user to delete.
     * @return ResponseInterface The HTTP response.
     */
    public function destroy(int $id): ResponseInterface
    {
        try {
            $model = new UserModel();
            $user = $model->withDeleted()->findUserById($id);

            if (empty($user)) {
                throw new \Exception('User not found');
            }

            if (!$model->delete($user)) {
                throw new \Exception('Failed to delete user');
            }

            return $this->getResponse([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $exception) {
            return $this->getResponse([
                'message' => $exception->getMessage()
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }

    /**
     * Restore a soft-deleted user by ID.
     *
     * @param int $id The ID of the user to restore.
     * @return ResponseInterface The HTTP response.
     */
    public function restore(int $id): ResponseInterface
    {
        try {
            $model = new UserModel();
            $restored = $model->restoreDeleted($id);

            if ($restored) {
                return $this->getResponse([
                    'message' => 'User restored successfully',
                ]);
            } else {
                return $this->getResponse([
                    'message' => 'User restoration failed',
                ], ResponseInterface::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $exception) {
            return $this->getResponse([
                'message' => $exception->getMessage(),
            ], ResponseInterface::HTTP_NOT_FOUND);
        }
    }
}
