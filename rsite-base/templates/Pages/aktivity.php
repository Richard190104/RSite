<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Page $page
 * @var array<\App\Model\Entity\Event> $upcomingEvents
 * @var array<\App\Model\Entity\Category> $categories
 * @var array<int, array<string, mixed>> $calendarEvents
 */
$this->assign('title', __($page->title));

$activityIcons = [
    '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    '<path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>',
    '<path d="M12 22c4-4 8-7.5 8-12a8 8 0 1 0-16 0c0 4.5 4 8 8 12Z"/><circle cx="12" cy="10" r="3"/>',
    '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
];
?>
<section class="p-aktivity">
    <div class="p-aktivity__body">
        <section class="p-aktivity__section">
            <h2 class="p-aktivity__heading"><?= __('Upcoming events') ?></h2>

            <?php if ($upcomingEvents): ?>
                <div class="p-aktivity__upcoming">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <article class="p-aktivity__event-card">
                            <div class="p-aktivity__event-media">
                                <?php if ($event->image): ?>
                                    <?= $this->Html->image('/img/events/' . $event->image, [
                                        'alt' => $event->title,
                                        'class' => 'p-aktivity__event-image',
                                    ]) ?>
                                <?php endif; ?>
                                <?php if ($event->date): ?>
                                    <span class="p-aktivity__event-date">
                                        <span class="p-aktivity__event-day"><?= h($event->date->i18nFormat('dd')) ?></span>
                                        <span class="p-aktivity__event-month"><?= h($event->date->i18nFormat('MMM yyyy')) ?></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="p-aktivity__event-body">
                                <?php if ($event->category): ?>
                                    <span class="p-aktivity__event-category"><?= h(__($event->category->title)) ?></span>
                                <?php endif; ?>
                                <h3 class="p-aktivity__event-title"><?= h($event->title) ?></h3>
                                <p class="p-aktivity__event-text"><?= h($event->description) ?></p>
                                <button
                                    type="button"
                                    class="p-aktivity__event-more"
                                    hidden
                                    data-more-label="<?= h(__('Read more')) ?>"
                                    data-less-label="<?= h(__('Show less')) ?>"
                                >
                                    <?= __('Read more') ?>
                                </button>
                                <?php if ($event->location || $event->time): ?>
                                    <p class="p-aktivity__event-meta">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/>
                                            <circle cx="12" cy="10" r="2.5"/>
                                        </svg>
                                        <span>
                                            <?php if ($event->location): ?>
                                                <?= h($event->location) ?>
                                            <?php endif; ?>
                                            <?php if ($event->location && $event->time): ?> · <?php endif; ?>
                                            <?php if ($event->time): ?>
                                                <?= h($event->time) ?>
                                            <?php endif; ?>
                                        </span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="p-aktivity__empty"><?= __('No upcoming events yet.') ?></p>
            <?php endif; ?>
        </section>

        <?php if ($categories): ?>
            <section class="p-aktivity__section">
                <h2 class="p-aktivity__heading"><?= __('Our activities') ?></h2>
                <div class="p-aktivity__types">
                    <?php foreach ($categories as $index => $category): ?>
                        <article class="p-aktivity__type-card">
                            <span class="p-aktivity__type-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <?= $activityIcons[$index % count($activityIcons)] ?>
                                </svg>
                            </span>
                            <h3 class="p-aktivity__type-title"><?= h(__($category->title)) ?></h3>
                            <p class="p-aktivity__type-text">
                                <?= __('Events and activities in this category.') ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="p-aktivity__section">
            <h2 class="p-aktivity__heading"><?= __('Activities calendar') ?></h2>
            <div
                class="p-aktivity__calendar"
                id="aktivity-calendar"
                data-events="<?= h(json_encode($calendarEvents, JSON_UNESCAPED_UNICODE)) ?>"
                data-locale="sk-SK"
                data-empty-label="<?= h(__('No events this month.')) ?>"
                data-day-empty-label="<?= h(__('No events on this day.')) ?>"
                data-closest-label="<?= h(__('Closest events')) ?>"
                data-weekdays="<?= h(json_encode([
                    __('Mon'),
                    __('Tue'),
                    __('Wed'),
                    __('Thu'),
                    __('Fri'),
                    __('Sat'),
                    __('Sun'),
                ], JSON_UNESCAPED_UNICODE)) ?>"
            >
                <div class="p-aktivity__calendar-main">
                    <div class="p-aktivity__calendar-toolbar">
                        <button type="button" class="p-aktivity__calendar-nav" data-cal-prev aria-label="<?= __('Previous month') ?>">&larr;</button>
                        <h3 class="p-aktivity__calendar-month" data-cal-month></h3>
                        <button type="button" class="p-aktivity__calendar-nav" data-cal-next aria-label="<?= __('Next month') ?>">&rarr;</button>
                    </div>
                    <div class="p-aktivity__calendar-weekdays" data-cal-weekdays></div>
                    <div class="p-aktivity__calendar-grid" data-cal-grid></div>
                </div>
                <aside class="p-aktivity__calendar-aside">
                    <h3 class="p-aktivity__calendar-aside-title" data-cal-aside-title><?= __('Closest events') ?></h3>
                    <ul class="p-aktivity__calendar-list" data-cal-list></ul>
                </aside>
            </div>
        </section>
    </div>
</section>
<?= $this->Html->script('aktivity-calendar') ?>
