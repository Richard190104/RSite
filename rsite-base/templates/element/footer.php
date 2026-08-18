<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="site-footer">
    <div>
        <p class="site-footer__left">
            &copy; <?= date('Y') ?> <?= h(__($this->fetch('title'))) ?>
        </p>
    </div>
    
    <div>
        <p class="site-footer__center">
            &copy; <?= date('Y') ?> <?= h(__($this->fetch('title'))) ?>
        </p>
    </div>

    <div>
        <p class="site-footer__right">
            &copy; <?= date('Y') ?> <?= h(__($this->fetch('title'))) ?>
        </p>
    </div>

</div>
