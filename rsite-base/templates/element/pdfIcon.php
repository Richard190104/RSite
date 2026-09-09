<?php
/**
 * A stylized "document with a folded corner + PDF label" icon, in the
 * site's own colors (currentColor for the sheet, v.$color-secondary for the
 * label band via a CSS class — see .p-zarybnenie__doc-icon-band) rather
 * than the usual generic red Acrobat-style icon, so it reads as part of the
 * page rather than a foreign brand mark.
 */
?>
<svg class="p-zarybnenie__doc-icon" viewBox="0 0 48 56" fill="none" aria-hidden="true">
    <path
        d="M6 2h24l12 12v36a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4Z"
        fill="currentColor"
        fill-opacity="0.08"
        stroke="currentColor"
        stroke-width="2"
        stroke-linejoin="round"
    />
    <path
        d="M30 2v10a2 2 0 0 0 2 2h10"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linejoin="round"
    />
    <rect x="4" y="32" width="34" height="16" rx="2" class="p-zarybnenie__doc-icon-band" />
    <text x="21" y="43.5" text-anchor="middle" class="p-zarybnenie__doc-icon-label">PDF</text>
</svg>
