<?php

declare(strict_types=1);

namespace GitWrapper;

final class GitCommand
{
    /**
     * Path to the directory containing the working copy. If this variable is
     * set, then the process will change into this directory while the Git
     * command is being run.
     *
     * @var string|null
     */
    private $directory;

    /**
     * The command being run, e.g. "clone", "commit", etc.
     *
     * @var string
     */
    private $command = '';

    /**
     * Whether command execution should be bypassed.
     *
     * @var bool
     */
    private $bypass = false;

    /**
     * Whether to execute the raw command without escaping it. This is useful
     * for executing arbitrary commands, e.g. "status -s". If this is true,
     * any options and arguments are ignored.
     *
     * @var bool
     */
    private $executeRaw = false;

    /**
     * @var mixed[]
     */
    private $options = [];

    /**
     * @var mixed[]
     */
    private $args = [];

    /**
     * @param mixed ...$argsAndOptions
     */
    public function __construct(string $command = '', ...$argsAndOptions)
    {
        $this->command = $command;

        foreach ($argsAndOptions as $argOrOption) {
            if (is_array($argOrOption)) {
                // If item is array, set it as the options
                $this->setOptions($argOrOption);
            } else {
                // Pass all other as the Git command arguments
                $this->addArgument($argOrOption);
            }
        }
    }

    /**
     * Returns Git command being run, e.g. "clone", "commit", etc.
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    public function setDirectory(?string $directory): void
    {
        $this->directory = $directory;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    /**
     * A bool flagging whether to skip running the command.
     */
    public function bypass(bool $bypass = true): void
    {
        $this->bypass = $bypass;
    }

    /**
     * Set whether to execute the command as-is without escaping it.
     */
    public function executeRaw(bool $executeRaw = true): void
    {
        $this->executeRaw = $executeRaw;
    }

    /**
     * Returns true if the Git command should be run.
     */
    public function notBypassed(): bool
    {
        return ! $this->bypass;
    }

    /**
     * Builds the command line options for use in the Git command.
     *
     * @return mixed[]
     */
    public function buildOptions(): array
    {
        $options = [];
        foreach ($this->options as $option => $values) {
            foreach ((array) $values as $value) {
                // Render the option.
                $prefix = strlen($option) !== 1 ? '--' : '-';
                $options[] = $prefix . $option;

                // Render apend the value if the option isn't a flag.
                if ($value !== true) {
                    $options[] = $value;
                }
            }
        }

        return $options;
    }

    /**
     * @param mixed[]|string|true $value The option's value, pass true if the options is a flag.
     */
    public function setOption(string $option, $value): void
    {
        $this->options[$option] = $value;
    }

    /**
     * @param mixed[] $options
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    public function setFlag(string $option): void
    {
        $this->setOption($option, true);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $option, $default = null)
    {
        return $this->options[$option] ?? $default;
    }

    public function unsetOption(string $option): void
    {
        unset($this->options[$option]);
    }

    public function addArgument(string $arg): void
    {
        $this->args[] = $arg;
    }

    /**
     * Renders the arguments and options for the Git command.
     *
     * @return string|mixed[]
     */
    public function getCommandLine()
    {
        if ($this->executeRaw) {
            return $this->getCommand();
        }

        $command = array_merge([$this->getCommand()], $this->buildOptions(), $this->args);

        return array_filter($command, 'strlen');
    }
}
