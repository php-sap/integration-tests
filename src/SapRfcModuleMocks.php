<?php
/**
 * File src/SapRfcModuleMocks.php
 *
 * Container holding mock logic for the SAP RFC module.
 *
 * @package integration-tests
 * @author  Gregor J.
 * @license MIT
 */

namespace phpsap\IntegrationTests;

use kbATeam\MemoryContainer\Container;

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
    private static $validModuleFunctions = [];

    /**
     * @var string Path to file that will get required once.
     */
    private static $requireFile;

    /**
     * Set the file to require.
     * @param string $file
     * @throws \RuntimeException
     */
    public static function requireFile($file)
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf(
                'Required file %s not found!',
                $file
            ));
        }
        static::$requireFile = $file;
    }

    /**
     * Set an array of valid function names.
     * @param array $moduleFunctions
     */
    public static function validModuleFunctions($moduleFunctions)
    {
        if (!is_array($moduleFunctions)) {
            throw new \RuntimeException(
                'Expected array of valid function names.'
            );
        }
        static::$validModuleFunctions = $moduleFunctions;
    }

    /**
     * Mock a SAP RFC module specific function or method.
     * @param string $name
     * @param \Closure $logic
     */
    public function mock($name, $logic)
    {
        $nameValid = $this->validateId($name);
        if (!is_object($logic) && ! $logic instanceof \Closure) {
            throw new \InvalidArgumentException('Expect function to be closure!');
        }
        $this->set($nameValid, $logic);
    }

    /**
     * Validate an ID for the other methods.
     * @param  mixed  $name  The function name to validate.
     * @return string
     * @throws \InvalidArgumentException The function name was no string or an empty
     *         string, or not in the list of templates.
     */
    protected function validateId($name)
    {
        $return = parent::validateId($name);
        if (!in_array($return, static::$validModuleFunctions, true)) {
            throw new \InvalidArgumentException(sprintf(
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
            throw new \RuntimeException('No module logic template file defined!');
        }
        require_once static::$requireFile;
    }
}
