<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginLogsModel;
use CodeIgniter\HTTP\Response;
use App\Controllers\BaseController;
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
            'phone' => 'required|min_length[10]|max_length[12]|is_unique[users.phone]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'photo' => 'uploaded[photo]|max_size[photo,2048]|ext_in[photo,png,jpg,jpeg]',
            'password' => 'required|min_length[8]|max_length[255]',
            'type_user' => 'in_list[admin,basic]'
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->isAdmin()) {
            return $this->getResponse(['message' => 'Access denied. You must be an admin to create users.'], ResponseInterface::HTTP_FORBIDDEN);
        }

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        $photoFile = $this->request->getFile('photo');

        if ($photoFile->isValid() && !$photoFile->hasMoved()) {
            $newName = $photoFile->getRandomName();
            $photoFile->move(ROOTPATH . 'public/uploads', $newName);
            $photoPath = 'uploads/' . $newName;
            $input['photo'] = $photoPath;
        } else {
            return $this->getResponse(['message' => 'Invalid photo upload'], ResponseInterface::HTTP_BAD_REQUEST);
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

            $this->createLoginLog($user['phone']);

            helper('jwt');

            return $this->getResponse([
                'message' => 'User authenticated successfully',
                'user' => $user,
                'access_token' => getSignedJWTForUser($phone, $user['type_user'])
            ]);
        } catch (\Exception $exception) {

            return $this->getResponse([
                'error' => $exception->getMessage()
            ], $responseCode);
        }
    }

    /**
     * Create a login log entry for the user.
     *
     * @param string $phone The user's phone number.
     * @return void
     */
    private function createLoginLog(string $phone): void
    {
        try {
            $model = new UserModel();
            $user = $model->findUserByPhone($phone);

            $loginLogModel = new LoginLogsModel();
            $loginLogModel->insert([
                'user_id' => $user['id'],
                'login_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $exception) {
            //
        }
    }

    /**
     * Check if the authenticated user is an admin.
     *
     * @return bool
     */
    private function isAdmin(): bool
    {
        $decodedToken = $this->getDecodedTokenFromRequest();
        return isset($decodedToken->type_user) && $decodedToken->type_user === 'admin';
    }

    /**
     * Get the decoded JWT token from the request.
     *
     * @return object|null
     */
    private function getDecodedTokenFromRequest(): ?object
    {
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');

        if (is_null($authenticationHeader)) {
            return null;
        }

        $encodedToken = explode(' ', $authenticationHeader)[1];

        try {
            helper('jwt');
            return validateJWTFromRequest($encodedToken);
        } catch (\Exception $exception) {
            return null;
        }
    }
}
