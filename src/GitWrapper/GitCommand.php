<?php declare(strict_types=1);

namespace GitWrapper;

/**
 * Base class extended by all Git command classes.
 */
class GitCommand
{
    /**
     * Path to the directory containing the working copy. If this variable is
     * set, then the process will change into this directory while the Git
     * command is being run.
     *
     * @var string|null
     */
    protected $directory;

    /**
     * The command being run, e.g. "clone", "commit", etc.
     *
     * @var string
     */
    protected $command = '';

    /**
     * @var mixed[]
     */
    protected $options = [];

    /**
     * @var mixed[]
     */
    protected $args = [];

    /**
     * Whether command execution should be bypassed.
     *
     * @var bool
     */
    protected $bypass = false;

    /**
     * Whether to execute the raw command without escaping it. This is useful
     * for executing arbitrary commands, e.g. "status -s". If this is true,
     * any options and arguments are ignored.
     *
     * @var bool
     */
    protected $executeRaw = false;

    /**
     * Use GitCommand::getInstance() as the factory method for this class.
     *
     * @param mixed[] $args The arguments passed to GitCommand::getInstance().
     */
    protected function __construct(array $args)
    {
        if ($args) {
            // The first argument is the command.
            $this->command = array_shift($args);

            // If the last element is an array, set it as the options.
            $options = end($args);
            if (is_array($options)) {
                $this->setOptions($options);
                array_pop($args);
            }

            // Pass all other method arguments as the Git command arguments.
            foreach ($args as $arg) {
                $this->addArgument($arg);
            }
        }
    }

    /**
     * Constructs a GitCommand object.
     *
     * Accepts a variable number of arguments to model the arguments passed to
     * the Git command line utility. If the last argument is an array, it is
     * passed as the command options.
     *
     * @param string $command The Git command being run, e.g. "clone", "commit", etc.
     * @param string ... Zero or more arguments passed to the Git command.
     * @param array $options An optional array of arguments to pass to the command.
     *
     */
    public static function getInstance(): GitCommand
    {
        $args = func_get_args();
        return new static($args);
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
     *
     * @param bool $bypass Whether to bypass execution of the command. The parameter defaults to
     *   true for code readability, however the default behavior of this class is to run the command.
     */
    public function bypass(bool $bypass = true): void
    {
        $this->bypass = (bool) $bypass;
    }

    /**
     * Set whether to execute the command as-is without escaping it.
     *
     * @param bool $executeRaw Whether to execute the command as-is without excaping it.
     *
     */
    public function executeRaw(bool $executeRaw = true): void
    {
        $this->executeRaw = $executeRaw;
    }

    /**
     * Returns true if the Git command should be run.
     *
     * The return value is the bool opposite $this->bypass. Although this
     * seems complex, it makes the code more readable when checking whether the
     * command should be run or not.
     *
     * @return bool
     *   If true, the command should be run.
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
                $prefix = (strlen($option) !== 1) ? '--' : '-';
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

        $command = array_merge(
            [$this->getCommand()],
            $this->buildOptions(),
            $this->args
        );

        return array_filter($command);
    }
}
