# ────────────────────────────────────────────────────────────
# FoPost WordPress Plugin Makefile
# ────────────────────────────────────────────────────────────

PLUGIN_SLUG  := fopost
VERSION      ?= $(shell grep -i 'Version:' fopost.php | head -1 | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]')
DIST_DIR     := dist
BUILD_DIR    := $(DIST_DIR)/$(PLUGIN_SLUG)
ZIP_FILE     := $(DIST_DIR)/$(PLUGIN_SLUG)-$(VERSION).zip

# SVN settings
SVN_URL      := https://plugins.svn.wordpress.org/$(PLUGIN_SLUG)
SVN_DIR      := .svn-wp
SVN_USER     ?= $(shell echo $${WP_ORG_SVN_USERNAME})

.DEFAULT_GOAL := help

# ── Development ──────────────────────────────────────────────

.PHONY: install
install: ## Install all Composer dependencies (dev + prod)
	composer install --prefer-dist

.PHONY: lint
lint: ## Run PHPCS (project standard from phpcs.xml.dist)
	php -d xdebug.mode=off vendor/bin/phpcs

.PHONY: lint-fix
lint-fix: ## Auto-fix PHPCS violations where possible
	php -d xdebug.mode=off vendor/bin/phpcbf

.PHONY: test
test: ## Run PHPUnit tests
	vendor/bin/phpunit

# ── Build / Release ─────────────────────────────────────────

.PHONY: build
build: clean ## Build distribution zip for WordPress marketplace
	@echo "==> Building $(PLUGIN_SLUG) v$(VERSION)"

	@# Install production-only dependencies.
	composer install --no-dev --prefer-dist --optimize-autoloader --quiet

	@# Create build directory and sync files.
	@mkdir -p $(BUILD_DIR)
	rsync -rc --exclude-from=".distignore" ./ $(BUILD_DIR)/

	@# Remove dev vendor packages (safety net).
	rm -rf $(BUILD_DIR)/vendor/phpunit \
	       $(BUILD_DIR)/vendor/phpcsstandards \
	       $(BUILD_DIR)/vendor/squizlabs \
	       $(BUILD_DIR)/vendor/wp-coding-standards \
	       $(BUILD_DIR)/vendor/dealerdirect \
	       $(BUILD_DIR)/vendor/staabm \
	       $(BUILD_DIR)/vendor/phar-io \
	       $(BUILD_DIR)/vendor/sebastian \
	       $(BUILD_DIR)/vendor/theseer \
	       $(BUILD_DIR)/vendor/nikic \
	       $(BUILD_DIR)/vendor/myclabs \
	       $(BUILD_DIR)/vendor/bin

	@# Create the zip.
	@echo "==> Creating zip…"
	cd $(DIST_DIR) && zip -rq ../$(ZIP_FILE) $(PLUGIN_SLUG)/

	@# Clean up build directory.
	rm -rf $(BUILD_DIR)

	@# Restore dev dependencies.
	@echo "==> Restoring dev dependencies…"
	composer install --quiet

	@echo "==> Done! $(ZIP_FILE)"
	@echo "    Size: $$(du -h $(ZIP_FILE) | cut -f1)"

.PHONY: clean
clean: ## Remove previous build artifacts
	rm -rf $(DIST_DIR)

# ── SVN / WordPress.org Release ─────────────────────────────

.PHONY: svn-checkout
svn-checkout: ## One-time: checkout WordPress.org SVN repo
	@if [ -d "$(SVN_DIR)/trunk" ]; then \
		echo "==> SVN working copy already exists at $(SVN_DIR)"; \
		echo "    Run 'make svn-update' to pull latest changes."; \
	else \
		echo "==> Checking out $(SVN_URL)…"; \
		svn checkout $(SVN_URL) $(SVN_DIR) --username $(SVN_USER); \
		echo "==> Done! SVN working copy at $(SVN_DIR)"; \
	fi

