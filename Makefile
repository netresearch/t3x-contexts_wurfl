# TYPO3 Extension: contexts_wurfl (Device Detection)
# Makefile for development tasks
#
# Usage:
#   make install      Install dependencies
#   make test         Run all tests
#   make unit         Run unit tests
#   make functional   Run functional tests
#   make phpstan      Run static analysis
#   make cgl          Check coding guidelines
#   make cgl-fix      Fix coding guidelines
#   make clean        Clean build artifacts
#

.PHONY: install test unit functional phpstan cgl cgl-fix clean help

# Default PHP version
PHP_VERSION ?= 8.2
TYPO3_VERSION ?= 12

# Paths
BUILD_DIR := .Build
BIN_DIR := $(BUILD_DIR)/bin
PHPUNIT := $(BIN_DIR)/phpunit
PHPSTAN := $(BIN_DIR)/phpstan
PHP_CS_FIXER := $(BIN_DIR)/php-cs-fixer

# Default target
help:
	@echo "TYPO3 Extension: contexts_wurfl"
	@echo ""
	@echo "Available targets:"
	@echo "  install      Install dependencies"
	@echo "  test         Run all tests (unit + functional)"
	@echo "  unit         Run unit tests"
	@echo "  functional   Run functional tests"
	@echo "  phpstan      Run PHPStan static analysis"
	@echo "  cgl          Check coding guidelines"
	@echo "  cgl-fix      Fix coding guidelines"
	@echo "  clean        Clean build artifacts"
	@echo ""
	@echo "Variables:"
	@echo "  PHP_VERSION=$(PHP_VERSION)     Set PHP version"
	@echo "  TYPO3_VERSION=$(TYPO3_VERSION) Set TYPO3 version"

# Install dependencies
install:
	composer install --prefer-dist --no-progress

# Run all tests
test: unit functional

# Run unit tests
unit: $(PHPUNIT)
	$(PHPUNIT) -c Build/phpunit/UnitTests.xml

# Run functional tests
functional: $(PHPUNIT)
	$(PHPUNIT) -c Build/phpunit/FunctionalTests.xml

# Run PHPStan
phpstan: $(PHPSTAN)
	$(PHPSTAN) analyse -c Build/phpstan.neon

# Check coding guidelines
cgl: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix --config=.php-cs-fixer.dist.php --dry-run --diff

# Fix coding guidelines
cgl-fix: $(PHP_CS_FIXER)
	$(PHP_CS_FIXER) fix --config=.php-cs-fixer.dist.php

# Clean build artifacts
clean:
	rm -rf $(BUILD_DIR)/coverage
	rm -rf .php-cs-fixer.cache

# Ensure binaries exist
$(PHPUNIT) $(PHPSTAN) $(PHP_CS_FIXER): install
