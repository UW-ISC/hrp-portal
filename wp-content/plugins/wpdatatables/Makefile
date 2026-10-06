PHONY :=
.DEFAULT_GOAL := help
SHELL := /bin/bash

# Colors
RED := \033[0;31m
GREEN := \033[0;32m
YELLOW := \033[1;33m
CYAN := \033[1;36m
NC := \033[0m # No Color

# Container configuration
LOCAL_DIR := $(shell pwd)

# Auto-detect container name based on current working directory
# Extract site name from path: sites/<site>/wordpress/wp-content/plugins/wpdatatables
SITE_NAME := $(shell echo $(LOCAL_DIR) | grep -oE 'sites/[^/]+' | cut -d'/' -f2)
ifeq ($(SITE_NAME),)
SITE_NAME := wp
endif
CONTAINER_NAME := wordpress-$(SITE_NAME)

PLUGIN_DIR ?= /var/www/html/wp-content/plugins/wpdatatables
LITE_PLUGIN_DIR ?= /var/www/html/wp-content/plugins/wpdatatables-lite

define ensure_container
	@if ! docker ps -a --format '{{.Names}}' | grep -qx '$(CONTAINER_NAME)'; then \
		printf "$(YELLOW)Container $(CONTAINER_NAME) not found. Start stack with 'make up'.$(NC)\n"; \
		exit 1; \
	fi
endef

PHONY += help
help: ## Show this help message
	@printf "$(CYAN)wpDataTables Translation Commands$(NC)\n"
	@printf "\n"
	@printf "$(CYAN)Translations$(NC)\n"
	@printf "  $(YELLOW)lang-upload$(NC)         Upload source language to Localazy\n"
	@printf "  $(YELLOW)lang-download$(NC)       Download translations from Localazy\n"

PHONY += lang-download
lang-download: ## Download translations via Localazy
	$(ensure_container)
	@printf "$(YELLOW)Downloading translations from Localazy...$(NC)\n"
	@docker exec -w $(PLUGIN_DIR) $(CONTAINER_NAME) bash jenkins/localazyDownload.sh || (printf "$(RED)Localazy download failed$(NC)\n" && exit 1)
	@printf "$(GREEN)Translations downloaded$(NC)\n"

PHONY += lang-upload
lang-upload: ## Upload source language to Localazy
	$(ensure_container)
	@printf "$(YELLOW)Uploading translations to Localazy...$(NC)\n"
	@docker exec -w $(PLUGIN_DIR) -e LITE_PATH=$(LITE_PLUGIN_DIR) $(CONTAINER_NAME) bash jenkins/localazyUpload.sh || (printf "$(RED)Localazy upload failed$(NC)\n" && exit 1)
	@printf "$(GREEN)Translations uploaded$(NC)\n"

.PHONY: $(PHONY)
