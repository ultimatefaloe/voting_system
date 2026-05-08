Dev: Running backend tests with PHP 8.3 (without changing host PHP)

If your local PHP version differs from the project's CI PHP version, use the provided devcontainer or Docker helper to run PHPUnit inside a PHP 8.3 environment.

Quick options:

- Run via helper script (requires Docker):

```bash
./scripts/run-phpunit-in-container.sh
```

- Use VS Code Devcontainer (recommended for development):
  - Install the "Remote - Containers" extension.
  - Reopen the repository in container (Command Palette: "Dev Containers: Reopen in Container").
  - The container image builds from `.devcontainer/Dockerfile` and runs `composer install` automatically.

Notes:
- The container image is a minimal PHP 8.3 CLI image with `pdo_sqlite`, `zip`, `mbstring`, and `xml` extensions enabled and `composer` installed.
- If additional PHP extensions are required by the project, edit `.devcontainer/Dockerfile` accordingly.
