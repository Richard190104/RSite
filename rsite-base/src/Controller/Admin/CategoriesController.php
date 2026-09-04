<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class CategoriesController extends AppController
{
    use ImageUploadTrait;

    public function index(): void
    {
        // Top-level categories with their children eager-loaded (ordered
        // by position, same as the child list itself) — the template
        // renders one drag-reorder list per parent (top-level categories
        // among themselves, then each parent's children among themselves),
        // since positions are only meaningful scoped to a shared parent_id
        // (see reorder()).
        $categories = $this->fetchTable('Categories')
            ->find()
            ->where(['parent_id IS' => null])
            ->contain(['ChildCategories' => fn ($q) => $q->orderBy(['ChildCategories.position' => 'ASC'])])
            ->orderBy(['Categories.position' => 'ASC'])
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Reorders Categories via drag-and-drop on the index listing — see
     * webroot/js/admin-drag-reorder.js. Expects an ordered array of ids in
     * `order`, all sharing the same `parentId` (null for top-level), and
     * writes 0-based positions to match, same pattern as
     * NavbarCategoriesController::reorder(). `parentId` is included in the
     * WHERE (not just used to pick the list) so a stale or tampered
     * request can only ever move ids within the parent it claims, never
     * reassign a category to a different parent's position sequence.
     */
    public function reorder()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success']);

        $Categories = $this->fetchTable('Categories');
        $ids = array_map('intval', (array)$this->request->getData('order'));
        $parentId = $this->request->getData('parentId');
        $parentId = $parentId === null || $parentId === '' ? null : (int)$parentId;
        $parentCondition = $parentId === null ? ['parent_id IS' => null] : ['parent_id' => $parentId];

        foreach ($ids as $position => $id) {
            $Categories->updateAll(['position' => $position], ['id' => $id] + $parentCondition);
        }

        $this->set('success', true);

        return null;
    }

    public function add()
    {
        $Categories = $this->fetchTable('Categories');
        $category = $Categories->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasFile ? $this->imageUploadError($upload, false) : null;

            $category = $Categories->patchEntity($category, $data);

            if (!$category->getErrors() && $uploadError === null) {
                if ($hasFile) {
                    $category->image = $this->storeImageUpload($upload, 'categories');
                }

                if ($Categories->save($category)) {
                    $this->Flash->success(__('Category saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the category, check the errors below.'));
        }

        $this->set('category', $category);
        $this->set('parentOptions', $this->parentOptions());

        return null;
    }

    public function edit(?string $id = null)
    {
        $Categories = $this->fetchTable('Categories');
        $category = $Categories->get($id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            /** @var \Psr\Http\Message\UploadedFileInterface|null $upload */
            $upload = $data['image'] ?? null;
            unset($data['image']);

            $hasNewFile = $upload !== null && $upload->getError() !== UPLOAD_ERR_NO_FILE;
            $uploadError = $hasNewFile ? $this->imageUploadError($upload, false) : null;

            $oldImage = $category->image;
            $category = $Categories->patchEntity($category, $data);

            if (!$category->getErrors() && $uploadError === null) {
                if ($hasNewFile) {
                    $category->image = $this->storeImageUpload($upload, 'categories');
                }

                if ($Categories->save($category)) {
                    if ($hasNewFile && $oldImage) {
                        $this->deleteImageUpload('categories', $oldImage);
                    }

                    $this->Flash->success(__('Category saved.'));

                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($uploadError !== null) {
                $this->Flash->error($uploadError);
            }
            $this->Flash->error(__('Could not save the category, check the errors below.'));
        }

        $this->set('category', $category);
        $this->set('parentOptions', $this->parentOptions((int)$category->id));

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Categories = $this->fetchTable('Categories');
        $category = $Categories->get($id);

        if ($Categories->delete($category)) {
            if ($category->image) {
                $this->deleteImageUpload('categories', $category->image);
            }

            $this->Flash->success(__('Category deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the category.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Categories that can be picked as a parent. A category can't be its
     * own parent, so it's excluded when editing.
     *
     * @return array<int, string>
     */
    private function parentOptions(?int $excludeId = null): array
    {
        $query = $this->fetchTable('Categories')->find()->orderBy(['title' => 'ASC']);
        if ($excludeId !== null) {
            $query->where(['id !=' => $excludeId]);
        }

        return $query->all()->combine('id', 'title')->toArray();
    }
}
