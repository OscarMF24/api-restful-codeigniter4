<?php

namespace App\Controllers;

use Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Get a JSON response with the given response body and status code.
     *
     * @param array $responseBody The response body.
     * @param int $code The HTTP status code (default is HTTP_OK).
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK): ResponseInterface
    {
        return $this->response->setStatusCode($code)->setJSON($responseBody);
    }

    /**
     * Get and return input data from an IncomingRequest.
     *
     * @param \CodeIgniter\HTTP\IncomingRequest $request The incoming request.
     * @return array The input data.
     */
    public function getRequestInput(IncomingRequest $request): array
    {
        $input = $request->getPost();

        if (empty($input)) {
            $input = json_decode($request->getBody(), true);
        }

        return $input;
    }

    /**
     * Validate input data against specified rules and messages.
     *
     * @param array $input The input data to validate.
     * @param array|string $rules The validation rules.
     * @param array $messages Custom error messages (optional).
     * @return bool True if validation passes, false otherwise.
     * @throws \CodeIgniter\Validation\Exceptions\ValidationException
     */
    public function validateRequest(array $input, array $rules, array $messages = []): bool
    {
        $this->validator = Services::validation()->setRules($rules);

        if (is_string($rules)) {
            $validation = config('Validation');

            if (!isset($validation->$rules)) {
                throw ValidationException::forRuleNotFound($rules);
            }

            if (!$messages) {
                $errorName = $rules . '_errors';
                $messages = $validation->$errorName ?? [];
            }

            $rules = $validation->$rules;
        }

        return $this->validator->setRules($rules, $messages)->run($input);
    }
}
