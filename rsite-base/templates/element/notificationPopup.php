<?php
/**
 * @var \App\View\AppView $this
 *
 * Small top-right popup shown once per page load when at least one active
 * notification is flagged settings.show_as_popup (see
 * AppView::SiteInfoTrait::popupNotification() — picks one at random when
 * more than one qualifies). Purely presentational: dismissing it just
 * hides it in the DOM, nothing is persisted, so it reappears on the next
 * page load the same way the bell dropdown does.
 */
$notification = $this->popupNotification();

if ($notification === null) {
    return;
}
?>
<div class="notification-popup" role="status">
    <button type="button" class="notification-popup__close" aria-label="<?= __('Close') ?>">&times;</button>
    <img class="notification-popup__image" src="<?= h($this->Url->build('/img/notifications/' . $notification->image)) ?>" alt="">
    <div class="notification-popup__text">
        <span class="notification-popup__title"><?= h($notification->title) ?></span>
        <p class="notification-popup__description"><?= h($notification->description) ?></p>
    </div>
</div>
<?= $this->Html->script('notification-popup') ?>
