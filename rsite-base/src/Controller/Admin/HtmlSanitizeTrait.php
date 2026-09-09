<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitizes rich-text HTML coming from a WYSIWYG editor (News::content,
 * filled either by hand or by the admin AI chat) before it's saved.
 * Nothing in the app renders this HTML on the public site yet, but it
 * still needs to be safe at the point it's written — sanitizing on save
 * means every future reader of this column can trust it, rather than
 * every future render site having to remember to escape/strip it itself.
 */
trait HtmlSanitizeTrait
{
    private function sanitizeHtml(string $html): string
    {
        // Pull any <style> block(s) out before HTMLPurifier ever sees the
        // markup — it has no real concept of a raw-CSS-text element, so a
        // <style> tag left in place would either get dropped outright or
        // have its contents mangled as if it were HTML. Cleaned separately
        // below and spliced back onto the purified fragment afterwards.
        [$html, $styleBlock] = $this->extractStyleBlocks($html);
        $html = $this->stripDocumentWrapper($html);

        $config = HTMLPurifier_Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p[class|style],br[class],strong[class],b[class],em[class],i[class],u[class],s[class],'
                . 'a[href|class|style],ul[class],ol[class],li[class],h1[class|style],h2[class|style],'
                . 'h3[class|style],h4[class|style],blockquote[class],img[src|alt|class|style],'
                . 'span[class|style],div[class|style],table[class|style],thead[class],tbody[class],'
                . 'tr[class|style],td[class|style],th[class|style]',
        );
        // border-radius lives behind CSS.Proprietary and display behind
        // CSS.AllowTricky in HTMLPurifier's CSSDefinition — without these,
        // both properties are silently stripped regardless of what's
        // otherwise allowed, since HTMLPurifier gates them there before any
        // allowlist is even consulted.
        // Deliberately not setting CSS.AllowedProperties: leaving it unset
        // lets through every property HTMLPurifier's own CSSDefinition
        // knows about, rather than pinning inline style="" attributes to a
        // small curated list that needed a manual addition every time a new
        // property came up. Note inline style="" still can't do flexbox/
        // grid or object-fit/object-position/box-sizing — those aren't part
        // of HTMLPurifier's CSS support at all. Those DO work in the <style>
        // block above though, since it isn't run through this per-property
        // validator — use classes there for anything that needs them.
        $config->set('CSS.Proprietary', true);
        $config->set('CSS.AllowTricky', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        $purifier = new HTMLPurifier($config);
        $body = $purifier->purify($html);

        return ($styleBlock !== '' ? '<style>' . $styleBlock . '</style>' : '') . $body;
    }

    /**
     * Extracts every <style>...</style> block from $html, returning the
     * remaining markup and the cleaned, concatenated CSS separately.
     *
     * @return array{0: string, 1: string}
     */
    private function extractStyleBlocks(string $html): array
    {
        $css = '';
        $html = (string)preg_replace_callback(
            '/<style\b[^>]*>(.*?)<\/style>/is',
            function (array $matches) use (&$css): string {
                $css .= $matches[1] . "\n";

                return '';
            },
            $html,
        );

        return [$html, $this->sanitizeStyleBlock($css)];
    }

    /**
     * A <style> block is inert as far as script execution goes (browsers
     * never parse its contents as HTML/JS), so this only needs to strip the
     * handful of constructs that can still cause trouble in CSS itself:
     * old-IE expression()/behavior/-moz-binding script-like hooks, and
     * @import / url(javascript:...) as ways to pull in or run something
     * else. Everything else (custom properties, media queries, grid,
     * pseudo-classes/elements, gradients, hover states, url() for images)
     * is left alone — this is admin-authored content rendered inside an
     * isolated iframe, not arbitrary public input.
     */
    private function sanitizeStyleBlock(string $css): string
    {
        if (trim($css) === '') {
            return '';
        }

        $css = preg_replace('/@import\b[^;]*;?/i', '', $css) ?? $css;
        $css = preg_replace('/expression\s*\([^)]*\)/i', '', $css) ?? $css;
        $css = preg_replace('/-moz-binding\s*:[^;]*;?/i', '', $css) ?? $css;
        $css = preg_replace('/behaviou?r\s*:[^;]*;?/i', '', $css) ?? $css;
        $css = preg_replace_callback(
            '/url\s*\(\s*([\'"]?)\s*(?:javascript|vbscript):[^\'")]*\1\s*\)/i',
            static fn () => 'url()',
            $css,
        ) ?? $css;

        return trim($css);
    }

    /**
     * Admins sometimes paste a full HTML document (e.g. copied from an
     * external AI tool) instead of just the body fragment this field
     * expects — strips the <!DOCTYPE>/<head>/<html>/<body> wrapper so
     * <title>/<meta>/<link> noise doesn't leak through as stray text.
     */
    private function stripDocumentWrapper(string $html): string
    {
        $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<head\b[^>]*>.*?<\/head>/is', '', $html) ?? $html;
        $html = preg_replace('/<\/?(?:html|body)(?:\s[^>]*)?>/i', '', $html) ?? $html;

        return $html;
    }
}
