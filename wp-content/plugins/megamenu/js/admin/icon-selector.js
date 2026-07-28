(function($) {
    "use strict";

    class MMMIconSelector {
        constructor(selectElement) {
            this.select = selectElement;
            this.$select = $(selectElement);
            this.isOpen = false;
            this.focusedIndex = -1;
            this.init();
        }

        getIconClass(cls) {
            if (!cls) return '';
            if (cls.indexOf('dashicons-') === 0 && cls.indexOf('dashicons ') === -1) {
                return 'dashicons ' + cls;
            }
            return cls;
        }

        getCustomAttributes(optionEl) {
            let attrStr = '';
            if (optionEl && optionEl.attributes) {
                $.each(optionEl.attributes, function() {
                    if (this.name.indexOf('data-') === 0 && 
                        this.name !== 'data-class' && 
                        this.name !== 'data-svg' && 
                        !this.name.endsWith('-up') && 
                        !this.name.endsWith('-down') && 
                        !this.name.endsWith('-left') && 
                        !this.name.endsWith('-right')) {
                        attrStr += ' ' + this.name + '="' + this.value + '"';
                    }
                });
            }
            return attrStr;
        }

        getPreviewHtml(optionEl, contextClass) {
            const $opt = $(optionEl);

            let hasMulti = false;
            if (optionEl && optionEl.attributes) {
                $.each(optionEl.attributes, function() {
                    if (this.name.indexOf('data-') === 0 && 
                        (this.name.endsWith('-up') || 
                         this.name.endsWith('-down') || 
                         this.name.endsWith('-left') || 
                         this.name.endsWith('-right'))) {
                        hasMulti = true;
                        return false;
                    }
                });
            }

            if (hasMulti) {
                const directions = ['up', 'down', 'left', 'right'];
                let html = '<span class="' + contextClass + ' mmm-multi-preview">';
                
                directions.forEach(dir => {
                    const svg = $opt.attr('data-svg-' + dir);
                    const baseCls = $opt.attr('data-class');
                    const icon = $opt.attr('data-icon-' + dir);
                    
                    if (svg) {
                        html += '<span class="mmm-multi-preview-item mmm-svg-preview">' + svg + '</span>';
                    } else if (baseCls && icon) {
                        let cls = baseCls;
                        let attrStr = '';
                        
                        if (baseCls === 'dashicons') {
                            cls += ' ' + icon;
                        } else if (baseCls === 'mega-material-symbol') {
                            attrStr += ' data-ms-icon="' + icon + '"';
                        } else if (icon) {
                            cls = icon.includes(' ') ? icon : cls + ' ' + icon;
                        }
                        
                        html += '<span class="mmm-multi-preview-item mmm-font-preview"><i class="' + cls + '"' + attrStr + '></i></span>';
                    } else {
                        html += '<span class="mmm-multi-preview-item mmm-disabled-preview"></span>';
                    }
                });
                
                html += '</span>';
                return html;
            }

            const val = $opt.val();
            if (!val || val === 'disabled') {
                return '<span class="' + contextClass + ' mmm-disabled-preview"></span>';
            }

            const svg = $opt.attr('data-svg');
            const cls = $opt.attr('data-class');

            if (svg) {
                return '<span class="' + contextClass + ' mmm-svg-preview">' + svg + '</span>';
            } else if (cls) {
                const attrStr = this.getCustomAttributes(optionEl);
                return '<span class="' + contextClass + ' mmm-font-preview"><i class="' + this.getIconClass(cls) + '"' + attrStr + '></i></span>';
            }

            return '<span class="' + contextClass + ' mmm-disabled-preview"></span>';
        }

        isSearchDisabled() {
            const flag = this.$select.attr('data-no-search');
            return flag !== undefined && flag !== null && flag !== '' && flag !== '0' && flag !== 'false';
        }

        getOptionSearchText(optionEl) {
            const $opt = $(optionEl);
            const parts = [ $opt.text(), $opt.val() ];

            if (optionEl && optionEl.attributes) {
                $.each(optionEl.attributes, function() {
                    if (this.name.indexOf('data-') !== 0) {
                        return;
                    }
                    if (this.name === 'data-svg' || this.name.indexOf('data-svg-') === 0) {
                        return;
                    }

                    const value = String(this.value || '');
                    if (!value) {
                        return;
                    }

                    parts.push(value);
                    parts.push(value.replace(/-/g, ' '));
                    parts.push(value.replace(/^dashicons-/, '').replace(/-/g, ' '));
                });
            }

            return parts.join(' ').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        getItemSearchText(item) {
            return [
                item.searchText || item.label,
                item.groupLabel || '',
                item.value || '',
                item.$el.attr('title') || ''
            ].join(' ').toLowerCase();
        }

        init() {
            // Hide the original select
            this.$select.hide();

            // Build the custom dropdown UI
            this.buildUI();
            this.bindEvents();
        }

        buildUI() {
            let hasMultiOptions = false;
            this.$select.find('option').each((_, option) => {
                if (option.attributes) {
                    $.each(option.attributes, function() {
                        if (this.name.indexOf('data-') === 0 && 
                            (this.name.endsWith('-up') || 
                             this.name.endsWith('-down') || 
                             this.name.endsWith('-left') || 
                             this.name.endsWith('-right'))) {
                            hasMultiOptions = true;
                            return false;
                        }
                    });
                }
                if (hasMultiOptions) return false;
            });

            this.isMultiDirectional = hasMultiOptions;

            // Create container
            this.$container = $('<div class="mmm-icon-selector-container"></div>');
            if (this.isMultiDirectional) {
                this.$container.addClass('mmm-icon-selector-multi');
            }
            
            // Create trigger
            this.$trigger = $('<button type="button" class="mmm-icon-selector-trigger" aria-haspopup="listbox" aria-expanded="false"></button>');
            this.updateTriggerContent();
            this.$container.append(this.$trigger);

            // Create dropdown panel
            this.$dropdown = $('<div class="mmm-icon-selector-dropdown" style="display: none;"></div>');

            // Search box (unless explicitly disabled via data-no-search)
            if (!this.isSearchDisabled()) {
                this.$searchWrap = $('<div class="mmm-icon-selector-search-wrap"></div>');
                this.$searchInput = $('<input type="search" class="mmm-icon-selector-search" placeholder="Search icons..." autocomplete="off" aria-label="Search icons" />');
                this.$searchWrap.append(this.$searchInput);
                this.$dropdown.append(this.$searchWrap);
            } else {
                this.$searchWrap = null;
                this.$searchInput = null;
            }

            // Options list wrapper
            this.$optionsList = $('<div class="mmm-icon-selector-list" role="listbox" tabindex="-1"></div>');
            this.populateOptions();
            this.$dropdown.append(this.$optionsList);

            this.$container.append(this.$dropdown);
            this.$select.after(this.$container);
        }

        updateTriggerContent() {
            const $selectedOpt = this.$select.find('option:selected');
            const val = this.$select.val();
            
            if (!val || val === 'disabled') {
                this.$trigger.html('<span class="mmm-icon-selector-trigger-preview mmm-disabled-preview"></span>');
                this.$trigger.attr('title', $selectedOpt.text() || '');
                return;
            }

            const previewHtml = this.getPreviewHtml($selectedOpt[0], 'mmm-icon-selector-trigger-preview');
            this.$trigger.html(previewHtml);
            this.$trigger.attr('title', $selectedOpt.text() || '');
        }

        populateOptions() {
            this.$optionsList.empty();
            this.optionItems = [];

            // Add Disabled option first if it exists
            const $disabledOpt = this.$select.find('option[value="disabled"]');
            if ($disabledOpt.length) {
                const $item = $('<div class="mmm-icon-selector-item" role="option" data-value="disabled" title="' + $disabledOpt.text() + '">' +
                    '<span class="mmm-icon-selector-item-preview mmm-disabled-preview"></span>' +
                    '<span class="mmm-icon-selector-item-label">' + $disabledOpt.text() + '</span>' +
                    '</div>');
                this.$optionsList.append($item);
                this.optionItems.push({
                    $el: $item,
                    value: 'disabled',
                    label: $disabledOpt.text().toLowerCase(),
                    searchText: this.getOptionSearchText($disabledOpt[0])
                });
            }

            // Loop optgroups or plain options
            const $optgroups = this.$select.find('optgroup');
            if ($optgroups.length) {
                $optgroups.each((_, optgroup) => {
                    const $group = $(optgroup);
                    const label = $group.attr('label');
                    
                    const $groupTitle = $('<div class="mmm-icon-selector-group-title">' + label + '</div>');
                    const $groupContainer = $('<div class="mmm-icon-selector-group-container"></div>');
                    
                    let groupHasItems = false;
                    $group.find('option').each((_, option) => {
                        const $opt = $(option);
                        const val = $opt.val();
                        if (val === 'disabled') return;

                        const itemLabel = $opt.text();
                        const previewHtml = this.getPreviewHtml(option, 'mmm-icon-selector-item-preview');

                        const $item = $('<div class="mmm-icon-selector-item" role="option" data-value="' + val + '" title="' + itemLabel + '">' +
                            previewHtml +
                            '</div>');

                        $groupContainer.append($item);
                        this.optionItems.push({
                            $el: $item,
                            value: val,
                            label: itemLabel.toLowerCase(),
                            searchText: this.getOptionSearchText(option),
                            groupLabel: (label || '').toLowerCase(),
                            $groupTitle: $groupTitle,
                            $groupContainer: $groupContainer
                        });
                        groupHasItems = true;
                    });

                    if (groupHasItems) {
                        if (this.isMultiDirectional) {
                            const $groupWrapper = $('<div class="mmm-icon-selector-group"></div>');
                            $groupWrapper.append($groupTitle);
                            $groupWrapper.append($groupContainer);
                            this.$optionsList.append($groupWrapper);
                        } else {
                            this.$optionsList.append($groupTitle);
                            this.$optionsList.append($groupContainer);
                        }
                    }
                });
            } else {
                // If flat options list, dynamically sniff groups
                this.$select.find('option').each((_, option) => {
                    const $opt = $(option);
                    const val = $opt.val();
                    if (val === 'disabled') return;

                    const itemLabel = $opt.text();
                    const previewHtml = this.getPreviewHtml(option, 'mmm-icon-selector-item-preview');

                    const $item = $('<div class="mmm-icon-selector-item" role="option" data-value="' + val + '" title="' + itemLabel + '">' +
                        previewHtml +
                        '</div>');

                    this.$optionsList.append($item);
                    this.optionItems.push({
                        $el: $item,
                        value: val,
                        label: itemLabel.toLowerCase(),
                        searchText: this.getOptionSearchText(option)
                    });
                });
            }

            this.updateSelectionHighlight();
        }

        updateSelectionHighlight() {
            const currentVal = this.$select.val();
            this.optionItems.forEach(item => {
                if (item.value === currentVal) {
                    item.$el.addClass('mmm-selected').attr('aria-selected', 'true');
                } else {
                    item.$el.removeClass('mmm-selected').attr('aria-selected', 'false');
                }
            });
        }

        bindEvents() {
            // Toggle dropdown on click
            this.$trigger.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (this.isOpen) {
                    this.close();
                } else {
                    this.open();
                }
            });

            // Handle option item clicks
            this.$optionsList.on('click', '.mmm-icon-selector-item', (e) => {
                const $item = $(e.currentTarget);
                const val = $item.data('value');
                this.selectValue(val);
            });

            // Handle search filtering
            if (this.$searchInput) {
                this.$searchInput.on('input', () => {
                    this.filterOptions();
                });
                this.$searchInput.on('keydown', (e) => {
                    this.handleSearchKeyDown(e);
                });
            }

            // Close when clicking outside
            $(document).on('click.mmmIconSelector_' + this.$select.attr('name'), (e) => {
                if (!$(e.target).closest(this.$container).length) {
                    this.close();
                }
            });

            // Keyboard navigation
            this.$container.on('keydown', (e) => {
                this.handleKeyDown(e);
            });
        }

        selectValue(val) {
            this.$select.val(val).trigger('change');
            this.updateTriggerContent();
            this.updateSelectionHighlight();
            this.close();
            this.$trigger.focus();
        }

        open() {
            // Close any other open selectors first
            $('.mmm-icon-selector-container').not(this.$container).each(function() {
                const selector = $(this).prev('.icon_dropdown').data('mmmIconSelector');
                if (selector) selector.close();
            });

            this.isOpen = true;
            this.$trigger.attr('aria-expanded', 'true');
            this.$dropdown.show();
            this.$container.addClass('mmm-open');
            if (this.$searchInput) {
                this.$searchInput.focus().select();
            } else {
                this.$optionsList.focus();
            }
            this.filterOptions();
            this.updateSelectionHighlight();
        }

        close() {
            if (!this.isOpen) return;
            this.isOpen = false;
            this.$trigger.attr('aria-expanded', 'false');
            this.$dropdown.hide();
            this.$container.removeClass('mmm-open');
            this.focusedIndex = -1;
            this.optionItems.forEach(item => item.$el.removeClass('mmm-focused'));
            if (this.$searchInput) {
                this.$searchInput.val('');
            }
        }

        filterOptions() {
            if (!this.$searchInput) return;
            const query = this.$searchInput.val().trim().toLowerCase();
            const groupsToShow = new Set();

            this.optionItems.forEach(item => {
                const matches = !query || this.getItemSearchText(item).includes(query);

                if (matches) {
                    item.$el.show();
                    if (item.$groupTitle) {
                        groupsToShow.add(item.$groupTitle[0]);
                        groupsToShow.add(item.$groupContainer[0]);
                    }
                } else {
                    item.$el.hide();
                }
            });

            // Second pass: hide empty groups
            this.optionItems.forEach(item => {
                if (item.$groupTitle) {
                    if (groupsToShow.has(item.$groupTitle[0])) {
                        item.$groupTitle.show();
                        item.$groupContainer.show();
                    } else {
                        item.$groupTitle.hide();
                        item.$groupContainer.hide();
                    }
                }
            });

            this.focusedIndex = -1;
            this.optionItems.forEach(item => item.$el.removeClass('mmm-focused'));
        }

        getVisibleItems() {
            return this.optionItems.filter(item => item.$el.is(':visible'));
        }

        handleSearchKeyDown(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();
                this.close();
                this.$trigger.focus();
                return;
            }

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                e.stopPropagation();
                this.$optionsList.focus();
                this.focusedIndex = e.key === 'ArrowDown' ? 0 : this.getVisibleItems().length - 1;
                this.updateFocusHighlight(this.getVisibleItems());
            }
        }

        handleKeyDown(e) {
            if ($(e.target).hasClass('mmm-icon-selector-search')) {
                return;
            }

            if (!this.isOpen) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    this.open();
                }
                return;
            }

            const visibleItems = this.getVisibleItems();
            if (!visibleItems.length) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    this.close();
                    this.$trigger.focus();
                }
                return;
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.focusedIndex = (this.focusedIndex + 1) % visibleItems.length;
                    this.updateFocusHighlight(visibleItems);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    this.focusedIndex = (this.focusedIndex - 1 + visibleItems.length) % visibleItems.length;
                    this.updateFocusHighlight(visibleItems);
                    break;

                case 'Enter':
                    e.preventDefault();
                    if (this.focusedIndex >= 0 && this.focusedIndex < visibleItems.length) {
                        this.selectValue(visibleItems[this.focusedIndex].value);
                    }
                    break;

                case 'Escape':
                    e.preventDefault();
                    this.close();
                    this.$trigger.focus();
                    break;

                case 'Tab':
                    // Tab naturally closes the dropdown
                    this.close();
                    break;
            }
        }

        updateFocusHighlight(visibleItems) {
            this.optionItems.forEach(item => item.$el.removeClass('mmm-focused'));
            if (this.focusedIndex >= 0 && this.focusedIndex < visibleItems.length) {
                const item = visibleItems[this.focusedIndex];
                item.$el.addClass('mmm-focused');
                
                // Scroll into view inside listbox
                const listEl = this.$optionsList[0];
                const itemEl = item.$el[0];
                const scrollBottom = listEl.clientHeight + listEl.scrollTop;
                const itemBottom = itemEl.offsetTop + itemEl.clientHeight;

                if (itemEl.offsetTop < listEl.scrollTop) {
                    listEl.scrollTop = itemEl.offsetTop;
                } else if (itemBottom > scrollBottom) {
                    listEl.scrollTop = itemBottom - listEl.clientHeight;
                }
            }
        }

        destroy() {
            $(document).off('click.mmmIconSelector_' + this.$select.attr('name'));
            this.$container.remove();
            this.$select.show().removeData('mmmIconSelector');
        }
    }

    // Register jQuery plugin
    $.fn.mmmIconSelector = function() {
        return this.each(function() {
            const $el = $(this);
            let selector = $el.data('mmmIconSelector');
            if (!selector) {
                selector = new MMMIconSelector(this);
                $el.data('mmmIconSelector', selector);
            }
        });
    };

})(jQuery);
