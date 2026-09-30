<?php
declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * Base controller for the admin section (/admin/*) — a pass-through onto
 * the shared Rcore plugin's own base, which provides the admin layout,
 * Flash/Authentication, adminCategories()/adminUrl(), and the login
 * whitelist. Every existing `extends AppController` in this app's own
 * domain controllers keeps resolving to this same class/namespace
 * unchanged; this app's own admin sections are registered via
 * Configure::write('Rcore.extraAdminCategories', [...]) in
 * src/Application.php rather than overriding anything here.
 */
class AppController extends \Rcore\Controller\Admin\AppController
{
}
