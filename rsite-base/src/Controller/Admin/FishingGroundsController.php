<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class FishingGroundsController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $fishingGrounds = $this->fetchTable('FishingGrounds')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all();

        $this->set(compact('fishingGrounds'));
    }

    public function add()
    {
        $FishingGrounds = $this->fetchTable('FishingGrounds');
        $fishingGround = $FishingGrounds->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasFile ? $this->imageUploadError($upload, false) : null;

            $fishingGround = $FishingGrounds->patchEntity($fishingGround, $data);

            if (!$fishingGround->getErrors() && $uploadError === null) {
                if ($hasFile) {
                    $fishingGround->image = $this->storeImageUpload($upload, 'fishing-grounds');
                }

                if ($FishingGrounds->save($fishingGround)) {
                    $this->Flash->success(__('Fishing ground saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the fishing ground, check the errors below.'));
        }

        $this->set('fishingGround', $fishingGround);

        return null;
    }

    public function edit(?string $id = null)
    {
        $FishingGrounds = $this->fetchTable('FishingGrounds');
        $fishingGround = $FishingGrounds->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldImage = $fishingGround->image;
            $fishingGround = $FishingGrounds->patchEntity($fishingGround, $data);

            if (!$fishingGround->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $fishingGround->image = $this->storeImageUpload($upload, 'fishing-grounds');
                }

                if ($FishingGrounds->save($fishingGround)) {
                    if ($hasNewFile && $oldImage) {
                        $this->deleteImageUpload('fishing-grounds', $oldImage);
                    }

                    $this->Flash->success(__('Fishing ground saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the fishing ground, check the errors below.'));
        }

        $this->set('fishingGround', $fishingGround);

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $FishingGrounds = $this->fetchTable('FishingGrounds');
        $fishingGround = $FishingGrounds->get($id);

        if ($FishingGrounds->delete($fishingGround)) {
            if ($fishingGround->image) {
                $this->deleteImageUpload('fishing-grounds', $fishingGround->image);
            }

            $this->Flash->success(__('Fishing ground deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the fishing ground.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
