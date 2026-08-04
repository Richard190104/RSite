<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Table\BannersTable;
use Cake\Utility\Text;
use Psr\Http\Message\UploadedFileInterface;

class BannersController extends AppController
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_SIZE = 5 * 1024 * 1024;

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
            $uploadError = $this->uploadError($upload, true);

            if (!$banner->getErrors() && $uploadError === null) {
                $banner->background = $this->storeBackground($upload);

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
            $uploadError = $hasNewFile ? $this->uploadError($upload, false) : null;

            $oldBackground = $banner->background;
            $banner = $Banners->patchEntity($banner, $data);

            if (!$banner->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $banner->background = $this->storeBackground($upload);
                }

                if ($Banners->save($banner)) {
                    if ($hasNewFile) {
                        $oldFile = WWW_ROOT . 'img' . DS . 'banners' . DS . $oldBackground;
                        if (is_file($oldFile)) {
                            unlink($oldFile);
                        }
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
            $file = WWW_ROOT . 'img' . DS . 'banners' . DS . $banner->background;
            if (is_file($file)) {
                unlink($file);
            }

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

    /**
     * Manually validates the raw upload (title/location go through the
     * Table validator, but a Psr UploadedFileInterface can't be marshalled
     * into the 'background' string column, so it never reaches patchEntity).
     */
    private function uploadError(?UploadedFileInterface $file, bool $required): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $required ? __('Please choose a background image.') : null;
        }

        if ($file->getError() !== UPLOAD_ERR_OK) {
            return __('The uploaded file could not be processed.');
        }

        if (!in_array($file->getClientMediaType(), self::ALLOWED_TYPES, true)) {
            return __('The background image must be a JPEG, PNG or WEBP file.');
        }

        if ($file->getSize() > self::MAX_SIZE) {
            return __('The background image must be smaller than 5 MB.');
        }

        return null;
    }

    /**
     * Moves a validated uploaded image into webroot/img/banners and returns
     * the generated filename to store on the entity. The extension is
     * derived from the validated mime type, never from the client-supplied
     * filename.
     */
    private function storeBackground(UploadedFileInterface $file): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$file->getClientMediaType()] ?? 'jpg';

        $targetDir = WWW_ROOT . 'img' . DS . 'banners';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = Text::uuid() . '.' . $extension;
        $file->moveTo($targetDir . DS . $filename);

        return $filename;
    }
}