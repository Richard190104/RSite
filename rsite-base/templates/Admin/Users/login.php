<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Log in'));
$this->disableAutoLayout();
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= __('Admin log in') ?></title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css(['normalize.min', 'milligram.min', 'admin']) ?>
</head>
<body>
    <div class="admin-login">
        <div class="admin-login__box content">
            <h1><?= __('Admin log in') ?></h1>
            <?= $this->Flash->render() ?>
            <?= $this->Form->create(null) ?>
                <?= $this->Form->control('username', ['label' => __('Username'), 'autofocus' => true]) ?>
                <?= $this->Form->control('password', ['label' => __('Password')]) ?>
                <?= $this->Form->button(__('Log in')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</body>
</html>