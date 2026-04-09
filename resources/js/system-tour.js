export function registerSystemTour(Alpine, stepBlueprints = []) {
    Alpine.store('tour', {
        storageKey: 'erp-system-tour-v1',
        launcherOpen: false,
        isActive: false,
        activeIndex: 0,
        hasSavedProgress: false,
        stepBlueprints: [],
        steps: [],
        spotlight: { top: 0, left: 0, width: 0, height: 0 },
        tooltip: { top: 0, left: 0, right: 'auto', bottom: 'auto' },

        init() {
            this.stepBlueprints = Array.isArray(stepBlueprints) ? stepBlueprints : [];
            this.steps = [...this.stepBlueprints];

            window.addEventListener('resize', () => this.refreshPosition());
            window.addEventListener('scroll', () => this.refreshPosition(), true);

            const resume = () => {
                window.setTimeout(() => this.resumeIfNeeded(), 180);
            };

            window.addEventListener('app:content-visible', resume, { once: true });

            if (document.readyState === 'complete') {
                resume();
            } else {
                window.addEventListener('load', resume, { once: true });
            }

            this.syncSavedProgressFlag();
        },

        currentStep() {
            return this.steps[this.activeIndex] ?? null;
        },

        isPageLevelStep() {
            return this.currentStep()?.selector === '[data-tour="page-content"]';
        },

        usesSpotlight() {
            return this.currentStep()?.spotlight !== false;
        },

        toggleLauncher() {
            this.launcherOpen = !this.launcherOpen;
        },

        openLauncher() {
            this.launcherOpen = true;
        },

        closeLauncher() {
            this.launcherOpen = false;
        },

        start() {
            this.closeLauncher();
            this.steps = [...this.stepBlueprints];
            this.isActive = true;
            this.activeIndex = 0;
            this.persistState();
            this.syncSavedProgressFlag();
            this.goToCurrentStep();
        },

        end() {
            this.cleanupTarget();
            this.isActive = false;
            this.activeIndex = 0;
            this.spotlight = { top: 0, left: 0, width: 0, height: 0 };
            this.tooltip = { top: 0, left: 0, right: 'auto', bottom: 'auto' };
            this.clearState();
            this.syncSavedProgressFlag();
        },

        next() {
            if (this.activeIndex >= this.steps.length - 1) {
                this.end();
                return;
            }

            this.activeIndex += 1;
            this.persistState();
            this.goToCurrentStep();
        },

        previous() {
            if (this.activeIndex <= 0) {
                return;
            }

            this.activeIndex -= 1;
            this.persistState();
            this.goToCurrentStep();
        },

        restart() {
            this.clearState();
            this.start();
        },

        loadState() {
            try {
                return JSON.parse(window.sessionStorage.getItem(this.storageKey) || 'null');
            } catch (error) {
                return null;
            }
        },

        persistState() {
            window.sessionStorage.setItem(this.storageKey, JSON.stringify({
                active: true,
                activeIndex: this.activeIndex,
            }));
        },

        clearState() {
            window.sessionStorage.removeItem(this.storageKey);
        },

        syncSavedProgressFlag() {
            const saved = this.loadState();
            this.hasSavedProgress = !!(saved && saved.active);
        },

        resumeIfNeeded() {
            const saved = this.loadState();
            if (!saved || !saved.active || this.isActive) {
                return;
            }

            this.steps = [...this.stepBlueprints];
            if (!this.steps.length) {
                this.clearState();
                this.syncSavedProgressFlag();
                return;
            }

            this.isActive = true;
            this.activeIndex = Math.min(Math.max(Number(saved.activeIndex ?? 0), 0), this.steps.length - 1);
            this.syncSavedProgressFlag();
            this.goToCurrentStep();
        },

        normalizePath(path) {
            if (!path) {
                return '/';
            }

            try {
                const url = new URL(path, window.location.origin);
                const normalized = url.pathname.replace(/\/+$/, '');
                return normalized === '' ? '/' : normalized;
            } catch (error) {
                const normalized = String(path).replace(/\/+$/, '');
                return normalized === '' ? '/' : normalized;
            }
        },

        goToCurrentStep() {
            this.cleanupTarget();

            const step = this.currentStep();
            if (!step) {
                this.end();
                return;
            }

            this.prepareStep(step);

            if (this.normalizePath(window.location.pathname) !== this.normalizePath(step.path)) {
                window.location.href = step.path;
                return;
            }

            this.showStep();
        },

        showStep() {
            const step = this.currentStep();
            if (!step) {
                this.end();
                return;
            }

            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(() => {
                    const target = this.getTarget(step);
                    if (!target) {
                        this.end();
                        return;
                    }

                    target.scrollIntoView({
                        block: 'center',
                        inline: 'center',
                        behavior: 'smooth',
                    });

                    window.requestAnimationFrame(() => {
                        if (!this.isPageLevelStep() && this.usesSpotlight()) {
                            target.classList.add('tour-target-active');
                        }
                        this.positionElements(target);
                    });
                });
            });
        },

        prepareStep() {
            const sidebar = Alpine.store('sidebar');

            if (window.innerWidth < 1280) {
                sidebar.setMobileOpen(true);
            } else {
                sidebar.isExpanded = true;
                sidebar.isMobileOpen = false;
            }

            this.closeLauncher();

            document.querySelectorAll('.header-alert').forEach((el) => el.classList.remove('header-alert--open'));
            document.querySelectorAll('.header-user').forEach((el) => el.classList.remove('header-user--open'));
        },

        getTarget(step) {
            if (!step) {
                return null;
            }

            return document.querySelector(step.selector)
                || document.querySelector('[data-tour="page-content"]');
        },

        isElementVisible(element) {
            if (!element) {
                return false;
            }

            const rect = element.getBoundingClientRect();
            const styles = window.getComputedStyle(element);

            return rect.width > 0
                && rect.height > 0
                && styles.visibility !== 'hidden'
                && styles.display !== 'none';
        },

        positionElements(target) {
            const step = this.currentStep();
            if (step?.selector === '[data-tour="page-content"]' || step?.spotlight === false) {
                this.spotlight = { top: 0, left: 0, width: 0, height: 0 };
                this.tooltip = { top: 'auto', left: 'auto', right: '24px', bottom: '24px' };
                return;
            }

            const rect = target.getBoundingClientRect();
            const tooltipEl = document.querySelector('[data-tour-tooltip]');
            const measuredTooltipRect = tooltipEl?.getBoundingClientRect() ?? { width: 0, height: 0 };
            const tooltipRect = {
                width: measuredTooltipRect.width > 40 ? measuredTooltipRect.width : (tooltipEl?.offsetWidth || 352),
                height: measuredTooltipRect.height > 40 ? measuredTooltipRect.height : (tooltipEl?.offsetHeight || 320),
            };
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const gap = 16;
            const padding = 10;
            const largeTarget = rect.width > (viewportWidth * 0.55) || rect.height > (viewportHeight * 0.55);

            let top = rect.top;
            let left = rect.right + gap;

            if (largeTarget) {
                this.spotlight = { top: 0, left: 0, width: 0, height: 0 };
                this.tooltip = { top: 'auto', left: 'auto', right: '24px', bottom: '24px' };
                return;
            }

            this.spotlight = {
                top: Math.max(rect.top - padding, 8),
                left: Math.max(rect.left - padding, 8),
                width: rect.width + (padding * 2),
                height: rect.height + (padding * 2),
            };

            if ((left + tooltipRect.width) > (viewportWidth - 16)) {
                left = rect.left - tooltipRect.width - gap;
            }

            if (left < 16) {
                left = viewportWidth - tooltipRect.width - 24;
            }

            if ((top + tooltipRect.height) > (viewportHeight - 16)) {
                top = viewportHeight - tooltipRect.height - 24;
            }

            if (top < 16) {
                top = 16;
            }

            this.tooltip = { top: `${top}px`, left: `${left}px`, right: 'auto', bottom: 'auto' };
        },

        refreshPosition() {
            if (!this.isActive) {
                return;
            }

            const target = this.getTarget(this.currentStep());
            if (!target || !this.isElementVisible(target)) {
                return;
            }

            this.positionElements(target);
        },

        cleanupTarget() {
            document.querySelectorAll('.tour-target-active').forEach((element) => {
                element.classList.remove('tour-target-active');
            });
        },
    });
}
