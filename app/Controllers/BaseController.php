<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\SiteSettingModel; // Jangan lupa use Model ini

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * class Home extends BaseController
 *
 * For security, be sure to declare any new methods as protected or private.
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
    protected $helpers = ['form', 'url']; // Tambahkan helper umum di sini

    /**
     * Variabel Global Identitas Website (White Label)
     * Bisa diakses di Controller anak via $this->siteIdentity
     */
    protected $siteIdentity;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // =================================================================
        // LOGIKA GLOBAL: LOAD SITE SETTINGS (WHITE LABEL)
        // =================================================================
        
        $siteModel = new SiteSettingModel();
        
        // Ambil data pertama, jika kosong (belum setup db) pakai default
        $this->siteIdentity = $siteModel->first() ?? [
            'site_name'    => 'Estato',
            'company_name' => 'Estato System',
            'site_logo'    => 'default.png',
            'footer_text'  => 'Smart CRM System'
        ];

        // --- CARA MODERN CI4: SHARE KE SEMUA VIEW ---
        // Dengan ini, variabel $site otomatis tersedia di semua file View (.php)
        // Tidak perlu passing manual di setiap controller ($data['site'] = ...)
        \Config\Services::renderer()->setData(['site' => $this->siteIdentity], 'raw');
    }
}