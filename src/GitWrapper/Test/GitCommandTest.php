<?php

namespace GitWrapper\Test;

use GitWrapper\GitCommand;

class GitCommandTest extends GitWrapperTestCase
{
    public function testCommand()
    {
        $command = $this->randomString();
        $argument = $this->randomString();
        $flag = $this->randomString();
        $option_name = $this->randomString();
        $option_value = $this->randomString();

        $git = GitCommand::getInstance($command)
            ->addArgument($argument)
            ->setFlag($flag)
            ->setOption($option_name, $option_value);

        $expected = "$command --$flag --$option_name='$option_value' '$argument'";
        $command_line = $git->getCommandLine();

        $this->assertEquals($expected, $command_line);
    }

    public function testOption()
    {
        $option_name = $this->randomString();
        $option_value = $this->randomString();

        $git = GitCommand::getInstance()
            ->setOption($option_name, $option_value);

        $this->assertEquals($option_value, $git->getOption($option_name));

        $git->unsetOption($option_name);
        $this->assertNull($git->getOption($option_name));
    }

    public function testEscapeFilepattern()
    {
        $filepattern = 'a.directory/test.txt';
        $expected = 'a.directory/test\\.txt';
        $this->assertEquals($expected, GitCommand::escapeFilepattern($filepattern));
    }
}
