<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

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
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // E.g.: $this->session = service('session');
        
        // Set no-cache headers globally untuk semua response
        $this->setGlobalNoCacheHeaders($response);
    }

    /**
     * Set no-cache headers untuk semua response
     * Ini mencegah browser menyimpan cache dari halaman/API
     */
    protected function setGlobalNoCacheHeaders(ResponseInterface $response)
    {
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Cache-Control', 'post-check=0, pre-check=0', false);
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        
        // Tambahan header untuk mencegah caching
        $response->setHeader('X-Accel-Expires', '0');
        
        // Untuk AJAX requests, tambahkan header khusus
        if ($this->request->isAJAX()) {
            $response->setHeader('X-Requested-With', 'XMLHttpRequest');
        }
    }

    /**
     * Helper method untuk set no-cache headers (backward compatibility)
     * Method ini PROTECTED agar bisa dipanggil dari child class
     */
    protected function setNoCacheHeaders()
    {
        $this->setGlobalNoCacheHeaders($this->response);
    }
}