.PHONY: svn-update
svn-update: ## Pull latest changes from WordPress.org SVN
	@if [ ! -d "$(SVN_DIR)/trunk" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	svn update $(SVN_DIR)

.PHONY: svn-sync
svn-sync: ## Sync plugin files to SVN trunk (runs build first)
	@if [ ! -d "$(SVN_DIR)/trunk" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	@echo "==> Preparing production build for SVN…"

	@# Install production-only dependencies.
	composer install --no-dev --prefer-dist --optimize-autoloader --quiet

	@# Sync to SVN trunk, respecting .distignore.
	@echo "==> Syncing to SVN trunk…"
	rsync -rc --delete --exclude-from=".distignore" ./ $(SVN_DIR)/trunk/

	@# Remove dev vendor packages (safety net).
	rm -rf $(SVN_DIR)/trunk/vendor/phpunit \
	       $(SVN_DIR)/trunk/vendor/phpcsstandards \
	       $(SVN_DIR)/trunk/vendor/squizlabs \
	       $(SVN_DIR)/trunk/vendor/wp-coding-standards \
	       $(SVN_DIR)/trunk/vendor/dealerdirect \
	       $(SVN_DIR)/trunk/vendor/staabm \
	       $(SVN_DIR)/trunk/vendor/phar-io \
	       $(SVN_DIR)/trunk/vendor/sebastian \
	       $(SVN_DIR)/trunk/vendor/theseer \
	       $(SVN_DIR)/trunk/vendor/nikic \
	       $(SVN_DIR)/trunk/vendor/myclabs \
	       $(SVN_DIR)/trunk/vendor/bin

	@# Track new and removed files in SVN.
	cd $(SVN_DIR) && svn add --force trunk
	cd $(SVN_DIR) && svn status trunk | awk '/^!/ {print $$2}' | xargs -I {} svn rm "{}"

	@# Restore dev dependencies.
	composer install --quiet

	@echo "==> Trunk synced. Run 'make svn-diff' to review or 'make svn-push' to commit."

.PHONY: svn-diff
svn-diff: ## Preview SVN changes before committing
	@if [ ! -d "$(SVN_DIR)/trunk" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	@echo "==> SVN status:"
	cd $(SVN_DIR) && svn status
	@echo ""
	@echo "==> SVN diff (summary):"
	cd $(SVN_DIR) && svn diff --summarize

# Tag and trunk must land in ONE revision: WordPress.org reads trunk's Stable tag
# on commit and keeps the previous version if that tag does not exist yet.
.PHONY: svn-tag
svn-tag: ## Stage tags/VERSION as a local copy of trunk (svn-push commits it)
	@if [ ! -d "$(SVN_DIR)/trunk" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	@if [ -d "$(SVN_DIR)/tags/$(VERSION)" ]; then \
		echo "Error: tags/$(VERSION) already exists. Bump the version first."; \
		exit 1; \
	fi
	@echo "==> Staging tag $(VERSION) from trunk…"
	cd $(SVN_DIR) && svn copy trunk tags/$(VERSION)
	@echo "==> Tag staged. Run 'make svn-push' to commit it together with trunk."

.PHONY: svn-push
svn-push: ## Commit trunk and the staged tag to WordPress.org in one revision
	@if [ ! -d "$(SVN_DIR)/trunk" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	@if [ ! -d "$(SVN_DIR)/tags/$(VERSION)" ]; then \
		echo "Error: tags/$(VERSION) is not staged. Run 'make svn-tag' first."; \
		exit 1; \
	fi
	@echo "==> Committing v$(VERSION) to WordPress.org…"
	cd $(SVN_DIR) && svn commit --username $(SVN_USER) -m "Release $(VERSION)"
	@echo "==> trunk and tags/$(VERSION) committed in a single revision."

.PHONY: svn-assets
svn-assets: ## Sync assets/ to SVN assets branch (banners, icons, screenshots)
	@if [ ! -d "$(SVN_DIR)/assets" ]; then \
		echo "Error: No SVN working copy. Run 'make svn-checkout' first."; \
		exit 1; \
	fi
	@if [ ! -d "wp-assets" ]; then \
		echo "Error: No wp-assets/ directory found."; \
		echo "  Create wp-assets/ with your banner/icon/screenshot files:"; \
		echo "    banner-1544x500.png   banner-772x250.png"; \
		echo "    icon-128x128.png      icon-256x256.png"; \
		echo "    screenshot-1.png      screenshot-2.png …"; \
		exit 1; \
	fi
	@echo "==> Syncing wp-assets/ to SVN assets/…"
	rsync -rc --delete wp-assets/ $(SVN_DIR)/assets/
	cd $(SVN_DIR) && svn add --force assets
	cd $(SVN_DIR) && svn status assets | awk '/^!/ {print $$2}' | xargs -I {} svn rm "{}"
	cd $(SVN_DIR) && svn commit --username $(SVN_USER) assets -m "Update plugin assets"
	@echo "==> Assets updated on WordPress.org."

.PHONY: release
release: lint test svn-sync svn-tag svn-push ## Full release: lint → test → sync → tag → push
	@echo ""
	@echo "==> $(PLUGIN_SLUG) v$(VERSION) released to WordPress.org!"
	@echo "    https://wordpress.org/plugins/$(PLUGIN_SLUG)/"

.PHONY: version-check
version-check: ## Verify version is consistent across files
	@PHP_VER=$$(grep -i 'Version:' fopost.php | head -1 | sed 's/.*Version:[[:space:]]*//' | tr -d '[:space:]'); \
	README_VER=$$(grep -i 'Stable tag:' readme.txt | head -1 | sed 's/.*Stable tag:[[:space:]]*//' | tr -d '[:space:]'); \
	CONST_VER=$$(grep "FOPOST_VERSION" fopost.php | head -1 | sed "s/.*'\\(.*\\)'.*/\\1/"); \
	echo "  fopost.php header : $$PHP_VER"; \
	echo "  readme.txt Stable tag: $$README_VER"; \
	echo "  FOPOST_VERSION     : $$CONST_VER"; \
	if [ "$$PHP_VER" = "$$README_VER" ] && [ "$$PHP_VER" = "$$CONST_VER" ]; then \
		echo "  ✓ All versions match ($$PHP_VER)"; \
	else \
		echo "  ✗ VERSION MISMATCH, fix before releasing!"; \
		exit 1; \
	fi

.PHONY: version-bump
version-bump: ## Bump version: make version-bump V=1.2.0
	@if [ -z "$(V)" ]; then \
		echo "Usage: make version-bump V=1.2.0"; \
		exit 1; \
	fi
	@echo "==> Bumping version to $(V)…"
	@sed -i '' "s/^ \* Version:.*/ * Version:           $(V)/" fopost.php
	@sed -i '' "s/^define('FOPOST_VERSION', '.*');/define('FOPOST_VERSION', '$(V)');/" fopost.php
	@sed -i '' "s/^Stable tag:.*/Stable tag: $(V)/" readme.txt
	@echo "==> Version updated to $(V) in fopost.php and readme.txt"
	@$(MAKE) --no-print-directory version-check

# ── Help ─────────────────────────────────────────────────────

.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'
