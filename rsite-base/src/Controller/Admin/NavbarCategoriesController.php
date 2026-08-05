<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class NavbarCategoriesController extends AppController
{
    public function index(): void
    {
        $categories = $this->fetchTable('NavbarCategories')
            ->find()
            ->contain(['Pages'])
            ->orderBy(['NavbarCategories.title' => 'ASC'])
            ->all();

        $this->set(compact('categories'));
    }

    public function add()
    {
        $Categories = $this->fetchTable('NavbarCategories');
        $category = $Categories->newEmptyEntity();

        if ($this->request->is('post')) {
            $category = $Categories->patchEntity($category, $this->request->getData());

            if ($Categories->save($category)) {
                $this->Flash->success(__('Category saved.'));

                return $this->redirect(['action' => 'edit', $category->id]);
            }

            $this->Flash->error(__('Could not save the category, check the errors below.'));
        }

        $this->set(compact('category'));

        return null;
    }

    public function edit(?string $id = null)
    {
        $Categories = $this->fetchTable('NavbarCategories');
        $Pages = $this->fetchTable('Pages');
        $category = $Categories->get($id);

        if ($this->request->is(['post', 'put'])) {
            $category = $Categories->patchEntity($category, $this->request->getData());

            if ($Categories->save($category)) {
                $selectedIds = array_map('intval', (array)$this->request->getData('page_ids'));

                if ($selectedIds !== []) {
                    $Pages->updateAll(
                        ['navbar_category_id' => $category->id],
                        ['id IN' => $selectedIds],
                    );
                }
                $Pages->updateAll(
                    ['navbar_category_id' => null],
                    ['navbar_category_id' => $category->id, 'id NOT IN' => $selectedIds ?: [0]],
                );

                $this->Flash->success(__('Category saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the category, check the errors below.'));
        }

        $allPages = $Pages->find()->orderBy(['title' => 'ASC'])->all();
        $selectedPageIds = $Pages->find()
            ->select(['id'])
            ->where(['navbar_category_id' => $category->id])
            ->all()
            ->extract('id')
            ->toArray();

        $this->set(compact('category', 'allPages', 'selectedPageIds'));

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Categories = $this->fetchTable('NavbarCategories');
        $category = $Categories->get($id);

        if ($Categories->delete($category)) {
            $this->Flash->success(__('Category deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the category.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}