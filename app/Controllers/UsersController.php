<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use App\Controllers\BaseController;

class UsersController extends BaseController
{
    /**
     * Retrieve a list of  active users.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index(): Response
    {
        $model = new UserModel();
        $users = $model->findAll();

        $users = array_map(function ($user) {
            unset($user['password']);
            unset($user['type_user']);
            return $user;
        }, $users);

        return $this->getResponse([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ]);
    }
}
