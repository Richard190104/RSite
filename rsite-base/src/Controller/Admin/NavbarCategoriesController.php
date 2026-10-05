<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Datasource\EntityInterface;

/**
 * Migrated onto the generic Resource pattern for index/add/delete (trials
 * the flat 'reorder' list preset) — but edit() stays a real, hand-written
 * override. Its page-picker widget updates a DIFFERENT table's rows
 * (Pages' navbar_category_id/position) based on a custom drag-reorder
 * selection UI, which has no place in the generic field-config system —
 * exactly the "this resource has outgrown the generic controller for this
 * one action" case ResourceController's own docblock calls out. Its own
 * template lives at templates/Admin/NavbarCategories/edit.php (the parent's
 * templatePath is pinned to Admin/Resource, so this explicitly points back
 * at this controller's own directory before rendering).
 */
class NavbarCategoriesController extends \Rcore\Controller\Admin\ResourceController
{
    protected function resourceConfig(?EntityInterface $item = null): array
    {
        return [
            'table' => 'Rcore.NavbarCategories',
            'title' => __('Navbar categories'),
            'list' => [
                'preset' => 'reorder',
                'contain' => ['Pages'],
                'columns' => [
                    ['field' => 'title', 'label' => __('Title')],
                    ['field' => 'pages', 'label' => __('Pages'), 'type' => 'count'],
                ],
            ],
            'form' => [
                'fields' => [
                    ['name' => 'title', 'type' => 'text', 'label' => __('Title')],
                ],
            ],
        ];
    }

    public function edit(?string $id = null)
    {
        $Categories = $this->fetchTable('Rcore.NavbarCategories');
        // Bare 'Pages', not 'Rcore.Pages' — see NavbarCategoriesTable's own
        // comment on its hasMany('Pages', ...).
        $Pages = $this->fetchTable('Pages');
        $category = $Categories->get($id);

        if ($this->request->is(['post', 'put'])) {
            // Explicit fieldList — NavbarCategory's own $_accessible is now
            // wildcard (see its own docblock), so this form's raw request
            // data must be restricted here instead, same job
            // Rcore\Controller\Admin\ResourceController::formData() does
            // for every ResourceController-driven form.
            $category = $Categories->patchEntity($category, $this->request->getData(), ['fieldList' => ['title']]);

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
        $this->viewBuilder()->setTemplatePath('Admin/NavbarCategories');
        $this->render('edit');

        return null;
    }
}
