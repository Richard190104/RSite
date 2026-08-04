<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class UsersController extends AppController
{
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        $result = $this->Authentication->getResult();

        if ($result !== null && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect()
                ?? ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'];

            return $this->redirect($target);
        }

        if ($this->request->is('post') && ($result === null || !$result->isValid())) {
            $this->Flash->error(__('Invalid username or password.'));
        }

        return null;
    }

    public function logout()
    {
        $this->Authentication->logout();

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'login']);
    }
}