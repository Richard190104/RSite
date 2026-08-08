<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class LogosController extends AppController
{
    use ImageUploadTrait;

    /**
     * Logo slots are never added/removed here — that only happens via a
     * migration. This just lists the slots that already exist.
     */
    public function index(): void
    {
        $logos = $this->fetchTable('Logos')->find()->orderBy(['id' => 'ASC'])->all();

        $this->set(compact('logos'));
    }

    /**
     * The image is the only editable thing about a logo, so an upload is
     * always required here — submitting the form without a file would be a
     * no-op anyway.
     */
    public function edit(?string $id = null)
    {
        $Logos = $this->fetchTable('Logos');
        $logo = $Logos->get($id);

        if ($this->request->is(['post', 'put'])) {
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $this->request->getData('path');
            $uploadError = $this->imageUploadError($upload, true);

            if ($uploadError === null) {
                $oldPath = (string)$logo->path;
                $logo->path = $this->storeImageUpload($upload, 'logos');

                if ($Logos->save($logo)) {
                    if ($oldPath !== '') {
                        $this->deleteImageUpload('logos', $oldPath);
                    }

                    $this->Flash->success(__('Logo saved.'));

                    return $this->redirect(['action' => 'index']);
                }

                $this->Flash->error(__('Could not save the logo.'));
            } else {
                $this->Flash->error($uploadError);
            }
        }

        $this->set(compact('logo'));

        return null;
    }
}
