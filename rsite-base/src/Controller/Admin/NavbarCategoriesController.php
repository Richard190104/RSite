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
            ->orderBy(['NavbarCategories.position' => 'ASC'])
            ->all();

        $this->set(compact('categories'));
    }

    /**
     * Reorders NavbarCategories via drag-and-drop on the index listing —
     * see webroot/js/admin-drag-reorder.js. Expects an ordered array of
     * ids in `order` and writes 0-based positions to match, so a later
     * ->orderBy(['position' => 'ASC']) always reflects exactly the order
     * the admin last dropped them in.
     */
    public function reorder()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success']);

        $Categories = $this->fetchTable('NavbarCategories');
        $ids = array_map('intval', (array)$this->request->getData('order'));

        foreach ($ids as $position => $id) {
            $Categories->updateAll(['position' => $position], ['id' => $id]);
        }

        $this->set('success', true);

        return null;
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
                // page_ids arrives in whatever order webroot/js/admin-drag-reorder.js
                // last left the checkboxes in the DOM — that's the admin's
                // chosen display order, not just a selection set, so array
                // index doubles as the position to persist.
                $selectedIds = array_map('intval', (array)$this->request->getData('page_ids'));

                $Pages->updateAll(
                    ['navbar_category_id' => null],
                    ['navbar_category_id' => $category->id, 'id NOT IN' => $selectedIds ?: [0]],
                );

                foreach ($selectedIds as $position => $pageId) {
                    $Pages->updateAll(
                        ['navbar_category_id' => $category->id, 'position' => $position],
                        ['id' => $pageId],
                    );
                }

                $this->Flash->success(__('Category saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save the category, check the errors below.'));
        }

        // Selected pages first (in their current position order), then the
        // rest — so the drag-and-drop list opens already showing this
        // category's pages up top in the order they'll display, with
        // everything else available below to add.
        // ->toArray() up front — the query result is a single-pass cursor,
        // so reusing it both for extract() below and for the append() merge
        // would silently yield nothing the second time round.
        $selectedPages = $Pages->find()
            ->where(['navbar_category_id' => $category->id])
            ->orderBy(['position' => 'ASC'])
            ->all()
            ->toArray();
        $selectedPageIds = array_map(fn ($page) => $page->id, $selectedPages);

        $otherPages = $Pages->find()
            ->where(['OR' => [
                'navbar_category_id !=' => $category->id,
                'navbar_category_id IS' => null,
            ]])
            ->orderBy(['title' => 'ASC'])
            ->all()
            ->toArray();

        $allPages = array_merge($selectedPages, $otherPages);

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