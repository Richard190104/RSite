<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h($this->request->getAttribute('csrfToken')) ?>">
    <title>Admin: <?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'admin']) ?>
    <?= $this->Html->script('admin-helper-widget') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

</head>
<body>
    <div class="admin-shell">
       <?= $this->element('Admin/sidebar') ?>
        <main class="admin-main">
            <div class="admin-topbar">
                <h1><?= $this->fetch('title') ?></h1>
            </div>
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </main>
    </div>
    <?= $this->element('Admin/aiChat', $this->get('aiChatFields', [])) ?>
</body>
</html>