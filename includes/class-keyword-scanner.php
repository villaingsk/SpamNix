<?php
if (! defined('ABSPATH')) {
    exit;
}

class SpamNix_Keyword_Scanner
{
    private $max_links;
    private $keywords;

    public function __construct($settings)
    {
        $this->max_links = max(0, (int) ($settings['max_links'] ?? 2));

        $raw = preg_split('/[\r\n,]+/', (string) ($settings['banned_keywords'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $this->keywords = array_map('strtolower', array_map('trim', $raw));
    }

    public function scan($content)
    {
        $content = (string) $content;

        preg_match_all('/https?:\/\/|www\./i', $content, $matches);
        if (count($matches[0]) > $this->max_links) {
            return 'too_many_links';
        }

        $lower = strtolower($content);
        foreach ($this->keywords as $keyword) {
            if ($keyword !== '' && strpos($lower, $keyword) !== false) {
                return 'keyword_blocked';
            }
        }

        if (preg_match('/(.)\1{8,}/u', $content)) {
            return 'repeated_characters';
        }

        if (preg_match('/[\x{200B}-\x{200F}\x{202A}-\x{202E}]/u', $content)) {
            return 'suspicious_unicode';
        }

        $letters = preg_replace('/[^a-zA-Z]/', '', $content);
        if (strlen($letters) >= 20) {
            $upper = preg_match_all('/[A-Z]/', $letters);
            $ratio = $upper / max(1, strlen($letters));
            if ($ratio > 0.75) {
                return 'excessive_uppercase';
            }
        }

        return false;
    }
}
