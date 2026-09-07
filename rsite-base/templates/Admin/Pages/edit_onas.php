<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<int, array{id: int, title: string, date: string}> $events
 */
$this->assign('title', __('Edit "About us" page'));
$aboutUsText = $page->content['about_us_text'] ?? '';
$description = $page->content['description'] ?? '';
$featuredActivities = array_map('intval', (array)($page->content['featured_activities'] ?? []));
$eventsById = [];
foreach ($events as $event) {
    $eventsById[$event['id']] = $event;
}
?>
<div class="content form-card">
    <?= $this->Form->create($page) ?>
        <div class="form-grid">
            <?= $this->Form->control('content.about_us_text', [
                'type' => 'textarea',
                'label' => __('About us text'),
                'value' => $aboutUsText,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>
            <?= $this->Form->control('content.description', [
                'type' => 'textarea',
                'rows' => 3,
                'label' => __('Description'),
                'value' => $description,
                'container' => ['class' => 'form-grid__full'],
            ]) ?>

            <div class="form-grid__full">
                <label><?= __('Naše aktivity timeline (max 5 activities)') ?></label>
                <div
                    class="activity-picker"
                    data-activity-picker
                    data-events="<?= h(json_encode(array_values($events))) ?>"
                    data-max="5"
                    data-no-results-text="<?= h(__('No matching activities.')) ?>"
                    data-remove-label="<?= h(__('Remove')) ?>"
                >
                    <div class="activity-picker__search-wrap">
                        <input
                            type="text"
                            class="activity-picker__search"
                            data-activity-picker-search
                            placeholder="<?= h(__('Search activities by name…')) ?>"
                            autocomplete="off"
                        >
                        <ul class="activity-picker__results" data-activity-picker-results hidden></ul>
                    </div>

                    <ul class="activity-picker__selected" data-activity-picker-selected>
                        <?php foreach ($featuredActivities as $eventId): ?>
                            <?php if (isset($eventsById[$eventId])): ?>
                                <li class="activity-picker__selected-item" data-id="<?= h($eventId) ?>">
                                    <span class="activity-picker__selected-title"><?= h($eventsById[$eventId]['title']) ?></span>
                                    <span class="activity-picker__selected-date"><?= h($eventsById[$eventId]['date']) ?></span>
                                    <button type="button" class="activity-picker__remove" data-activity-picker-remove aria-label="<?= h(__('Remove')) ?>">&times;</button>
                                    <input type="hidden" name="content[featured_activities][]" value="<?= h($eventId) ?>">
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="form-card__actions">
            <?= $this->Form->button(__('Save')) ?>
        </div>
    <?= $this->Form->end() ?>

    <div class="form-card__hints">
        <p class="form-card__hint">
            <?= __('The image shown next to the "about us" text is managed separately as a banner with the "About us — minibanner" location.') ?>
            <?= $this->Html->link(__('Manage banners'), ['prefix' => 'Admin', 'controller' => 'Banners', 'action' => 'index']) ?>
        </p>
        <p class="form-card__hint">
            <?= __('The timeline is shown on the public page ordered by activity date, oldest first — not by the order selected here.') ?>
        </p>
    </div>
</div>

<?= $this->Html->script('admin-activity-picker') ?>
