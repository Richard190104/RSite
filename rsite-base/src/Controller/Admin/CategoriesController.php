<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class CategoriesController extends AppController
{
    public function index(): void
    {
        $categories = $this->fetchTable('Categories')
            ->find()
            ->contain(['ParentCategories'])
            ->orderBy(['Categories.title' => 'ASC'])
            ->all();

        $this->set(compact('categories'));
    }

    public function add()
    {
        $Categories = $this->fetchTable('Categories');
        $category = $Categories->newEmptyEntity();

        if ($this->request->is('post')) {
            $category = $Categories->patchEntity($category, $this->request->getData());

            if ($Categories->save($category)) {
                $this->Flash->success(__('Category saved.'));

                return $this->redirect(['action' => 'index']);
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
            $category = $Categories->patchEntity($category, $this->request->getData());

            if ($Categories->save($category)) {
                $this->Flash->success(__('Category saved.'));

                return $this->redirect(['action' => 'index']);
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