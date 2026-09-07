<?php
/**
 * @var \App\View\AppView $this
 * @var array<\App\Model\Entity\FishingGround> $fishingGrounds
 *
 * A stylized (illustrated, not tile-based) map of Medzilaborce and its
 * immediate surroundings, showing where the organisation's fishing grounds
 * sit relative to the town and each other. The backdrop (river, roads,
 * forest cover, town boundary) is real OpenStreetMap geometry, simplified
 * and pre-projected once into config/data/reviry_map_basemap.php — see that
 * file's docblock for how/why it was generated; it does not change at
 * request time. Fishing-ground pins are placed by a simple percentage
 * (Category::position_x/position_y, 0-100, set by an admin eyeballing the
 * map — see Admin\FishingGroundsController's add/edit forms) rather than
 * real GPS coordinates, since exact lat/lng is fiddly to enter and this map
 * is illustrative, not navigational. Only grounds with both position_x and
 * position_y set get a pin; grounds missing either are silently skipped
 * rather than guessed at.
 */
$basemap = require CONFIG . 'data' . DS . 'reviry_map_basemap.php';
$bounds = $basemap['bounds'];

$located = [];
foreach ($fishingGrounds as $fishingGround) {
    if ($fishingGround->position_x !== null && $fishingGround->position_y !== null) {
        $located[] = $fishingGround;
    }
}

$canvasWidth = $bounds['canvas_w'];
$canvasHeight = $bounds['canvas_h'];

// A closure, not a named function — this element can be rendered more than
// once per request (e.g. if a future page reuses it), and a `function`
// declaration here would fatal with "Cannot redeclare" on the second pass.
// Same $pad inset as the basemap's own geometry (see
// config/data/reviry_map_basemap.php), so a pin at position_x/y = 0/100
// lands just inside the frame rather than flush against its edge. Returned
// as a percentage of the canvas (not raw pixels) since pins are now plain
// HTML positioned with left/top — CSS percentages already track the SVG's
// own responsive scaling (it has no fixed pixel size, just a viewBox), so
// this is simpler and correct at any viewport width without a resize
// listener the pixel-based version would have needed.
$project = function (float $percentX, float $percentY) use ($bounds): array {
    $xPx = $bounds['pad'] + ($percentX / 100) * ($bounds['canvas_w'] - 2 * $bounds['pad']);
    $yPx = $bounds['pad'] + ($percentY / 100) * ($bounds['canvas_h'] - 2 * $bounds['pad']);

    return [$xPx / $bounds['canvas_w'] * 100, $yPx / $bounds['canvas_h'] * 100];
};

$pins = [];
foreach ($located as $fishingGround) {
    [$leftPercent, $topPercent] = $project((float)$fishingGround->position_x, (float)$fishingGround->position_y);
    $pins[] = ['ground' => $fishingGround, 'left' => $leftPercent, 'top' => $topPercent];
}
?>
<figure class="p-reviry-map">
    <div class="p-reviry-map__frame">
        <svg
            class="p-reviry-map__svg"
            viewBox="0 0 <?= $canvasWidth ?> <?= $canvasHeight ?>"
            role="img"
            aria-label="<?= h(__('Stylized map of Medzilaborce showing the position of each fishing ground')) ?>"
        >
            <defs>
                <pattern id="reviryMapTrees" width="14" height="14" patternUnits="userSpaceOnUse">
                    <circle cx="3" cy="3" r="1.3" fill="currentColor" opacity="0.5" />
                    <circle cx="10" cy="8" r="1.1" fill="currentColor" opacity="0.4" />
                    <circle cx="6" cy="11" r="1.2" fill="currentColor" opacity="0.45" />
                </pattern>
            </defs>

            <rect x="0" y="0" width="<?= $canvasWidth ?>" height="<?= $canvasHeight ?>" class="p-reviry-map__bg" />

            <?php foreach ($basemap['forest'] as $d): ?>
                <path d="<?= h($d) ?>" class="p-reviry-map__forest" />
            <?php endforeach; ?>

            <?php foreach ($basemap['boundary'] as $d): ?>
                <path d="<?= h($d) ?>" class="p-reviry-map__boundary" />
            <?php endforeach; ?>

            <?php foreach ($basemap['roads'] as $d): ?>
                <path d="<?= h($d) ?>" class="p-reviry-map__road" />
            <?php endforeach; ?>

            <?php foreach ($basemap['river'] as $d): ?>
                <path d="<?= h($d) ?>" class="p-reviry-map__river" />
            <?php endforeach; ?>

            <?php foreach ($basemap['labels'] as $label): ?>
                <g class="p-reviry-map__place<?= $label['primary'] ? ' p-reviry-map__place--primary' : '' ?>">
                    <circle cx="<?= $label['x'] ?>" cy="<?= $label['y'] ?>" r="<?= $label['primary'] ? 5 : 3.5 ?>" class="p-reviry-map__place-dot" />
                    <text x="<?= $label['x'] + 10 ?>" y="<?= $label['y'] + 4 ?>" class="p-reviry-map__place-label"><?= h($label['name']) ?></text>
                </g>
            <?php endforeach; ?>

        </svg>

        <?php // HTML overlay, not SVG — a real backdrop-filter blur (the "frosted glass" look) only reliably renders on an HTML element, not an SVG shape. Positioned with left/top percentages so it tracks the SVG's own responsive scaling with no JS/resize listener needed. ?>
        <div class="p-reviry-map__pins">
            <?php foreach ($pins as $pin): ?>
                <?php $ground = $pin['ground']; ?>
                <button
                    type="button"
                    class="p-reviry-map__pin"
                    style="left: <?= round($pin['left'], 2) ?>%; top: <?= round($pin['top'], 2) ?>%;"
                    data-reviry-card
                    data-id="<?= h($ground->id) ?>"
                    data-title="<?= h($ground->title) ?>"
                    data-description="<?= h($ground->description ?? '') ?>"
                    data-location="<?= h($ground->location ?? '') ?>"
                    data-type="<?= h($ground->type ?? '') ?>"
                    data-registration-number="<?= h($ground->registration_number ?? '') ?>"
                    data-image="<?= $ground->image ? h($this->Url->build('/img/fishing-grounds/' . $ground->image)) : '' ?>"
                >
                    <span class="p-reviry-map__pin-dot">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 22s7.5-6.1 7.5-12.2C19.5 5.2 16.1 2 12 2S4.5 5.2 4.5 9.8C4.5 15.9 12 22 12 22Z"/>
                            <circle cx="12" cy="9.6" r="3.1" fill="var(--color-bg, #fff)"/>
                        </svg>
                    </span>
                    <span class="p-reviry-map__pin-label"><?= h($ground->title) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</figure>
