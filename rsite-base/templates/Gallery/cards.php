<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\ResultSet<\App\Model\Entity\Category>|null $categories Top-level categories — root view only.
 * @var \App\Model\Entity\Category|null $parent Category being viewed — category detail view only.
 * @var \Cake\ORM\ResultSet<\App\Model\Entity\Category>|null $subcategories Direct children of $parent — category detail view only.
 * @var \Cake\ORM\ResultSet<\App\Model\Entity\Gallery>|null $photos Photos of $parent and its subcategories combined — category detail view only.
 */
$parent ??= null;
$this->assign('title', $parent !== null ? h($parent->title) : __('Gallery categories'));
?>
<section class="p-gallery">
    <div class="p-gallery__body">
        <?php if ($parent === null): ?>
            <h1 class="p-gallery__title"><?= __('Gallery categories') ?></h1>
            <?php if ($categories->isEmpty()): ?>
                <p class="p-gallery__empty"><?= __('No categories have been added yet.') ?></p>
            <?php else: ?>
                <div class="p-gallery__cards">
                    <?php foreach ($categories as $category): ?>
                        <a
                            class="p-gallery__card"
                            href="<?= $this->Url->build(['controller' => 'Gallery', 'action' => 'category', $category->id]) ?>"
                        >
                            <div
                                class="p-gallery__card-image"
                                <?php if ($category->image): ?>
                                    style="background-image: url('<?= h($this->Url->build('/img/categories/' . $category->image)) ?>');"
                                <?php endif; ?>
                            ></div>
                            <span class="p-gallery__card-badge"><?= h($category->title) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <a class="p-gallery__back" href="<?= $this->Url->build(['controller' => 'Gallery', 'action' => 'index']) ?>">
                &larr; <?= __('Gallery categories') ?>
            </a>
            <h1 class="p-gallery__title"><?= h($parent->title) ?></h1>

            <?php if ($parent->description): ?>
                <p class="p-gallery__description"><?= nl2br(h($parent->description)) ?></p>
            <?php endif; ?>

            <?php if (!$subcategories->isEmpty()): ?>
                <?= $this->Html->css('vendor/swiper-bundle.min') ?>
                <div class="p-gallery__subcats">
                    <div class="p-gallery__subcats-carousel swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($subcategories as $subcategory): ?>
                                <a
                                    class="p-gallery__card p-gallery__card--sub swiper-slide"
                                    href="<?= $this->Url->build(['controller' => 'Gallery', 'action' => 'category', $subcategory->id]) ?>"
                                >
                                    <div
                                        class="p-gallery__card-image"
                                        <?php if ($subcategory->image): ?>
                                            style="background-image: url('<?= h($this->Url->build('/img/categories/' . $subcategory->image)) ?>');"
                                        <?php endif; ?>
                                    ></div>
                                    <span class="p-gallery__card-badge"><?= h($subcategory->title) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="p-gallery__subcats-nav">
                        <button type="button" class="p-gallery__subcats-nav-btn p-gallery__subcats-nav-btn--prev" aria-label="<?= __('Previous') ?>">&larr;</button>
                        <button type="button" class="p-gallery__subcats-nav-btn p-gallery__subcats-nav-btn--next" aria-label="<?= __('Next') ?>">&rarr;</button>
                    </div>
                </div>
                <?= $this->Html->script('vendor/swiper-bundle.min') ?>
                <?= $this->Html->script('gallery-subcats-swiper') ?>

                <hr class="p-gallery__divider">
            <?php endif; ?>

            <h1 class="p-gallery__section-title"><?= __('Photographs') ?></h1>

            <?php if ($photos->isEmpty()): ?>
                <p class="p-gallery__empty"><?= __('No photos in this category yet.') ?></p>
            <?php else: ?>
                <div class="p-gallery__grid">
                    <?php foreach ($photos as $photo): ?>
                        <?php
                        $belongsToSubcategory = $photo->category_id !== $parent->id && $photo->category;
                        $caption = $photo->text !== null && $photo->text !== '' ? $photo->text : $parent->title;
                        ?>
                        <div class="p-gallery__photo-card">
                            <a
                                class="p-gallery__item"
                                href="<?= h($photo->image) ?>"
                                data-gallery-lightbox
                                data-gallery-caption="<?= h($caption) ?>"
                            >
                                <img
                                    class="p-gallery__image"
                                    src="<?= h($photo->image) ?>"
                                    alt="<?= h($caption) ?>"
                                    loading="lazy"
                                >
                            </a>
                            <div class="p-gallery__photo-caption">
                                <?php if ($belongsToSubcategory): ?>
                                    <span class="p-gallery__photo-badge"><?= h($photo->category->title) ?></span>
                                <?php endif; ?>
                                <span class="p-gallery__photo-title"><?= h($caption) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-gallery__lightbox" data-gallery-lightbox-modal>
                    <div class="p-gallery__lightbox-frame">
                        <img class="p-gallery__lightbox-image" alt="">
                        <button type="button" class="p-gallery__lightbox-close" aria-label="<?= __('Close') ?>">&times;</button>
                    </div>
                    <p class="p-gallery__lightbox-caption"></p>
                </div>
                <?= $this->Html->script('gallery-lightbox') ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
