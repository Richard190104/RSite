<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class ConfigurationsController extends AppController
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

        $Configurations = $this->fetchTable('Configurations');
        $colors = $Configurations->find()->orderBy(['id' => 'ASC'])->all()->indexBy('slug')->toArray();
        $automaticImages = $Configurations->automaticImagesEnabled();

        $this->set(compact('colors', 'automaticImages'));

        return null;
    }

    /**
     * Whether a record with no image of its own gets a random stock photo
     * or a plain "no image" graphic — see PlaceholderImagesTable::random(),
     * the single place that actually reads this setting. A standalone
     * auto-submitting checkbox (see .js-toggle-checkbox/admin-toggle-checkbox.js),
     * same pattern as Admin\BannersController::toggleEnabled(), rather than
     * bundled into the colors form save — it's an unrelated setting, not
     * part of the palette, even though it lives in the same table now.
     */
    public function toggleAutomaticImages()
    {
        $this->request->allowMethod(['post']);

        $Configurations = $this->fetchTable('Configurations');
        $config = $Configurations->find()->where(['slug' => 'automatic_images'])->firstOrFail();
        $config->value = $config->value === '0' ? '1' : '0';

        if (!$Configurations->save($config, ['fields' => ['value']])) {
            $this->Flash->error(__('Could not update the setting.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Resets the whole palette to its original defaults
     * (ConfigurationsTable::defaults()) in one action — not one color at a
     * time, since a partial reset would leave the palette in a mismatched,
     * in-between state. Never touches 'automatic_images' — that's not part
     * of the palette (see ConfigurationsTable::defaults()'s own docblock).
     */
    public function reset()
    {
        $this->request->allowMethod(['post']);

        $errors = $this->saveAll($this->fetchTable('Configurations')->defaults());

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
        $Configurations = $this->fetchTable('Configurations');
        $configurations = $Configurations->find()->where(['slug IN' => array_keys($data)])->all()->indexBy('slug')->toArray();

        $errors = [];
        foreach ($configurations as $slug => $configuration) {
            $configuration = $Configurations->patchEntity($configuration, ['value' => $data[$slug] ?? ''], ['fields' => ['value']]);
            if ($Configurations->save($configuration)) {
                continue;
            }

            $errors[] = $slug . ': ' . implode(' ', $configuration->getError('value'));
        }

        return $errors;
    }
}
