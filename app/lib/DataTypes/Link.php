<?php

namespace lib\DataTypes;

use lib\Exceptions\LinkException;

class Link extends File
{
    /**
     * @return string
     * @throws LinkException
     */
    public function getURL() : string
    {
        $content = $this->getContent();
        $contentAsText = $content->getAsText();
        unset($content);

        $contentParsed = parse_ini_string($contentAsText, true);

        if ($contentParsed && isset($contentParsed['InternetShortcut']['URL'])) {
            return $contentParsed['InternetShortcut']['URL'];
        } else {
            throw new LinkException('Malformed file. No URL found.');
        }
    }
}