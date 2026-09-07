<?php
/**
 * @var \App\View\AppView $this
 *
 * Small top-right popup shown when at least one active notification is
 * flagged settings.show_as_popup (see AppView::SiteInfoTrait::
 * popupNotification() — picks one at random when more than one qualifies).
 * Server-rendered on every page load regardless, but webroot/js/
 * notification-popup.js only actually displays it the first time a given
 * notification id is seen in this browser tab's sessionStorage — so
 * navigating between pages within one visit doesn't keep re-showing it,
 * while a genuinely new notification (different id) still pops up once.
 */
$notification = $this->popupNotification();

if ($notification === null) {
    return;
}
?>
<div class="notification-popup" role="status" data-notification-id="<?= h($notification->id) ?>">
    <button type="button" class="notification-popup__close close-btn" aria-label="<?= __('Close') ?>">&times;</button>
    <img class="notification-popup__image" src="<?= h($this->Url->build('/img/notifications/' . $notification->image)) ?>" alt="">
    <div class="notification-popup__text">
        <span class="notification-popup__title"><?= h($notification->title) ?></span>
        <p class="notification-popup__description"><?= h($notification->description) ?></p>
    </div>
</div>
<?= $this->Html->script('notification-popup') ?>
