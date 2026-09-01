<?php
/**
 * @var \App\View\AppView $this
 */

use App\Controller\Admin\AppController;

// The section list itself lives in AppController::adminCategories() — the
// single source of truth also read by Admin\AiController, so the chat
// assistant's list of "where can I do X" sections never drifts from what's
// actually in this sidebar. Update that method, not this file, to add one.
$adminCategories = AppController::adminCategories();
$currentController = $this->getRequest()->getParam('controller');
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar__title">
        <a href="<?= $this->Url->build('/admin') ?>">Admin</a>
    </div>
    <nav class="admin-sidebar__nav">
        <?php foreach ($adminCategories as $controller => $category): ?>
            <a
                href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => $controller, 'action' => 'index']) ?>"
                class="admin-sidebar__category<?= $currentController === $controller ? ' is-active' : '' ?>"
            ><?= $category['label'] ?></a>
        <?php endforeach; ?>
    </nav>
    <nav class="admin-sidebar__nav admin-sidebar__nav--bottom">
        <a
            href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout']) ?>"
            class="admin-sidebar__category"
        ><?= __('Log out') ?></a>
    </nav>
</aside>