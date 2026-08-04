<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseController;
use Cake\Event\EventInterface;

/**
 * Base controller for the admin section (/admin/*).
 *
 * Every action here requires a logged in admin user, except the
 * login action itself (whitelisted in beforeFilter below).
 */
class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->viewBuilder()->setLayout('admin');
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login']);
    }
}