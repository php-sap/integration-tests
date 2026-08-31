<?php

declare(strict_types=1);

namespace phpsap\IntegrationTests;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class \phpsap\IntegrationTests\SapRfcModuleMocks
 *
 * In-memory registry holding mock logic for the SAP RFC module.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
class SapRfcModuleMocks
{
    /**
     * @var array<int, string> Valid SAP RFC module function or class method names.
     */
    protected static array $validModuleFunctions = [];

    /**
     * @var string Path to file that will get required once.
     */
    protected static string $requireFile;

    /**
     * @var array<string, Closure> In-memory storage of mocked logic.
     */
    private array $storage = [];

    /**
     * Set the file to require.
     * @param  string  $file
     * @throws RuntimeException
     */
    public static function requireFile(string $file): void
    {
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf(
                'Required file %s not found!',
                $file
            ));
        }
        static::$requireFile = $file;
    }

    /**
     * Set an array of valid function names.
     * @param  array<int, string>  $moduleFunctions
     */
    public static function validModuleFunctions(array $moduleFunctions): void
    {
        static::$validModuleFunctions = $moduleFunctions;
    }

    /**
     * Mock a SAP RFC module specific function or method.
     * @param  string  $name
     * @param  Closure  $logic
     */
    public function mock(string $name, Closure $logic): void
    {
        $this->storage[$this->validateId($name)] = $logic;
    }

    /**
     * Retrieve the mocked logic for a SAP RFC module specific function or method.
     * @param  string  $name
     * @return Closure
     * @throws InvalidArgumentException The function name was no string or an empty
     *         string, or not in the list of templates.
     * @throws RuntimeException No mock has been registered for this name.
     */
    public function get(string $name): Closure
    {
        $nameValid = $this->validateId($name);
        if (!array_key_exists($nameValid, $this->storage)) {
            throw new RuntimeException(sprintf('%s not found', $nameValid));
        }
        return $this->storage[$nameValid];
    }

    /**
     * Validate an ID for the other methods.
     * @param  string  $id  The function name to validate.
     * @return string
     * @throws InvalidArgumentException The function name was no string or an empty
     *         string, or not in the list of templates.
     */
    protected function validateId(string $id): string
    {
        $return = trim($id);
        if ($return === '' || !in_array($return, static::$validModuleFunctions, true)) {
            throw new InvalidArgumentException(sprintf(
                '%s function not defined in template.',
                $return
            ));
        }
        return $return;
    }

    /**
     * SapRfcModuleMocks constructor.
     * Loads the module logic template file containing the SAP RFC module specific
     * functions or class methods.
     */
    public function __construct()
    {
        if (!isset(static::$requireFile)) {
            throw new RuntimeException('No module logic template file defined!');
        }
        require_once static::$requireFile;
    }

    /**
     * Always returns the same instance for the duration of the process.
     * @return self
     */
    public static function singleton(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}
