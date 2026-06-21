# Krate dev tasks. Run from WSL (where ddev lives). Usage: `make <target>`.
.DEFAULT_GOAL := help
.PHONY: help start stop restart lint logs db shell composer url

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

start: ## Start the ddev project
	ddev start

stop: ## Stop the ddev project
	ddev stop

restart: ## Restart the ddev project
	ddev restart

lint: ## PHP lint every .php file under src/, public/, config/
	ddev exec 'find src public config -name "*.php" -print0 | xargs -0 -n1 -P4 php -l'

logs: ## Follow the web container log (Ctrl-C to stop)
	ddev logs -s web -f

db: ## Open a MySQL shell on the project database
	ddev mysql

shell: ## Open a bash shell inside the web container
	ddev ssh

composer: ## Run composer in the container, e.g. `make composer ARGS="install"`
	ddev composer $(ARGS)

url: ## Print the project URL
	@ddev describe -j 2>/dev/null | grep -o '"primary_url":"[^"]*"' || echo "https://krate.ddev.site"
