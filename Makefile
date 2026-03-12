.PHONY: pint pint-check rector rector-check phpstan test fix check before-push

PINT = vendor/bin/pint
RECTOR = php vendor/bin/rector process --memory-limit=1G
PHPSTAN = php vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --memory-limit=1G --no-progress
TEST = php artisan test --compact

pint:
	$(PINT) --format=agent

pint-check:
	$(PINT) --test

rector:
	$(RECTOR)

rector-check:
	$(RECTOR) --dry-run

phpstan:
	$(PHPSTAN)

test:
	$(TEST)

fix: rector pint

check: pint-check rector-check phpstan test

before-push:
	$(MAKE) fix
	$(MAKE) check
