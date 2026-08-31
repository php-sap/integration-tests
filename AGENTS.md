# AGENTS.md

## Scope
- This repository is a **library of shared integration test infrastructure** for PHP/SAP implementations, not a standalone application.
- `README.md` is intentionally minimal; the real behavior is defined in `src/`.
- There are **no concrete `*Test.php` files or PHPUnit config here**. Consumer repositories subclass these base classes and run the tests there.

## Architecture you need to understand first
- `src/AbstractTestCase.php` is the entry point for implementation-specific tests.
  - It wires SAP module mocking in the constructor via `SapRfcModuleMocks::requireFile()` and `::validModuleFunctions()`.
  - `newSapRfc()` delegates to `static::getClassName()::create(...)`; concrete repos must provide the actual RFC implementation class.
  - `getApi($remoteFunction)` loads `src/<RFC_NAME>.json`, so fixture filenames must match the SAP function name exactly.
- `src/AbstractSapRfcTestCase.php` defines the shared contract for SAP RFC implementations.
  - Concrete test classes must implement the abstract mock methods (`mockConnectionFailed()`, `mockSuccessfulRfcPing()`, `mockUnknownFunctionException()`, etc.).
  - The tests intentionally support two modes:
    - real SAP execution when `extension_loaded(static::getModuleName())` is true
    - mock-driven execution otherwise, using `getSampleSapConfig()` and module template functions/classes
- `src/SapRfcModuleMocks.php` is a strict mock registry.
  - Mock names are validated against `getValidModuleFunctions()`; unknown mock IDs throw immediately.
  - The module template file must exist before test case construction or the suite fails early.

## Data and API description patterns
- SAP RFC signatures live in JSON fixtures under `src/`:
  - `RFC_PING.json` is the empty/simple case.
  - `RFC_READ_TABLE.json` shows table parameters with `members`.
  - `RFC_WALK_THRU_TEST.json` shows nested `struct`/`table` output and SAP date/time fields.
- Preserve the existing schema keys exactly: `type`, `name`, `direction`, `optional`, and nested `members`.
- When adding coverage for a new RFC, add the JSON fixture first, then update tests/mocks to use the exact same function name.

## Implementation-specific conventions
- Keep `declare(strict_types=1);` and the existing namespace layout: `phpsap\IntegrationTests\` -> `src/`.
- This package uses PHPUnit assertions inside abstract base classes; follow that pattern instead of adding ad-hoc helper scripts.
- The sample connection config in `AbstractTestCase::$sampleSapConfig` is intentionally fake and is only for mock mode.
- Real-system tests depend on a concrete repo implementing `getSapConfigFile()` and returning a valid JSON config consumable by `ConfigTypeA::jsonDecode()`.
- Error expectations are part of the contract. Examples:
  - incomplete config must surface `IncompleteConfigException`
  - unknown RFC names must surface `UnknownFunctionException`
  - malformed runtime calls are expected to surface `FunctionCallException`

## Workflows that are easy to miss
- Run commands from the repository root.
- PHPUnit is installed (`./vendor/bin/phpunit`), but running it in this repo without a target only shows usage because this package ships reusable abstract tests, not a local suite.
- Code style is defined by `phpcs.xml` and targets `src/` with PSR-12.
- If you need PHP CodeSniffer tools and they are not installed as project dependencies, use the environment wrapper commands from the host setup; this repo itself does not vendor `phpcs`.

## Safe change strategy for agents
- Prefer small edits in `src/` and keep public abstract method contracts stable; downstream SAP implementation packages depend on them.
- Before changing a mock name or module template expectation, trace all related abstract methods and the `SapRfcModuleMocks::validateId()` path.
- Before changing JSON fixtures, inspect the matching assertions in `AbstractSapRfcTestCase.php` so types still align with expected PHP values (`DateTime`, `DateInterval`, arrays, decoded hex values).
- Write documentation, comments, and new code in English to match the repository style.
