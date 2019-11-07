<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Strings;

use GitWrapper\Strings\GitStrings;
use PHPUnit\Framework\TestCase;

final class GitStringsTest extends TestCase
{
    public function testParseRepositoryName(): void
    {
        $nameGit = GitStrings::parseRepositoryName('git@github.com:cpliakas/git-wrapper.git');
        $this->assertSame($nameGit, 'git-wrapper');

        $nameHttps = GitStrings::parseRepositoryName('https://github.com/cpliakas/git-wrapper.git');
        $this->assertSame($nameHttps, 'git-wrapper');
    }
}
