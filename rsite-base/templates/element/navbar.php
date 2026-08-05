<?php
/**
 * @var \App\View\AppView $this
 *
 * For now this just fetches navbar categories with their assigned pages
 * from the admin (NavbarCategories -> Pages) — no markup/design yet, same
 * minimal "just get the data there" approach as Pages::home().
 */
use Cake\ORM\TableRegistry;

$navbarCategories = TableRegistry::getTableLocator()->get('NavbarCategories')
    ->find()
    ->contain(['Pages'])
    ->orderBy(['NavbarCategories.title' => 'ASC'])
    ->all();
?>
<pre><?= h(print_r($navbarCategories->toArray(), true)) ?></pre>