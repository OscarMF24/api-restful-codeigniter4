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

        $usersWithLastLogin = array_map(function ($user) use ($model) {
            unset($user['password'], $user['type_user']);

            $lastLogin = $model->getLastLogin($user['id']);
            $user['last_login'] = $lastLogin ? $lastLogin['login_time'] : null;

            return $user;
        }, $users);

        return $this->getResponse([
            'message' => 'Users retrieved successfully',
            'users' => $usersWithLastLogin
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

            $lastLogin = $model->getLastLogin($user['id']);
            $user['last_login'] = $lastLogin ? $lastLogin['login_time'] : null;


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
            $user = $model->findUserById($id);

            $input = $this->getRequestInput($this->request);
            $photoFile = $this->request->getFile('photo');

            if ($photoFile->isValid() && !$photoFile->hasMoved()) {
                if (!empty($user['photo'])) {
                    unlink(ROOTPATH . 'public/' . $user['photo']);
                }

                $newName = $photoFile->getRandomName();
                $photoFile->move(ROOTPATH . 'public/uploads', $newName);
                $photoPath = 'uploads/' . $newName;
                $input['photo'] = $photoPath;
            } else {
                unset($input['photo']);
            }

            $model->update($id, $input);
            $user = $model->findUserById($id);

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

    /**
     * Genera un archivo PDF que contiene un listado de usuarios.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function generateUserListPdf(): Response
    {
        $mpdf = new \Mpdf\Mpdf();

        $userModel = new UserModel();
        $users = $userModel->findAll();

        $html = '<h1>Listado de Usuarios</h1>';
        foreach ($users as $user) {
            $html .= '<img src="' . site_url('public/' . $user['photo']) . '" width="100" height="100"><br>';
            $html .= 'Nombre: ' . $user['name'] . ' ' . $user['last_name'] . '<br>';
            $html .= 'Celular: ' . $user['phone'] . '<br>';
            $html .= 'Correo electrónico: ' . $user['email'] . '<br>';
            $html .= 'Tipo de Usuario: ' . $user['type_user'] . '<br><br>';
        }

        $mpdf->WriteHTML($html);

        $pdfFileName = 'user_list.pdf';
        $pdfFilePath = WRITEPATH . 'uploads/' . $pdfFileName;
        $mpdf->Output($pdfFilePath, 'F');

        $pdfUrl = base_url('uploads/' . $pdfFileName);

        $response = [
            'message' => 'PDF generate successfully',
            'pdf_url' => $pdfUrl,
        ];

        return $this->response->setJSON($response);
    }
}
