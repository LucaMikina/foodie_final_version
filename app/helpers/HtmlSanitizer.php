<?php

class HtmlSanitizer
{
    private const DOZVOLJENE_OZNAKE = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'ul', 'ol', 'li', 'h3', 'h4', 'span', 'a',
    ];

    private const DOZVOLJENI_ATRIBUTI = [
        'a' => ['href', 'title'],
    ];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><div id="foodie-root">' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $root = $dom->getElementById('foodie-root');
        if ($root === null) {
            return '';
        }

        self::sanitizeNode($dom, $root);

        $rezultat = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $rezultat .= $dom->saveHTML($child);
        }

        return trim($rezultat);
    }

    private static function sanitizeNode(DOMDocument $dom, DOMNode $node): void
    {

        $djeca = iterator_to_array($node->childNodes);

        foreach ($djeca as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            $oznaka = strtolower($child->tagName);

            if (!in_array($oznaka, self::DOZVOLJENE_OZNAKE, true)) {

                if (in_array($oznaka, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $node->removeChild($child);
                } else {
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                }
                continue;
            }

            self::sanitizeAttributes($child, $oznaka);
            self::sanitizeNode($dom, $child);
        }
    }

    private static function sanitizeAttributes(DOMElement $el, string $oznaka): void
    {
        $dozvoljeni = self::DOZVOLJENI_ATRIBUTI[$oznaka] ?? [];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $imeAtributa = strtolower($attr->name);

            if (!in_array($imeAtributa, $dozvoljeni, true)) {
                $el->removeAttribute($attr->name);
                continue;
            }

            if ($imeAtributa === 'href' && !self::isSafeUrl($attr->value)) {
                $el->removeAttribute('href');
            }
        }

        if ($oznaka === 'a' && $el->hasAttribute('href')) {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        return (bool) preg_match('#^(https?://|/|\#)#i', $url);
    }
}
