<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Entity\Logo;
use App\Model\Table\LogosTable;
use Cake\Http\Response;
use Rcore\Controller\Admin\ImageUploadTrait;

class LogosController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $logos = $this->fetchTable('Logos')->find()->orderBy(['id' => 'ASC'])->all();

        $this->set(compact('logos'));
    }

    public function add()
    {
        $logo = $this->fetchTable('Logos')->newEmptyEntity();
        $saved = $this->saveLogo($logo, true);
        if ($saved !== null) {
            return $saved;
        }

        $this->set(compact('logo'));

        return $this->render('edit');
    }

    public function edit(?string $id = null)
    {
        $logo = $this->fetchTable('Logos')->get($id);
        $saved = $this->saveLogo($logo, false);
        if ($saved !== null) {
            return $saved;
        }

        $this->set(compact('logo'));

        return null;
    }

    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $Logos = $this->fetchTable('Logos');
        $logo = $Logos->get($id);

        if ($Logos->delete($logo)) {
            if ($logo->path !== '') {
                $this->deleteImageUpload('logos', $logo->path);
            }
            $this->Flash->success(__('Logo deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the logo.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Shared by add and edit. $imageRequired is true only when creating,
     * because a logo with no file has nothing to show.
     */
    private function saveLogo(Logo $logo, bool $imageRequired): ?Response
    {
        if (!$this->request->is(['post', 'put'])) {
            return null;
        }

        $Logos = $this->fetchTable('Logos');
        $data = $this->request->getData();
        /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
        $upload = $data['path'] ?? null;
        unset($data['path']);

        $type = (string)($data['type'] ?? '');
        unset($data['type']);
        if (!in_array($type, [LogosTable::TYPE_MAIN, LogosTable::TYPE_PARTNER], true)) {
            $type = LogosTable::TYPE_PARTNER;
        }

        $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
        $uploadError = ($hasNewFile || $imageRequired) ? $this->imageUploadError($upload, $imageRequired) : null;

        $oldPath = (string)$logo->path;
        $logo = $Logos->patchEntity($logo, $data);
        $logo->options = ['type' => $type];

        if (!$logo->getErrors() && $uploadError === null) {
            if ($hasNewFile) {
                $logo->path = $this->storeImageUpload($upload, 'logos');
            }

            if ($Logos->save($logo)) {
                if ($hasNewFile && $oldPath !== '') {
                    $this->deleteImageUpload('logos', $oldPath);
                }
                $this->Flash->success(__('Logo saved.'));

                return $this->redirect(['action' => 'index']);
            }
        }

        if ($uploadError !== null) {
            $this->Flash->error($uploadError);
        } else {
            $this->Flash->error(__('Could not save the logo.'));
        }

        return null;
    }
}
