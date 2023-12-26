<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('auth', ['namespace' => 'App\Controllers'], function ($routes) {
    /**
     * Route for user login.
     *
     * Method: POST
     * Description: This route is used for user login by providing credentials.
     */
    $routes->add('login', 'AuthController::login', ['as' => 'login']);

    /**
     * Route for user registration only admin's.
     *
     * Method: POST
     * Description: This route is used for user registration by providing required data.
     */
    $routes->add('register', 'AuthController::register', ['as' => 'register', 'filter' => 'admin']);
});

$routes->group('users', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    /**
     * Route to retrieve a list of users.
     *
     * Method: GET
     * Description: This route is used to retrieve a list of registered users.
     */
    $routes->get('/', 'UsersController::index', ['as' => 'indexUsers']);

    /**
     * Route to retrieve a user by ID only admin's.
     *
     * Method: GET
     * Description: This route is used to retrieve details of a user by their ID.
     *              It accepts a numeric user ID as a parameter.
     */
    $routes->get('(:num)', 'UsersController::show/$1', ['as' => 'showUsers', 'filter' => 'admin']);

    /**
     * Route to partially update a user only admin's.
     *
     * Method: PATCH (POST)
     * Description: This route is used to partially update the details of a user
     * by their numeric ID. It accepts a JSON or form data payload with the fields
     * to be updated.
     * IMPORTAN: Changed method to post for method compatibility in Codeigniter4.
     * If patch works but you cannot enter the data through form-data only because of x-www-form-urlencoded
     */
    $routes->post('(:num)', 'UsersController::update/$1', ['as' => 'updateUsers', 'filter' => 'admin']);

    /**
     * Route to partially delete a user only admin's.
     *
     * Method: DELETE
     * Description: This route is used to partially delete a user's account
     * by their numeric ID. It soft-deletes the user, effectively disabling
     * their account while keeping their data for potential recovery.
     * It accepts the user's ID as a parameter.
     */
    $routes->delete('(:num)', 'UsersController::destroy/$1', ['as' => 'destroyUsers', 'filter' => 'admin']);

    /**
     * Route to restore a soft-deleted user only admin´'s.
     *
     * Method: PATCH
     * Description: This route is used to restore a user who has been soft-deleted, providing their numeric ID.
     * Restoration allows re-enabling the user's account. It accepts the ID of the deleted user as a parameter.
     */
    $routes->post('restore/(:num)', 'UsersController::restore/$1', ['as' => 'restoreUsers', 'filter' => 'admin']);

    /**
     * Route for generating a PDF file containing a list of users.
     *
     * HTTP Method: GET
     * Controller: PdfController
     * Method: generateUserListPdf
     *
     * @see \App\Controllers\PdfController::generateUserListPdf()
     */
    $routes->get('pdf', 'UsersController::generateUserListPdf', ['as' => 'generatePdf', 'filter' => 'admin']);
});

$routes->group('user', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function ($routes) {
    /**
     * Route for Retrieve the authenticated user..
     *
     * Method: GET
     * Description: This route is used to retrieve the authenticated user data.
     */
    $routes->get('profile', 'UsersController::profile', ['as' => 'profileUser']);

    /**
     * Route for Update authenticated user data.
     *
     * Method: POST
     * Description: This route is used to Update authenticated user data.
     */
    $routes->post('profile/update', 'UsersController::updateProfile', ['as' => 'updateProfileUsers']);
});
