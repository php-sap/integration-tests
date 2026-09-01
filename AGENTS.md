# AGENTS.md

## Project Overview
- This repository is a **library of shared integration test infrastructure** for [PHP/SAP](https://php-sap.github.io) implementations, not a standalone application.
- `README.md` is intentionally minimal; the real behavior is defined in `src/`.
- There are **no concrete `*Test.php` files or PHPUnit config here**. Consumer repositories subclass these base classes and run the tests there.

## Ecosystem

[PHP/SAP](https://php-sap.github.io) is split across five focused repositories that build on
each other instead of one monolithic package:

| Repository                  | Role                                                                                                 | Depends on (`composer.json`)                        |
|------------------------------|-------------------------------------------------------------------------------------------------------|-------------------------------------------------------|
| `php-sap/interfaces`         | Contract-only interfaces (`IApi`, `IConfiguration`, `IFunction`, exceptions). No concrete classes.    | —                                                       |
| `php-sap/datetime`           | SAP date/time format support on top of native `DateTime`/`DateInterval`.                             | —                                                       |
| `php-sap/common`             | Generic abstract classes, API/config value objects, and exceptions implementing `interfaces`.        | `interfaces`, `datetime`                                |
| `php-sap/integration-tests`  | Shared abstract PHPUnit test infrastructure and SAP module mocks reused by concrete connector packages. | `interfaces`, `common`, `datetime`                    |
| `php-sap/saprfc-kralik`      | Concrete adapter for Gregor Kralik's `ext-sapnwrfc` extension.                                        | `interfaces`, `common` (+ `integration-tests` for tests only) |

**→ You are here: `php-sap/integration-tests`** — the shared test scaffolding consumed by connectors.

This package only provides reusable abstract test scaffolding and mocks; concrete assertions
about a specific extension's behavior belong in the connector repo consuming it (e.g.
`saprfc-kralik`), not here.

## Architecture

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

### Data and API description patterns
- SAP RFC signatures live in JSON fixtures under `src/`:
  - `RFC_PING.json` is the empty/simple case.
  - `RFC_READ_TABLE.json` shows table parameters with `members`.
  - `RFC_WALK_THRU_TEST.json` shows nested `struct`/`table` output and SAP date/time fields.
- Preserve the existing schema keys exactly: `type`, `name`, `direction`, `optional`, and nested `members`.
- When adding coverage for a new RFC, add the JSON fixture first, then update tests/mocks to use the exact same function name.

## Developer Workflows

All commands run through the `Makefile` via Docker, so the host machine does not need a
local PHP installation. Run `make help` for the full target list. Use PHP 8.1, 8.2, and
8.3 (matching the CI matrix in `.github/workflows/main.yml`) for anything
version-sensitive (PHPStan, PHP lint). If you are behind a proxy, `install` and `audit`
already forward `HTTP_PROXY`/`HTTPS_PROXY`/`NO_PROXY`; pass `CA_CERT_FILE=/path/to/ca.pem`
to trust a corporate proxy root CA inside the container.

```bash
# Install/update dependencies for a given PHP version (set DEPENDENCIES_LOWEST=1 for
# --prefer-lowest, matching the CI "lowest" matrix job)
make install PHP_VERSION=8.1

# Syntax-check every .php file in src/, matches CI
make lint PHP_VERSION=8.1

# Run PHPStan
make analyze PHP_VERSION=8.1

# Auto-fix code style (run this before "sniff")
make beautify PHP_VERSION=8.1

# Check code style (uses phpcs.xml, PSR-12 for src/)
make sniff PHP_VERSION=8.1

# Check dependencies for known vulnerabilities
make audit

# Run composer validate --strict
make validate
```

There is no `test` Makefile target: PHPUnit is installed (`vendor/bin/phpunit`), but
running it here without a target only shows usage, since this package ships reusable
abstract tests, not a local suite. Consumer repositories run the tests via their own
`make test`.

**Always use these Makefile targets instead of inventing ad-hoc `docker run`/`composer`/
`php` commands.** If a task needs something the Makefile doesn't expose directly (e.g.
PHPCS on a single file), take the exact `docker run` invocation from the matching
Makefile target (image, `DOCKER_USER`, `DOCKER_MOUNT`, env forwarding) and only append
the extra arguments — don't build the command from scratch.

PHPStan runs at **level 9** (`phpstan.neon`, scans `src/`).

## Conventions
- Keep `declare(strict_types=1);` and the existing namespace layout: `phpsap\IntegrationTests\` -> `src/`.
- This package uses PHPUnit assertions inside abstract base classes; follow that pattern instead of adding ad-hoc helper scripts.
- The sample connection config in `AbstractTestCase::$sampleSapConfig` is intentionally fake and is only for mock mode.
- Real-system tests depend on a concrete repo implementing `getSapConfigFile()` and returning a valid JSON config consumable by `ConfigTypeA::jsonDecode()`.
- Error expectations are part of the contract. Examples:
  - incomplete config must surface `IncompleteConfigException`
  - unknown RFC names must surface `UnknownFunctionException`
  - malformed runtime calls are expected to surface `FunctionCallException`

## Safe Change Strategy for Agents
- Prefer small edits in `src/` and keep public abstract method contracts stable; downstream SAP implementation packages depend on them.
- Before changing a mock name or module template expectation, trace all related abstract methods and the `SapRfcModuleMocks::validateId()` path.
- Before changing JSON fixtures, inspect the matching assertions in `AbstractSapRfcTestCase.php` so types still align with expected PHP values (`DateTime`, `DateInterval`, arrays, decoded hex values).
- Write documentation, comments, and new code in English to match the repository style.
- Always run QA/build commands through the `Makefile` targets, not self-invented `docker run`
  commands. For one-off variants (a single file), base the invocation on the relevant
  Makefile target and only append the extra arguments.
