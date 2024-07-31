<?php

declare(strict_types=1);

namespace phpsap\IntegrationTests;

use Closure;
use InvalidArgumentException;
use kbATeam\MemoryContainer\Container;
use RuntimeException;

/**
 * Class \phpsap\IntegrationTests\SapRfcModuleMocks
 *
 * Container holding mock logic for the SAP RFC module.
 *
 * @package phpsap\IntegrationTests
 * @author  Gregor J.
 * @license MIT
 */
class SapRfcModuleMocks extends Container
{
    /**
     * @var array Valid SAP RFC module function or class method names.
     */
    private static array $validModuleFunctions = [];

    /**
     * @var string Path to file that will get required once.
     */
    private static string $requireFile;

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
     * @param  array  $moduleFunctions
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
        $nameValid = $this->validateId($name);
        $this->set($nameValid, $logic);
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
        $return = parent::validateId($id);
        if (!in_array($return, static::$validModuleFunctions, true)) {
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
        if (static::$requireFile === null) {
            throw new RuntimeException('No module logic template file defined!');
        }
        require_once static::$requireFile;
    }
}
