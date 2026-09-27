<?php

namespace Neoflow\MediaCms\Api;

abstract class AbstractApi
{
    /**
     * @var array
     */
    protected $TEXT;
    protected $MESSAGE;

    /**
     * @var array
     */
    protected $apiMethods = [];

    /**
     * Constructor.
     *
     * @param bool $anonymous Set TRUE to get anonymous access to the API
     * @param array $permissions Needed API permissions based on WBCE permission keys
     */
    public function __construct($anonymous = false, $permissions = [])
    {
        if (!$anonymous) {
            // Check whether user is authenticated
            if (!$this->isAuthenticated()) {
                $this->unauthenticated();
            }

            // Check whether user is authorized
            if (!$this->isAuthorized($permissions)) {
                $this->unauthorized();
            }
        }

        $this->setTranslations();
    }

    /**
     * Load and set translations of WBCE CMS.
     *
     * @return self
     */
    protected function setTranslations()
    {
        // Include language file
        $languageFilePath = THEME_PATH . '/languages/' . LANGUAGE . '.php';
        if (!file_exists($languageFilePath)) {
            $languageFilePath = THEME_PATH . '/languages/EN.php';
        }
        require $languageFilePath;

        if (isset($TEXT)) {
            $this->TEXT = $TEXT;
        }

        if (isset($MESSAGE)) {
            $this->MESSAGE = $MESSAGE;
        }

        return $this;
    }

    /**
     * Check whether user is authenticated.
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['USER_ID']) && isset($_SESSION['SYSTEM_PERMISSIONS']) && is_array($_SESSION['SYSTEM_PERMISSIONS']);
    }

    /**
     * Call API method.
     *
     * @param string $method Name of method
     * @param array $args Method arguments
     */
    public function call($method, $args = [])
    {
        try {
            // Check if API method is valid and exists
            if (in_array($method, $this->apiMethods) && method_exists($this, $method)) {
                call_user_func([$this, $method], $args);
            }
            $this->notFound();
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());
        }
    }

    /**
     * Check whether user is authorized by given permissions.
     *
     * @param array $permissions Authorized permissions
     *
     * @return bool
     */
    protected function isAuthorized($permissions = [])
    {
        if (count($permissions) > 0) {
            foreach ($permissions as $permission) {
                if (!in_array($permission, $_SESSION['SYSTEM_PERMISSIONS'])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Unauthorized API method.
     */
    protected function unauthorized()
    {
        $this->publish([
            'status' => 'error',
            'message' => 'Unauthorized. User has no permission to install/uninstall templates.',
        ], 403);
    }

    /**
     * Not found API method.
     */
    protected function notFound()
    {
        $this->publish([
            'status' => 'error',
            'message' => 'Not found. API method does not exist or method arguments are invalid.',
        ], 404);
    }

    /**
     * Unauthenticated API method.
     */
    protected function unauthenticated()
    {
        $this->publish([
            'status' => 'error',
            'message' => 'Unauthenticated. User is not logged in.',
        ], 401);
    }

    /**
     * Error API method.
     *
     * @param $message Error message
     */
    protected function error($message)
    {
        $this->publish([
            'status' => 'error',
            'message' => $message,
        ], 500);
    }

    /**
     * Publish API response.
     *
     * @param array $data Response data
     * @param int $httpCode HTTP status code
     */
    protected function publish($data, $httpCode = null)
    {
        header('Content-Type: application/json');
        if ($httpCode && is_int($httpCode)) {
            http_response_code($httpCode);
        }
        if (is_array($data)) {
            echo json_encode($data);
        } else {
            echo json_encode([]);
        }

        exit;
    }

    /**
     * Run API.
     */
    public function run()
    {
        $method = '';
        if (isset($_GET['m'])) {
            $method = $_GET['m'];
        }

        $args = [];
        if (isset($_GET['args']) && is_array($_GET['args'])) {
            $args = $_GET['args'];
        }

        $this->call($method, $args);
    }
}
