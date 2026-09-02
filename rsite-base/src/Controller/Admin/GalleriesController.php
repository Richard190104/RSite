<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class GalleriesController extends AppController
{
    // ImageUploadTrait is used only for its imageUploadError() validation
    // (type/size) — the actual bytes go to ImgBB (ImgBbUploadTrait), not
    // webroot/img/, since a photo gallery can grow far larger than this
    // host's disk quota comfortably holds.
    use ImageUploadTrait;
    use ImgBbUploadTrait;

    public function index(): void
    {
        $photos = $this->fetchTable('Galleries')
            ->find()
            ->contain(['Categories'])
            ->orderBy(['Galleries.created' => 'DESC'])
            ->all();

        $this->set(compact('photos'));
    }

    /**
     * Accepts one or more files at once (the form's file input has the
     * `multiple` attribute and name="image[]") — each valid file becomes
     * its own Gallery row under the single category picked in the form.
     * This is the only admin upload form that allows batch uploads; every
     * other image field (Banners, News, Notifications, Categories) is
     * still one file per record, since only the gallery is meant to grow
     * to dozens/hundreds of photos at a time.
     */
    public function add()
    {
        $Galleries = $this->fetchTable('Galleries');
        $photo = $Galleries->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $categoryId = $data['category_id'] ?? null;

            /** @var array<\Psr\Http\Message\UploadedFileInterface> $uploads */
            $uploads = (array)($data['image'] ?? []);
            $uploads = array_filter(
                $uploads,
                fn ($upload) => $upload instanceof \Psr\Http\Message\UploadedFileInterface
                    && $upload->getError() !== UPLOAD_ERR_NO_FILE,
            );

            if (!$uploads) {
                $this->Flash->error(__('Please choose at least one image.'));
            } else {
                $saved = 0;
                foreach ($uploads as $upload) {
                    $uploadError = $this->imageUploadError($upload, true);
                    if ($uploadError !== null) {
                        $this->Flash->error($uploadError);
                        continue;
                    }

                    try {
                        $uploaded = $this->uploadToImgBb($upload);
                    } catch (\RuntimeException $e) {
                        $this->Flash->error($e->getMessage());
                        continue;
                    }

                    $newPhoto = $Galleries->newEntity([
                        'category_id' => $categoryId,
                        'image' => $uploaded['url'],
                        'delete_url' => $uploaded['deleteUrl'],
                    ]);

                    if ($Galleries->save($newPhoto)) {
                        $saved++;
                    } else {
                        $this->Flash->error(__('Could not save one of the photos.'));
                    }
                }

                if ($saved > 0) {
                    $this->Flash->success(__n('{0} photo saved.', '{0} photos saved.', $saved, $saved));

                    return $this->redirect(['action' => 'index']);
                }
            }
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

            $oldDeleteUrl = $photo->delete_url;
            $photo = $Galleries->patchEntity($photo, $data);

            if (!$photo->getErrors() && $uploadError === null) {
                try {
                    if ($hasNewFile) {
                        $uploaded = $this->uploadToImgBb($upload);
                        $photo->image = $uploaded['url'];
                        $photo->delete_url = $uploaded['deleteUrl'];
                    }

                    if ($Galleries->save($photo)) {
                        if ($hasNewFile && $oldDeleteUrl) {
                            $this->deleteFromImgBb($oldDeleteUrl);
                        }

                        $this->Flash->success(__('Photo saved.'));

                        return $this->redirect(['action' => 'index']);
                    }
                } catch (\RuntimeException $e) {
                    $uploadError = $e->getMessage();
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
            if ($photo->delete_url) {
                $this->deleteFromImgBb($photo->delete_url);
            }

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
