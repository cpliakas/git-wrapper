<?php

declare(strict_types=1);

namespace GitWrapper\Strings;

final class GitStrings
{
    /**
     * For example, passing the "git@github.com:cpliakas/git-wrapper.git"
     * repository would return "git-wrapper".
     */
    public static function parseRepositoryName(string $repositoryUrl): string
    {
        $scheme = parse_url($repositoryUrl, PHP_URL_SCHEME);

        if ($scheme === null) {
            $parts = explode('/', $repositoryUrl);
            $path = end($parts);
        } else {
            $strpos = strpos($repositoryUrl, ':');
            $path = substr($repositoryUrl, $strpos + 1);
        }

        /** @var string $path */
        return basename($path, '.git');
    }
}
