<?php
/** @var Kirby\Cms\Page $page */

snippet('header'); ?>
<h1 class="text-4xl"><?= $page->title() ?></h1>
<?php snippet('footer'); ?>