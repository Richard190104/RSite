<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Table\BannersTable;
use Psr\Http\Message\UploadedFileInterface;

class BannersController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        $banners = $this->fetchTable('Banners')->find()->orderBy(['location' => 'ASC'])->all();
        $this->set(compact('banners'));
    }

    public function add()
    {
        $Banners = $this->fetchTable('Banners');
        $banner = $Banners->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['background'] ?? null;
            unset($data['background']);

            $banner = $Banners->patchEntity($banner, $data);
            $uploadError = $this->imageUploadError($upload, true);

            if (!$banner->getErrors() && $uploadError === null) {
                $banner->background = $this->storeImageUpload($upload, 'banners');

                if ($Banners->save($banner)) {
                    $this->Flash->success(__('Banner saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the banner, check the errors below.'));
        }

        $this->set('banner', $banner);
        $this->set('locations', $this->pageOptions());

        return null;
    }

    public function edit(?string $id = null)
    {
        $Banners = $this->fetchTable('Banners');
        $banner = $Banners->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['background'] ?? null;
            unset($data['background']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldBackground = $banner->background;
            $banner = $Banners->patchEntity($banner, $data);

            if (!$banner->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $banner->background = $this->storeImageUpload($upload, 'banners');
                }

                if ($Banners->save($banner)) {
                    if ($hasNewFile) {
                        $this->deleteImageUpload('banners', $oldBackground);
                    }

                    $this->Flash->success(__('Banner saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the banner, check the errors below.'));
        }

        $this->set('banner', $banner);
        $this->set('locations', $this->pageOptions());

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Banners = $this->fetchTable('Banners');
        $banner = $Banners->get($id);

        if ($Banners->delete($banner)) {
            $this->deleteImageUpload('banners', $banner->background);

            $this->Flash->success(__('Banner deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the banner.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Options for the location select, sourced from the pages table so it
     * always matches the actual pages/sections in the project.
     *
     * @return array<string, string>
     */
    private function pageOptions(): array
    {
        $pages = $this->fetchTable('Pages')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('slug', 'title')
            ->toArray();

        return $pages + BannersTable::VIRTUAL_LOCATIONS;
    }
}