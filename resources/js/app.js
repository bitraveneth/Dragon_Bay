import './bootstrap';
import './invoice';
import { registerSystemTour } from './system-tour';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.erpUiFontStack = 'Outfit, Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Ubuntu, Cantarell, "Helvetica Neue", Arial, sans-serif';

const detectClientPlatform = () => {
    const rawPlatform = (
        navigator.userAgentData?.platform ||
        navigator.platform ||
        navigator.userAgent ||
        ''
    ).toLowerCase();

    if (rawPlatform.includes('mac') || rawPlatform.includes('iphone') || rawPlatform.includes('ipad')) {
        return 'mac';
    }

    if (rawPlatform.includes('win')) {
        return 'windows';
    }

    if (rawPlatform.includes('linux') || rawPlatform.includes('x11')) {
        return 'linux';
    }

    return 'other';
};

window.erpPlatform = detectClientPlatform();

document.addEventListener('alpine:init', () => {
    registerSystemTour(Alpine, window.erpTourSteps || []);
});

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.dataset.platform = window.erpPlatform;
    if (document.body) {
        document.body.dataset.platform = window.erpPlatform;
    }

    const commandModifier = window.erpPlatform === 'mac' ? '⌘' : 'Ctrl';
    document.querySelectorAll('[data-shortcut-mod]').forEach((el) => {
        el.textContent = commandModifier;
    });
    document.querySelectorAll('[data-command-shortcut]').forEach((el) => {
        el.setAttribute('aria-label', `Open command palette (${commandModifier} K)`);
        el.setAttribute('title', `Open command palette (${commandModifier} K)`);
    });


        // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    // chartOne (monthly sale) is rendered with dynamic ERP data via
    // an inline script on the dashboard, so we skip the TailAdmin
    // demo initialiser here.
    if (document.querySelector('#chartTwo') && !document.querySelector('[data-dashboard-ajax]')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    // Dashboard statistics chart (#chartThree) is now rendered
    // from Blade with real ERP data, so we no longer load the
    // TailAdmin demo chart-3.js here to avoid double charts.
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init (only used on TailAdmin calendar examples)
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }

    // Sidebar groups
    const toggles = document.querySelectorAll('[data-collapse-toggle]');

    toggles.forEach((button) => {
        const group = button.closest('[data-collapsible]');
        if (!group) {
            return;
        }

        const icon = button.querySelector('.sidebar-toggle-icon');

        const openIcon = button.dataset.openIcon ?? '−';
        const closedIcon = button.dataset.closedIcon ?? '+';

        const syncState = (isExpanded) => {
            button.setAttribute('aria-expanded', isExpanded.toString());
            if (icon) {
                icon.textContent = isExpanded ? openIcon : closedIcon;
            }
        };

        const initialExpanded = !group.classList.contains('collapsed');
        syncState(initialExpanded);

        button.addEventListener('click', () => {
            const collapsed = group.classList.toggle('collapsed');
            syncState(!collapsed);
        });
    });

    const setupDropdown = (containerSelector, toggleSelector, openClass) => {
        const containers = document.querySelectorAll(containerSelector);
        if (!containers.length) {
            return;
        }

        containers.forEach((container) => {
            const toggle = container.querySelector(toggleSelector);
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                // Close any other open containers of the same type
                containers.forEach((other) => {
                    if (other !== container) {
                        other.classList.remove(openClass);
                    }
                });

                container.classList.toggle(openClass);
            });

            container.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        });

        document.addEventListener('click', () => {
            containers.forEach((container) => container.classList.remove(openClass));
        });
    };

    // Header user dropdown
    setupDropdown('.header-user', '.header-user-toggle', 'header-user--open');

    // Header alerts dropdown
    setupDropdown('.header-alert', '.header-alert-toggle', 'header-alert--open');

    // Header alerts close buttons
    document.querySelectorAll('.header-alert-menu-close').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const container = btn.closest('.header-alert');
            if (container) {
                container.classList.remove('header-alert--open');
            }
        });
    });

    // Warehouse card actions dropdown
    setupDropdown('.warehouse-card-actions', '.warehouse-card-actions-toggle', 'warehouse-card-actions--open');

    // ---------------------------------------------------------------------
    // Header command palette / search (Cmd/Ctrl + K)
    // ---------------------------------------------------------------------

    const commandInput = document.querySelector('[data-command-search]');
    const commandResults = document.querySelector('[data-command-results]');

    if (commandInput && commandResults) {
        let items = [];
        try {
            items = JSON.parse(commandInput.dataset.searchIndex || '[]');
        } catch (e) {
            // ignore malformed JSON
        }

        let filtered = [];
        let activeIndex = -1;

        const renderResults = () => {
            if (!commandInput.value.trim() || !filtered.length) {
                commandResults.classList.add('hidden');
                commandResults.innerHTML = '';
                activeIndex = -1;
                return;
            }

            commandResults.innerHTML = `
                <ul class="max-h-80 overflow-y-auto custom-scrollbar divide-y divide-gray-100 dark:divide-gray-800">
                    ${filtered
                        .map(
                            (item, index) => `
                        <li
                            class="command-item flex cursor-pointer items-center justify-between px-4 py-2.5 text-sm ${
                                index === activeIndex
                                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-200'
                                    : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800/60'
                            }"
                            data-index="${index}"
                            data-path="${item.path}"
                        >
                            <div class="flex flex-col">
                                <span class="font-medium">${item.label}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">${item.group}</span>
                            </div>
                            <span class="ml-3 text-[10px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                Go →
                            </span>
                        </li>`
                        )
                        .join('')}
                </ul>
            `;

            commandResults.classList.remove('hidden');

            // Click navigation
            commandResults.querySelectorAll('.command-item').forEach((el) => {
                el.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    const path = el.dataset.path;
                    if (path) {
                        window.location.href = path;
                    }
                });
            });
        };

        const updateFiltered = () => {
            const q = commandInput.value.trim().toLowerCase();
            if (!q) {
                filtered = [];
                renderResults();
                return;
            }

            filtered = items
                .filter((item) => {
                    const haystack = `${item.label} ${item.group} ${item.path}`.toLowerCase();
                    return haystack.includes(q);
                })
                .slice(0, 10);
            activeIndex = filtered.length ? 0 : -1;
            renderResults();
        };

        commandInput.addEventListener('input', () => {
            updateFiltered();
        });

        commandInput.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                if (filtered.length) {
                    event.preventDefault();
                    activeIndex = (activeIndex + 1) % filtered.length;
                    renderResults();
                }
            } else if (event.key === 'ArrowUp') {
                if (filtered.length) {
                    event.preventDefault();
                    activeIndex = (activeIndex - 1 + filtered.length) % filtered.length;
                    renderResults();
                }
            } else if (event.key === 'Enter') {
                if (filtered.length && activeIndex >= 0) {
                    event.preventDefault();
                    const item = filtered[activeIndex];
                    if (item?.path) {
                        window.location.href = item.path;
                    }
                }
            } else if (event.key === 'Escape') {
                commandResults.classList.add('hidden');
                activeIndex = -1;
                commandInput.blur();
            }
        });

        // Global shortcut Cmd/Ctrl + K
        document.addEventListener('keydown', (event) => {
            const isMac = window.erpPlatform === 'mac';
            const meta = isMac ? event.metaKey : event.ctrlKey;
            if (meta && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                commandInput.focus();
                commandInput.select();
            }
        });

        // Close results when clicking outside
        document.addEventListener('click', (event) => {
            if (!commandResults.classList.contains('hidden')) {
                const container = document.querySelector('[data-command-container]');
                if (container && !container.contains(event.target)) {
                    commandResults.classList.add('hidden');
                    activeIndex = -1;
                }
            }
        });
    }

    // Dismiss flash messages
    document.querySelectorAll('.flash-close').forEach((btn) => {
        btn.addEventListener('click', () => {
            const flash = btn.closest('.flash');
            if (flash) {
                flash.remove();
            }
        });
    });

    // Tag input with chips + suggestions
    document.querySelectorAll('[data-tag-input]').forEach((wrapper) => {
        const field = wrapper.querySelector('[data-tag-field]');
        const hidden = wrapper.querySelector('[data-tag-hidden]');
        const chipsContainer = wrapper.querySelector('[data-tag-chips]');
        const suggestionsBox = wrapper.querySelector('[data-tag-suggestions]');

        if (!field || !hidden || !chipsContainer || !suggestionsBox) {
            return;
        }

        const allOptions = JSON.parse(wrapper.dataset.tagOptions || '[]');
        let tags = [];

        const normalise = (value) => value.trim().replace(/\s+/g, ' ').toLowerCase();

        const renderChips = () => {
            chipsContainer.innerHTML = '';
            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';
                chip.textContent = tag;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'tag-chip-remove';
                remove.textContent = '×';
                remove.addEventListener('click', () => {
                    tags.splice(index, 1);
                    syncHidden();
                    renderChips();
                });

                chip.appendChild(remove);
                chipsContainer.appendChild(chip);
            });
        };

        const syncHidden = () => {
            hidden.value = tags.join(', ');
        };

        const addTag = (value) => {
            const trimmed = value.trim();
            if (!trimmed) return;

            const normalised = normalise(trimmed);
            if (tags.some((t) => normalise(t) === normalised)) {
                return;
            }

            tags.push(trimmed);
            syncHidden();
            renderChips();
        };

        const parseInitial = () => {
            if (!hidden.value) return;
            hidden.value.split(',').forEach((piece) => addTag(piece));
        };

        const renderSuggestions = (query) => {
            const q = normalise(query);
            const existing = new Set(tags.map((t) => normalise(t)));

            const matches = allOptions.filter((opt) => {
                const n = normalise(opt);
                if (existing.has(n)) {
                    return false;
                }
                return !q || n.includes(q);
            });

            suggestionsBox.innerHTML = '';
            if (!matches.length || !q) {
                suggestionsBox.classList.remove('open');
                return;
            }

            matches.forEach((match) => {
                const item = document.createElement('div');
                item.className = 'tag-suggestion';
                item.textContent = match;
                item.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    addTag(match);
                    field.value = '';
                    suggestionsBox.classList.remove('open');
                });
                suggestionsBox.appendChild(item);
            });

            suggestionsBox.classList.add('open');
        };

        field.addEventListener('input', () => {
            const value = field.value;
            if (value.includes(',')) {
                value.split(',').forEach((piece) => addTag(piece));
                field.value = '';
                suggestionsBox.classList.remove('open');
                return;
            }
            renderSuggestions(value);
        });

        field.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === 'Tab') {
                if (field.value.trim() !== '') {
                    event.preventDefault();
                    addTag(field.value);
                    field.value = '';
                    suggestionsBox.classList.remove('open');
                }
            } else if (event.key === 'Backspace' && field.value === '' && tags.length) {
                tags.pop();
                syncHidden();
                renderChips();
            }
        });

        field.addEventListener('blur', () => {
            if (field.value.trim() !== '') {
                addTag(field.value);
                field.value = '';
            }
            setTimeout(() => {
                suggestionsBox.classList.remove('open');
            }, 150);
        });

        parseInitial();
        renderChips();
    });

    // Product / material type specific fields
    const typeSelect = document.getElementById('product_type');
    if (typeSelect) {
        const syncProductTypeFields = () => {
            const type = typeSelect.value || 'finished';

            document.querySelectorAll('[data-product-type="finished-only"]').forEach((el) => {
                el.style.display = type === 'finished' ? '' : 'none';
            });
            document.querySelectorAll('[data-product-type="material-only"]').forEach((el) => {
                el.style.display = type === 'finished' ? 'none' : '';
            });

            // For in‑house steps supplier is usually not relevant – disable the field for clarity.
            const supplierInput = document.querySelector('[data-material-supplier]');
            const supplierWrapper = document.querySelector('[data-material-supplier-wrapper]');
            if (supplierInput) {
                if (type === 'inhouse') {
                    supplierInput.value = '';
                    supplierInput.disabled = true;
                    if (supplierWrapper) {
                        supplierWrapper.style.display = 'none';
                    }
                } else {
                    supplierInput.disabled = false;
                    if (supplierWrapper) {
                        supplierWrapper.style.display = '';
                    }
                }
            }

            // Update UOM placeholder by material type
            const uomInput = document.querySelector('[data-material-uom]');
            if (uomInput) {
                if (type === 'raw') {
                    uomInput.placeholder = 'e.g., piece, kg, liter';
                } else if (type === 'service') {
                    uomInput.placeholder = 'e.g., day, trip, shift, service';
                } else if (type === 'inhouse') {
                    uomInput.placeholder = 'e.g., piece, batch, case';
                }
            }
        };

        syncProductTypeFields();
        typeSelect.addEventListener('change', syncProductTypeFields);
    }

    // Preset + custom combo fields (size, volume)
    const wirePresetCombo = (selectId, inputId) => {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        if (!select || !input) {
            return;
        }

        const customValue = '__custom';

        const syncVisibility = () => {
            if (select.value === customValue) {
                input.style.display = '';
            } else {
                input.style.display = 'none';
                input.value = select.value || '';
            }
        };

        select.addEventListener('change', syncVisibility);
        syncVisibility();
    };

    wirePresetCombo('size_select', 'size');
    wirePresetCombo('volume_ml_select', 'volume_ml');

    // Generate production order number from button
    const orderBtn = document.getElementById('btn-generate-order-number');
    const orderInput = document.getElementById('order_number');
    const batchSelect = document.getElementById('batch_id');
    if (orderBtn && orderInput) {
        orderBtn.addEventListener('click', () => {
            const params = new URLSearchParams();
            if (batchSelect && batchSelect.value) {
                params.set('batch_id', batchSelect.value);
            }

            fetch(`/admin/production/order-number?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.order_number) {
                        orderInput.value = data.order_number;
                    }
                })
                .catch(() => {
                    // Fail silently; server-side auto-generation will still work on submit.
                });
        });
    }

    // Login password visibility toggle
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('toggle-password');
    if (passwordInput && toggleButton) {
        const showIcon = toggleButton.querySelector('.password-toggle-icon-show');
        const hideIcon = toggleButton.querySelector('.password-toggle-icon-hide');

        if (showIcon && hideIcon) {
            toggleButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                showIcon.style.display = isHidden ? 'none' : 'block';
                hideIcon.style.display = isHidden ? 'block' : 'none';
                toggleButton.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        }
    }
});
