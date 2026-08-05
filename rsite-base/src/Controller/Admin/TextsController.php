<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class TextsController extends AppController
{
    /**
     * Rows themselves are never added/removed here — that only happens via
     * a migration. This just lists what already exists.
     */
    public function index(): void
    {
        $texts = $this->fetchTable('Texts')->find()->orderBy(['id' => 'ASC'])->all();

        $this->set(compact('texts'));
    }

    public function edit(?string $id = null)
    {
        $Texts = $this->fetchTable('Texts');
        $text = $Texts->get($id);

        if ($this->request->is(['post', 'put'])) {
            $text = $Texts->patchEntity($text, $this->request->getData(), ['fields' => ['value']]);

            if ($Texts->save($text)) {
                $this->Flash->success(__('Text saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the text.'));
        }

        $this->set(compact('text'));

        return null;
    }
}