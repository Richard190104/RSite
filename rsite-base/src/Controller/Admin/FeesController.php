<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Table\FeesTable;

/**
 * "Poplatky" is managed entirely from Admin\PagesController::editPoplatky()
 * rather than as its own sidebar entry — same pattern as
 * StockingDocuments/CatchDocuments on the "zarybnenie" page. Fees stays a
 * full CRUD controller, this page just lists the fees (grouped by category)
 * and links into add/edit/delete here.
 */
class FeesController extends AppController
{
    public function add()
    {
        $Fees = $this->fetchTable('Fees');
        $fee = $Fees->newEmptyEntity();

        if ($this->request->is('post')) {
            $fee = $Fees->patchEntity($fee, $this->request->getData());

            if ($Fees->save($fee)) {
                $this->Flash->success(__('Fee saved.'));

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'poplatky']);
            }
            $this->Flash->error(__('Could not save the fee, check the errors below.'));
        }

        $this->set(compact('fee'));
        $this->set('categoryOptions', FeesTable::CATEGORIES);

        return null;
    }

    public function edit(?string $id = null)
    {
        $Fees = $this->fetchTable('Fees');
        $fee = $Fees->get($id);

        if ($this->request->is(['post', 'put'])) {
            $fee = $Fees->patchEntity($fee, $this->request->getData());

            if ($Fees->save($fee)) {
                $this->Flash->success(__('Fee saved.'));

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'poplatky']);
            }
            $this->Flash->error(__('Could not save the fee, check the errors below.'));
        }

        $this->set(compact('fee'));
        $this->set('categoryOptions', FeesTable::CATEGORIES);

        return null;
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $Fees = $this->fetchTable('Fees');
        $fee = $Fees->get($id);

        if ($Fees->delete($fee)) {
            $this->Flash->success(__('Fee deleted.'));
        } else {
            $this->Flash->error(__('Could not delete the fee.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Pages', 'action' => 'edit', 'poplatky']);
    }

    /**
     * Reorders Fees via drag-and-drop within one category's table on the
     * "Poplatky" page edit screen — see webroot/js/admin-drag-reorder.js.
     * Expects an ordered array of ids in `order`, all sharing the same
     * category (sent as `parentId`, same request shape the shared JS already
     * uses for Admin\CategoriesController::reorder() — here it just means
     * "which category's list this is" rather than a parent category id),
     * and writes 0-based positions to match. `category` is included in the
     * WHERE (not just used to pick the list) so a stale or tampered request
     * can only ever reorder ids within the category it claims.
     */
    public function reorder()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', ['success']);

        $Fees = $this->fetchTable('Fees');
        $ids = array_map('intval', (array)$this->request->getData('order'));
        $category = (string)$this->request->getData('parentId');

        foreach ($ids as $position => $id) {
            $Fees->updateAll(['position' => $position], ['id' => $id, 'category' => $category]);
        }

        $this->set('success', true);

        return null;
    }
}
