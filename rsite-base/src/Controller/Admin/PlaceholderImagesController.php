<?php
declare(strict_types=1);

namespace App\Controller\Admin;

class PlaceholderImagesController extends AppController
{
    /**
     * Whether a record with no image of its own gets a random stock photo
     * or a plain "no image" graphic — see PlaceholderImagesTable::random(),
     * the single place that actually reads this setting. A standalone
     * auto-submitting checkbox (see .js-toggle-checkbox/admin-toggle-checkbox.js),
     * embedded on the shared Rcore Configurations screen via
     * templates/element/Admin/automaticImagesToggle.php (see Configure::write(
     * 'Rcore.configurationsExtraElements', ...) in Application.php).
     *
     * The 'automatic_images' row lives in the same `configurations` table
     * Rcore\Model\Table\ConfigurationsTable owns, but that table's
     * validation only accepts hex colors now (it's colors-only from the
     * plugin's point of view) — 'validate' => false here since this value
     * ('0'/'1') is deliberately not a color and was never meant to pass
     * that rule.
     */
    public function toggleAutomatic()
    {
        $this->request->allowMethod(['post']);

        $Configurations = $this->fetchTable('Rcore.Configurations');
        $config = $Configurations->find()->where(['slug' => 'automatic_images'])->firstOrFail();
        $config->value = $config->value === '0' ? '1' : '0';

        if (!$Configurations->save($config, ['fields' => ['value'], 'validate' => false])) {
            $this->Flash->error(__('Could not update the setting.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'plugin' => 'Rcore', 'controller' => 'Configurations', 'action' => 'index']);
    }
}
