Title: dev: add PHP 8.3 devcontainer and helper to run PHPUnit in container; add optimistic invite tests

Summary:
- Adds a `.devcontainer` Dockerfile and `devcontainer.json` for PHP 8.3 to allow running backend tests without changing host PHP.
- Adds `scripts/run-phpunit-in-container.sh` to build the image and run `composer install` + `phpunit` inside the container.
- Adds `README_DEV.md` with quick instructions for running PHPUnit locally via Docker or VS Code Dev Containers.
- Adds Vitest tests for optimistic invite flows: `resources/js/pages/organizations/index.test.tsx`.

Why:
- CI runs PHPUnit on PHP 8.3; local developer machines may have different PHP versions. These additions let developers run PHPUnit in a reproducible PHP 8.3 environment without changing their host PHP.

How to test locally:
1. Build & run PHPUnit via helper (requires Docker):

```bash
./scripts/run-phpunit-in-container.sh
```

2. Run frontend tests:

```bash
npm ci
npm run test
```

Notes for reviewers:
- I pushed this branch `feat/php83-devcontainer` and left instructions in `README_DEV.md`.
- If maintainers prefer a different base image or additional PHP extensions, I can update `.devcontainer/Dockerfile` accordingly.
