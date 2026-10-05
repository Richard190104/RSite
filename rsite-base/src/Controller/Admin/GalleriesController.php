<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;
use Psr\Http\Message\UploadedFileInterface;
use Rcore\Controller\Admin\ImageUploadTrait;
use Rcore\Controller\Admin\ImgBbUploadTrait;

/**
 * Migrated onto the generic Resource pattern for index/edit/delete (trials
 * the ImgBB upload backend — see ResourceController's own docs) — but
 * add() stays a real, hand-written override. It accepts several files at
 * once (the form's file input has the `multiple` attribute) and creates
 * one Gallery row per file, which has no place in the generic "one form
 * submission = one entity" shape every other action assumes.
 */
class GalleriesController extends \Rcore\Controller\Admin\ResourceController
{
    use ImageUploadTrait;
    use ImgBbUploadTrait;

    protected function resourceConfig(?EntityInterface $item = null): array
    {
        $categories = $this->fetchTable('Rcore.Categories')
            ->find()
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->combine('id', 'title')
            ->toArray();

        return [
            'table' => 'Rcore.Galleries',
            'title' => __('Galleries'),
            'list' => [
                'contain' => ['Categories'],
                'order' => ['Galleries.created' => 'DESC'],
                'columns' => [
                    ['field' => 'image', 'label' => __('Image'), 'type' => 'image', 'backend' => 'imgbb'],
                    ['field' => 'category.title', 'label' => __('Category')],
                ],
            ],
            'form' => [
                'fields' => [
                    [
                        'name' => 'image', 'type' => 'image', 'label' => __('Image'),
                        'backend' => 'imgbb', 'deleteUrlField' => 'delete_url',
                    ],
                    ['name' => 'text', 'type' => 'text', 'label' => __('Caption')],
                    [
                        'name' => 'category_id', 'type' => 'select', 'label' => __('Category'),
                        'options' => $categories, 'empty' => __('— none —'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Accepts one or more files at once (the form's file input has the
     * `multiple` attribute and name="image[]") — each valid file becomes
     * its own Gallery row under the single category picked in the form.
     */
    public function add()
    {
        $Galleries = $this->fetchTable('Rcore.Galleries');
        $photo = $Galleries->newEmptyEntity();
        $categories = $this->resourceConfig()['form']['fields'][2]['options'];

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $categoryId = $data['category_id'] ?? null;

            /** @var array<\Psr\Http\Message\UploadedFileInterface> $uploads */
            $uploads = (array)($data['image'] ?? []);
            $uploads = array_filter(
                $uploads,
                fn ($upload) => $upload instanceof UploadedFileInterface
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

        $this->set(compact('photo', 'categories'));
        $this->viewBuilder()->setTemplatePath('Admin/Galleries');
        $this->render('add');

        return null;
    }
}
