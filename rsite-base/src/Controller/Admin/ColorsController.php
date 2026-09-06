<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class ColorsController extends AppController
{
    /**
     * One page, one form, every color at once — rows are never added or
     * removed here (that only happens via a migration), and editing them
     * one at a time (like Admin\TextsController does for plain text) would
     * make it hard to see how the whole palette works together.
     */
    public function index()
    {
        if ($this->request->is(['post', 'put'])) {
            $errors = $this->saveAll((array)$this->request->getData('colors'));

            if (!$errors) {
                $this->Flash->success(__('Colors saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Could not save: {0}', implode(', ', $errors)));
        }

        $colors = $this->fetchTable('Colors')->find()->orderBy(['id' => 'ASC'])->all()->indexBy('slug')->toArray();

        $this->set(compact('colors'));

        return null;
    }

    /**
     * Resets the whole palette to its original defaults (ColorsTable::defaults())
     * in one action — not one color at a time, since a partial reset would
     * leave the palette in a mismatched, in-between state.
     */
    public function reset()
    {
        $this->request->allowMethod(['post']);

        $errors = $this->saveAll($this->fetchTable('Colors')->defaults());

        if (!$errors) {
            $this->Flash->success(__('Colors reset to defaults.'));
        } else {
            $this->Flash->error(__('Could not reset: {0}', implode(', ', $errors)));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Saves every slug => value pair in $data against its existing row,
     * skipping slugs that don't already exist (rows are only ever created
     * by a migration). Shared by index()'s form save and reset()'s
     * all-at-once revert.
     *
     * @param array<string, string> $data
     * @return array<int, string> Error messages, one per failed slug — empty when everything saved.
     */
    private function saveAll(array $data): array
    {
        $Colors = $this->fetchTable('Colors');
        $colors = $Colors->find()->where(['slug IN' => array_keys($data)])->all()->indexBy('slug')->toArray();

        $errors = [];
        foreach ($colors as $slug => $color) {
            $color = $Colors->patchEntity($color, ['value' => $data[$slug] ?? ''], ['fields' => ['value']]);
            if ($Colors->save($color)) {
                continue;
            }

            $errors[] = $slug . ': ' . implode(' ', $color->getError('value'));
        }

        return $errors;
    }
}
