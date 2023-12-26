<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function register(): Response
    {
        $rules = [
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|min_length[10]|max_length[12]|is_unique[user.phone]',
            'email' => 'required|valid_email|is_unique[user.email]',
            'password' => 'required|min_length[8]|max_length[255]',
            'type_user' => 'in_list[admin,basic]'
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userModel = new UserModel();
        $userModel->save($input);

        return $this->getJWTForUser($input['phone'], ResponseInterface::HTTP_CREATED);
    }
    /**
     * Handle user login.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function login(): Response
    {
        $rules = [
            'phone' => 'required|min_length[10]|max_length[12]',
            'password' => 'required|min_length[8]|max_length[255]|validateUser[phone, password]'
        ];

        $errors = [
            'password' => [
                'validateUser' => 'Invalid login credentials provided'
            ]
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules, $errors)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        return $this->getJWTForUser($input['phone']);
    }

    /**
     * Get JWT token for a user.
     *
     * @param string $phone The user's phone number.
     * @param int $responseCode The HTTP response code (default is HTTP_OK).
     * @return \CodeIgniter\HTTP\Response
     */
    private function getJWTForUser(string $phone, int $responseCode = ResponseInterface::HTTP_OK): Response
    {
        try {
            $model = new UserModel();
            $user = $model->findUserByPhone($phone);
            unset($user['password']);

            helper('jwt');

            return $this->getResponse([
                'message' => 'User authenticated successfully',
                'user' => $user,
                'access_token' => getSignedJWTForUser($phone)
            ]);
        } catch (\Exception $exception) {

            return $this->getResponse([
                'error' => $exception->getMessage()
            ], $responseCode);
        }
    }
}
