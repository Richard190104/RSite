<?php
/**
 * @var \App\View\AppView $this
 *
 * Shared news detail modal — filled by webroot/js/news-modal.js from
 * [data-news-card] attributes (used on /news and the homepage carousel).
 */
?>
<div class="p-news__modal" data-news-modal>
    <div class="p-news__modal-frame">
        <?= $this->element('modalCloseBtn', ['extraClass' => 'p-news__modal-close']) ?>
        <img class="p-news__modal-image" alt="" hidden>
        <div class="p-news__modal-body">
            <div class="p-news__modal-meta-row">
                <span class="p-news__modal-category" hidden></span>
                <span class="p-news__modal-meta" hidden>
                    <svg class="p-news__modal-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="p-news__modal-meta-text"></span>
                </span>
            </div>
            <h3 class="p-news__modal-title"></h3>
            <hr class="p-news__modal-divider">
            <p class="p-news__modal-description"></p>
        </div>
        <div class="p-news__modal-poster" hidden></div>
    </div>
</div>
<?= $this->Html->script('news-modal') ?>
