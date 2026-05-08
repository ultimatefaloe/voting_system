#!/usr/bin/env bash
set -euo pipefail

# Builds the PHP 8.3 image defined in .devcontainer and runs phpunit inside it.
# Usage: ./scripts/run-phpunit-in-container.sh

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
IMAGE_NAME="voting_system_php83_dev"

echo "Building PHP 8.3 dev image..."
docker build -t ${IMAGE_NAME} -f .devcontainer/Dockerfile .

echo "Running composer install and phpunit inside container..."
docker run --rm -v "${ROOT}":/workspace -w /workspace ${IMAGE_NAME} bash -lc "composer install --no-interaction && ./vendor/bin/phpunit --configuration phpunit.xml"
