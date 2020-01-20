########################################################
# Constants
########################################################
# Console Colors
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)
TARGET_MAX_CHAR_NUM=20

# PHPUnit and Infection
INFECTION_DATA_DIR := infection
DEPENDENCY_DIR := ./vendor
VENDOR_BIN_PATH := $(DEPENDENCY_DIR)/bin
PHPUNIT_EXEC := $(VENDOR_BIN_PATH)/phpunit
INFECTION_EXEC := $(VENDOR_BIN_PATH)/infection
COMPOSER_INSTALL := composer install
########################################################
# Automatic Help Generator
########################################################
help:
	@echo ''
	@echo 'Usage:'
	@echo '  ${YELLOW}make${RESET} ${GREEN}<target>${RESET}'
	@echo ''
	@echo 'Targets:'
	@awk '/^[a-zA-Z\-\_0-9]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")-1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "  ${YELLOW}%-$(TARGET_MAX_CHAR_NUM)s${RESET} ${GREEN}%s${RESET}\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)
########################################################
# Commands
########################################################
.PHONY: install file_test dir_tests all_tests coverage infection

## install all the dependencies
install:
	@$(COMPOSER_INSTALL)

## run a specific test file ==> format: file=path/to/file
method_test:
	@$(PHPUNIT_EXEC) --filter $(method) --no-coverage $(file)

## run a specific test file ==> format: file=path/to/file
file_test:
	@$(PHPUNIT_EXEC) --no-coverage $(file)

## run all tests inside a directory ==> format: dir=path/to/directory
dir_tests:
	@$(PHPUNIT_EXEC) --no-coverage $(dir)

## run all tests
all_tests:
	@$(PHPUNIT_EXEC) --no-coverage

## generate test coverage (required for infection)
coverage:
	@$(PHPUNIT_EXEC)

## run infection to see how quality the tests are
infection:
	@[ -d $(INFECTION_DATA_DIR) ] || $(PHPUNIT_EXEC)
	@composer require --dev infection/infection
	@$(INFECTION_EXEC) --coverage=$(INFECTION_DATA_DIR)
	@composer remove --dev infection/infection

