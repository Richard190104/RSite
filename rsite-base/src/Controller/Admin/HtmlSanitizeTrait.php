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
        $config = HTMLPurifier_Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p[style],br,strong,b,em,i,u,s,a[href|style],ul,ol,li,h1[style],h2[style],h3[style],h4[style],'
                . 'blockquote,img[src|alt|style],span[style],div[style]',
        );
        // border-radius lives behind CSS.Proprietary and display behind
        // CSS.AllowTricky in HTMLPurifier's CSSDefinition — without these,
        // both properties are silently stripped even when listed in
        // CSS.AllowedProperties, since that allowlist can only keep or
        // remove properties the definition already registered.
        $config->set('CSS.Proprietary', true);
        $config->set('CSS.AllowTricky', true);
        $config->set(
            'CSS.AllowedProperties',
            'text-align,color,background-color,background,background-image,background-size,background-position,'
                . 'padding,padding-top,padding-right,padding-bottom,'
                . 'padding-left,margin,margin-top,margin-right,margin-bottom,margin-left,border-radius,border,'
                . 'font-size,font-weight,line-height,width,max-width,height,min-height,display,text-decoration,'
                . 'letter-spacing,text-transform',
        );
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
