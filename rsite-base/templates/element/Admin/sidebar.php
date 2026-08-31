<?php
/**
 * @var \App\View\AppView $this
 */

// Tu sa pridavaju admin kategorie do sidebaru. Later mozno by to mohli byt v db, aj nejaky admin config na pridavanie. Zatial staci toto
// Kluc je nazov controllera (pre routing), hodnota je prelozitelny popisok.
$adminCategories = [
    'Dashboard' => __('Dashboard'),
    'Texts' => __('Texts'),
    'Banners' => __('Banners'),
    'NavbarCategories' => __('Navbar categories'),
    'Pages' => __('Pages'),
    'News' => __('News'),
    'Categories' => __('Categories'),
    'Events' => __('Events'),
    'Galleries' => __('Galleries'),
    'Logos' => __('Logos'),
    'Notifications' => __('Notifications'),
];
$currentController = $this->getRequest()->getParam('controller');
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar__title">
        <a href="<?= $this->Url->build('/admin') ?>">Admin</a>
    </div>
    <nav class="admin-sidebar__nav">
        <?php foreach ($adminCategories as $controller => $label): ?>
            <a
                href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => $controller, 'action' => 'index']) ?>"
                class="admin-sidebar__category<?= $currentController === $controller ? ' is-active' : '' ?>"
            ><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <nav class="admin-sidebar__nav admin-sidebar__nav--bottom">
        <a
            href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout']) ?>"
            class="admin-sidebar__category"
        ><?= __('Log out') ?></a>
    </nav>
</aside>