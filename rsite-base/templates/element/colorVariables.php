<?php
/**
 * @var \App\View\AppView $this
 *
 * Overrides the :root custom properties resources/scss/_base.scss compiles
 * in as defaults, with whatever an admin has set via Admin\ColorsController
 * — later same-specificity rules win in CSS, so this only needs to
 * redeclare the properties, not diff them against the defaults. Rendered
 * only in the public layout (see templates/layout/default.php) — the admin
 * layout never includes this, so admin pages always show the built-in
 * defaults regardless of what's customized here.
 */
$colors = $this->siteColors();
?>
<?php if ($colors): ?>
<style>
:root {
<?php foreach ($colors as $slug => $value): ?>
    --color-<?= h(str_replace('_', '-', $slug)) ?>: <?= h($value) ?>;
<?php endforeach; ?>
}
</style>
<?php endif; ?>
