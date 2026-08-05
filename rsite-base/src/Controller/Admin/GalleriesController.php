<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class GalleriesController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $photos = $this->fetchTable('Galleries')
            ->find()
            ->contain(['Categories'])
            ->orderBy(['Galleries.created' => 'DESC'])
            ->all();

        $this->set(compact('photos'));
    }

    public function add()
    {
        $Galleries = $this->fetchTable('Galleries');
        $photo = $Galleries->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $photo = $Galleries->patchEntity($photo, $data);
            $uploadError = $this->imageUploadError($upload, true);

            if (!$photo->getErrors() && $uploadError === null) {
                $photo->image = $this->storeImageUpload($upload, 'galleries');

                if ($Galleries->save($photo)) {
                    $this->Flash->success(__('Photo saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the photo, check the errors below.'));
        }

        $this->set('photo', $photo);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function edit(?string $id = null)
    {
        $Galleries = $this->fetchTable('Galleries');
        $photo = $Galleries->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldImage = $photo->image;
            $photo = $Galleries->patchEntity($photo, $data);

            if (!$photo->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $photo->image = $this->storeImageUpload($upload, 'galleries');
                }

                if ($Galleries->save($photo)) {
                    if ($hasNewFile) {
                        $this->deleteImageUpload('galleries', $oldImage);
                    }

                    $this->Flash->success(__('Photo saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the photo, check the errors below.'));
        }

        $this->set('photo', $photo);
        $this->set('categories', $this->categoryOptions());

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Galleries = $this->fetchTable('Galleries');
        $photo = $Galleries->get($id);

        if ($Galleries->delete($photo)) {
            $this->deleteImageUpload('galleries', $photo->image);

            $this->Flash->success(__('Photo deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the photo.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @return array<int, string>
     */
    private function categoryOptions(): array
    {
        return $this->fetchTable('Categories')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();
    }
}