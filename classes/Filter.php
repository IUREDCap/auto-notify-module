<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule;

/**
 * Class for filtering/escaping user input.
 */
class Filter
{
    public static $allowedMessageTags = [
        'a', 'b', 'blockquote', 'em',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'i', 'li', 'ol', 'p', 'pre', 'span', 'strong',
        'table', 'tbody', 'td', 'th', 'thead', 'tr',
        'ul'
    ];

    public static $allowedLogDetailsTags = [
        'h1', 'h2', 'h3', 'h4',
        'table', 'tbody', 'td', 'th', 'thead', 'tr'
    ];

    /**
     * Escape text for displaying as HTML.
     * This method only works within REDCap context.
     *
     * @param string $value the text to display.
     */
    public static function escapeForHtml($value)
    {
        return \REDCap::escapeHtml($value);
    }

    public static function escapeForHtmlAttribute($value)
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }

    /**
     * Escape value for use as URL parameters.
     */
    public static function escapeForUrlParameter($value)
    {
        return urlencode($value);
    }

    public static function escapeForMysql($value)
    {
        return db_escape($value);
    }

    public static function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function sanitizeInt($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Removes tags and invalid characters for labels
     * (internal string values used for submit buttons, etc.).
     */
    public static function sanitizeLabel($value)
    {
        $filteredValue = strip_tags($value);
        $flags = FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK;
        $filteredValue = filter_var($filteredValue, FILTER_UNSAFE_RAW, $flags);
        return $filteredValue;
    }

    /**
     * Removes invalid characters for internal button labels.
     */
    public static function sanitizeButtonLabel($value)
    {
        if ($value !== null) {
            $value = preg_replace('/([^a-zA-Z0-9_\- .])/', '', $value);
        }
        return $value;
    }

    /**
     * Removes invalid characters from dates
     */
    public static function sanitizeDate($value)
    {
        if ($value !== null) {
            $value = preg_replace('/([^0-9\-\/])/', '', $value);
        }
        return $value;
    }

    /**
     * Removes invalid characters from date times
     */
    public static function sanitizeDateTime($value)
    {
        if ($value !== null) {
            $value = preg_replace('/([^0-9\-\/ :])/', '', $value);
        }
        return $value;
    }

    /**
     * Removes tags and invalid characters for strings.
     */
    public static function sanitizeString($value)
    {
        $filteredValue = strip_tags($value);
        $flags = FILTER_FLAG_STRIP_LOW;
        $filteredValue = filter_var($filteredValue, FILTER_UNSAFE_RAW, $flags);
        return $filteredValue;
    }

    public static function sanitizeEmail($value)
    {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitizes notification messages.
     * Removes all tags except allowed tags, and removes all tag
     * attributes except for the "href" attribute that have the
     * form href="http..." for the "a" tag.
     */
    public static function sanitizeMessage($text)
    {
        # Remove leading spacing from tags
        $text = preg_replace('/<\s+/', '<', $text);

        # Remove trailing space from tags
        $text = preg_replace('/\s+>/', '>', $text);

        # Close nested tags, for example, change "<a <a>" => "<a> <a>"
        $text = preg_replace('/<\s*([a-z][a-z0-9]*)([^<>]*<)/i', '<${1}>${2}', $text);

        # Terminate non-terminated a tags, e.g.: "<a this is a test" => "<a> this is a test"
        $text = preg_replace('/<\s*[a-z][a-z0-9]*\s+([^>]*$)/i', '<a>$1', $text);

        # Remove non-allowed tags
        $allowedTagsString = '<' . implode('><', self::$allowedMessageTags) . '>';
        $text = strip_tags($text, $allowedTagsString);

        # Remove disallowed attributes of allowed tags, except for the "a" tag
        foreach (self::$allowedMessageTags as $tag) {
            if ($tag === 'span') {
                # Allow only style attribute for span with "color" or "background-color" and hexidecimal color value,
                # or text-decoration with underline value
                $text = preg_replace(
                    '/<span\s*(style="'
                    . '((\s*text-decoration:\s+underline;)|(\s*(background-)?color:\s+#[0-9a-f]{6};))+")'
                    . '?[^>]*>/i',
                    '<span ${1}>',
                    $text
                );
            } elseif ($tag === 'table') {
                $text = preg_replace("/<table[^>]*>/i", '<table style="border-collapse: collapse;" border="1">', $text);
            } elseif ($tag === 'td') {
                $text = preg_replace("/<td[^>]*>/i", '<td style="padding: 1px 4px 1px 4px;">', $text);
            } elseif ($tag !== 'a') {
                # Remove all attributes
                $text = preg_replace("/<{$tag}\s+[^>]*?(\/?)>/i", '<' . $tag . '$1>', $text);
            }
        }

        # Remove "a" tag attributes that are not href with the form href="http..."
        $tempTag = 'a__TEMP_FILTER__';
        $text = preg_replace('/<' . $tempTag . '\s+/', '<a ', $text);  # Make sure there are no temp tags
        $text = preg_replace('/<a\s+[^>]*(href="http[^"]*")[^>]*>/i', '<' . $tempTag . ' $1>', $text);
        $text = preg_replace('/<a\s+[^>]*>/i', '<a>', $text);
        $text = preg_replace('/<' . $tempTag . '\s+/', '<a ', $text);

        return $text;
    }

    public static function sanitizeHtml($html)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        $purifiedHtml = $purifier->purify($html);

        return $purifiedHtml;
    }
}
