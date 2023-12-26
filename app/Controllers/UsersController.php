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

            if ($photoFile !== null && $photoFile->isValid() && !$photoFile->hasMoved()) {
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
    public function generateUserListPdf(): ResponseInterface
    {
        try {
            $pdf = new \TCPDF();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AddPage();

            $model = new UserModel();
            $users = $model->findAll();

            if (!(count($users))) {
                return $this->getResponse([
                    'message' => 'No users found',
                ], ResponseInterface::HTTP_NOT_FOUND);
            }

            $html = '<h1>List of Users</h1>';
            $html .= '<table>';
            $html .= '<tr><th>Name</th><th>Email</th></tr>';

            foreach ($users as $user) {
                $html .= '<tr>';
                $html .= '<td>' . $user['name'] . ' ' . $user['last_name'] . '</td>';
                $html .= '<td>' . $user['email'] . '</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $pdfFileName = 'user_list_' . date('YmdHis') . '.pdf';

            $pdfFilePath = WRITEPATH . 'pdfs/' . $pdfFileName;
            $pdf->Output($pdfFilePath, 'F');

            $pdfUrl = base_url('pdfs/' . $pdfFileName);

            return $this->getResponse([
                'message' => 'PDF generated successfully',
                'pdf_url' => $pdfUrl,
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->getResponse([
                'error' => $exception->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve the authenticated user.
     *
     * @return ResponseInterface The HTTP response containing user details.
     */
    public function profile(): ResponseInterface
    {
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');

        try {
            helper('jwt');
            $encodedToken = getJWTFromRequest($authenticationHeader);
            $decodedToken = validateJWTFromRequest($encodedToken);

            $model = new UserModel();
            $user = $model->findUserByPhone($decodedToken->phone);

            if ($user) {
                unset($user['password']);

                $lastLogin = $model->getLastLogin($user['id']);
                $user['last_login'] = $lastLogin ? $lastLogin['login_time'] : null;

                return $this->getResponse([
                    'message' => 'User retrieved successfully',
                    'user' => $user
                ]);
            } else {
                return $this->getResponse(['error' => 'Record not found'], ResponseInterface::HTTP_NOT_FOUND);
            }
        } catch (\Exception $exception) {
            return $this->getResponse([
                'error' => $exception->getMessage()
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     *  Update authenticated user data.
     *
     * @return ResponseInterface The HTTP response containing user details.
     */
    public function updateProfile(): ResponseInterface
    {
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');

        try {
            helper('jwt');
            $encodedToken = getJWTFromRequest($authenticationHeader);
            $decodedToken = validateJWTFromRequest($encodedToken);

            $model = new UserModel();
            $user = $model->findUserByPhone($decodedToken->phone);

            if ($user) {
                $input = $this->getRequestInput($this->request);
                $photoFile = $this->request->getFile('photo');

                if ($photoFile !== null && $photoFile->isValid() && !$photoFile->hasMoved()) {
                    $newName = $photoFile->getRandomName();
                    $photoFile->move(ROOTPATH . 'public/uploads', $newName);
                    $photoPath = 'uploads/' . $newName;
                    $input['photo'] = $photoPath;
                } else {
                    unset($input['photo']);
                }

                $model->update($user['id'], $input);

                $updatedUser = $model->findUserByPhone($user['phone']);

                $lastLogin = $model->getLastLogin($user['id']);
                $updatedUser['last_login'] = $lastLogin ? $lastLogin['login_time'] : null;
                unset($updatedUser['password']);

                return $this->getResponse([
                    'message' => 'User profile updated successfully',
                    'user' => $updatedUser
                ]);
            } else {
                return $this->getResponse(['error' => 'User not found'], ResponseInterface::HTTP_NOT_FOUND);
            }
        } catch (\Exception $exception) {
            return $this->getResponse([
                'error' => $exception->getMessage()
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }
}
