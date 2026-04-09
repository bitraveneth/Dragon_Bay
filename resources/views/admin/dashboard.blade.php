@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6" data-dashboard-ajax data-dashboard-url="{{ route('admin.dashboard') }}" data-currency-code="{{ $currencyCode ?? config('app.currency', 'BDT') }}">
    <div class="col-span-12">
      <x-ecommerce.ecommerce-metrics
          :agent-count="$agentCount ?? 0"
          :total-order-count="$totalOrderCount ?? 0"
          :return-order-count="$returnOrderCount ?? 0"
          :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
          :monthly-revenue="$monthlyAchieved ?? 0"
          :outstanding-receivables="$outstandingReceivables ?? 0"
          :pending-delivery-count="$pendingDeliveryCount ?? 0"
          :today-production-qty="$todayProductionQty ?? 0"
          :low-stock-alert-count="$lowStockAlertCount ?? 0"
      />
    </div>

    <div class="col-span-12 xl:col-span-7">
      <x-ecommerce.monthly-sale
          :month-labels="$monthLabels ?? []"
          :monthly-orders="$monthlyOrders ?? []"
          :sales-range-months="$salesRangeMonths ?? 12"
      />
    </div>

    <div class="col-span-12 xl:col-span-5 xl:h-full">
        <x-ecommerce.monthly-target
            :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
            :period-label="$currentMonthLabel ?? now()->format('F Y')"
            :target-basis="$monthlyTargetBasis ?? 'No monthly order target configured'"
            :monthly-sales-target="$monthlySalesTarget ?? 0"
            :monthly-achieved="$monthlyAchieved ?? 0"
            :today-achieved="$todayAchieved ?? 0"
            :progress-percent="$monthlyTargetProgress ?? 0"
            :target-month-options="$targetMonthOptions ?? []"
        />
    </div>

    <div class="col-span-12">
      <x-ecommerce.statistics-chart />
    </div>

    <div class="col-span-12">
      <x-ecommerce.recent-orders
          :orders="$recentOrders ?? collect()"
          :currency-code="$currencyCode ?? config('app.currency', 'BDT')"
      />
    </div>
  </div>
@endsection

@push('scripts')
    @if(isset($chartDays) && $chartDays instanceof \Illuminate\Support\Collection && $chartDays->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.ApexCharts) return;

                const dashboardRoot = document.querySelector('[data-dashboard-ajax]');
                if (!dashboardRoot) return;

                const currencyCode = dashboardRoot.dataset.currencyCode || @json($currencyCode ?? config('app.currency', 'BDT'));
                let currentMonthlySaleData = {
                    labels: @json($monthLabels),
                    orders: @json($monthlyOrders),
                };
                let monthlySalesResizeFrame = null;
                let monthlySalesCardObserver = null;
                const initialStats = {
                    labels: @json($chartDays->pluck('label')),
                    orders: @json($chartDays->pluck('orders')),
                    production: @json($chartDays->pluck('production')),
                    revenue: @json($chartDays->pluck('revenue')),
                };

                const getStatsSeries = (mode, statsPayload) => {
                    if (mode === 'sales') {
                        return [{ name: 'Orders', data: statsPayload.orders }];
                    }

                    if (mode === 'production') {
                        return [{ name: 'Operational Output', data: statsPayload.production }];
                    }

                    if (mode === 'revenue') {
                        return [{ name: `Billed Value (${currencyCode})`, data: statsPayload.revenue }];
                    }

                    return [
                        { name: 'Orders', data: statsPayload.orders },
                        { name: 'Operational Output', data: statsPayload.production },
                        { name: `Billed Value (${currencyCode})`, data: statsPayload.revenue },
                    ];
                };

                const renderMonthlySalesChart = (labels, orders) => {
                    const el = document.querySelector('#chartOne');
                    if (!el) return;

                    currentMonthlySaleData = { labels, orders };

                    if (window.dashboardMonthlySalesChart) {
                        window.dashboardMonthlySalesChart.destroy();
                    }

                    const chart = new window.ApexCharts(el, {
                        chart: {
                            type: 'bar',
                            height: 220,
                            toolbar: { show: false },
                            foreColor: '#667085',
                            redrawOnParentResize: true,
                            redrawOnWindowResize: true,
                            parentHeightOffset: 0,
                        },
                        series: [{
                            name: 'Client Orders',
                            data: orders,
                        }],
                        colors: ['#465FFF'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '39%',
                                borderRadius: 5,
                                borderRadiusApplication: 'end',
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: {
                            show: true,
                            width: 3,
                            colors: ['transparent']
                        },
                        grid: {
                            borderColor: '#E4E7EC',
                            strokeDashArray: 4,
                            yaxis: { lines: { show: true } }
                        },
                        xaxis: {
                            categories: labels,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                trim: true,
                                hideOverlappingLabels: false,
                                rotate: 0,
                                style: { fontSize: '11px' },
                            },
                        },
                        yaxis: {
                            labels: { style: { fontSize: '11px' } },
                            title: { text: undefined },
                        },
                        tooltip: {
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            y: {
                                formatter: (val) => val,
                            }
                        },
                        fill: { opacity: 0.95 },
                        legend: { show: false },
                        responsive: [
                            {
                                breakpoint: 1024,
                                options: {
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '48%',
                                        }
                                    },
                                    xaxis: {
                                        labels: {
                                            style: { fontSize: '10px' },
                                        }
                                    },
                                },
                            },
                            {
                                breakpoint: 640,
                                options: {
                                    plotOptions: {
                                        bar: {
                                            columnWidth: '58%',
                                        }
                                    },
                                    xaxis: {
                                        labels: {
                                            rotate: -35,
                                            trim: true,
                                            style: { fontSize: '10px' },
                                        }
                                    },
                                },
                            },
                        ],
                    });

                    chart.render();
                    window.dashboardMonthlySalesChart = chart;
                };

                const scheduleMonthlySalesRerender = () => {
                    if (monthlySalesResizeFrame !== null) {
                        window.cancelAnimationFrame(monthlySalesResizeFrame);
                    }

                    monthlySalesResizeFrame = window.requestAnimationFrame(() => {
                        monthlySalesResizeFrame = null;
                        renderMonthlySalesChart(
                            currentMonthlySaleData.labels || [],
                            currentMonthlySaleData.orders || []
                        );
                    });
                };

                const bindMonthlySalesResponsiveResize = () => {
                    const card = document.querySelector('#dashboard-monthly-sales-card');
                    if (!card) return;

                    monthlySalesCardObserver?.disconnect();
                    monthlySalesCardObserver = new ResizeObserver(() => {
                        scheduleMonthlySalesRerender();
                    });
                    monthlySalesCardObserver.observe(card);

                    window.addEventListener('resize', scheduleMonthlySalesRerender);
                    window.addEventListener('app:sidebar-changed', () => {
                        window.setTimeout(scheduleMonthlySalesRerender, 320);
                    });
                };

                const renderMonthlyTargetChart = (progressValue) => {
                    const el = document.querySelector('#chartTwo');
                    if (!el) return;

                    if (window.dashboardTargetChart) {
                        window.dashboardTargetChart.destroy();
                    }

                    const chart = new window.ApexCharts(el, {
                        series: [progressValue],
                        colors: ['#465FFF'],
                        chart: {
                            fontFamily: window.erpUiFontStack || 'Outfit, sans-serif',
                            type: 'radialBar',
                            height: 290,
                            sparkline: { enabled: true },
                        },
                        plotOptions: {
                            radialBar: {
                                startAngle: -90,
                                endAngle: 90,
                                hollow: { size: '80%' },
                                track: {
                                    background: '#E4E7EC',
                                    strokeWidth: '100%',
                                    margin: 5,
                                },
                                dataLabels: {
                                    name: { show: false },
                                    value: {
                                        fontSize: '42px',
                                        fontWeight: '600',
                                        offsetY: 72,
                                        color: '#1D2939',
                                        formatter: (val) => `${Math.round(val)}%`,
                                    },
                                },
                            },
                        },
                        fill: {
                            type: 'solid',
                            colors: ['#465FFF'],
                        },
                        stroke: { lineCap: 'round' },
                        labels: ['Progress'],
                    });

                    chart.render();
                    window.dashboardTargetChart = chart;
                };

                const renderStatsChart = (statsPayload) => {
                    const el = document.querySelector('#chartThree');
                    if (!el) return;

                    if (window.dashboardStatsChart) {
                        window.dashboardStatsChart.destroy();
                    }

                    const selectedTab = document.querySelector('[data-stats-tab].dashboard-stats-active')?.getAttribute('data-stats-tab') || 'overview';

                    const chart = new window.ApexCharts(el, {
                        chart: {
                            type: 'area',
                            height: 220,
                            toolbar: { show: false },
                            foreColor: '#667085',
                            dropShadow: {
                                enabled: true,
                                top: 4,
                                left: 0,
                                blur: 3,
                                opacity: 0.1
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2.5
                        },
                        dataLabels: { enabled: false },
                        grid: {
                            borderColor: '#E4E7EC',
                            strokeDashArray: 4,
                            row: {
                                opacity: 0.02
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 0.7,
                                opacityFrom: 0.25,
                                opacityTo: 0,
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 3,
                            strokeWidth: 0
                        },
                        tooltip: {
                            shared: true,
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            y: {
                                formatter: (val, opts) => {
                                    const seriesName = opts.series[opts.seriesIndex]?.name || '';
                                    const currencyPrefix = seriesName.includes('Revenue') ? `${currencyCode} ` : '';
                                    return `${currencyPrefix}${val}`;
                                }
                            }
                        },
                        xaxis: {
                            categories: statsPayload.labels,
                            labels: { style: { fontSize: '11px' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: { style: { fontSize: '11px' } }
                        },
                        colors: ['#465FFF', '#12B76A', '#F79009'],
                        series: getStatsSeries(selectedTab, statsPayload),
                        legend: {
                            show: getStatsSeries(selectedTab, statsPayload).length > 1,
                            position: 'top',
                            horizontalAlign: 'left',
                            fontSize: '11px',
                            markers: { radius: 12 }
                        }
                    });

                    chart.render();
                    window.dashboardStatsChart = chart;
                };

                const setActiveStatsTab = (mode) => {
                    const tabs = document.querySelectorAll('[data-stats-tab]');
                    tabs.forEach((btn) => {
                        const isActive = (btn.getAttribute('data-stats-tab') || 'overview') === mode;
                        btn.classList.toggle('dashboard-stats-active', isActive);
                    });
                };

                const syncDashboardWidgets = (payload) => {
                    const monthlyTargetCard = document.querySelector('#dashboard-monthly-target-card');
                    if (monthlyTargetCard && payload.monthlyTarget) {
                        monthlyTargetCard.querySelector('[data-target-amount]')?.replaceChildren(document.createTextNode(`${currencyCode} ${Number(payload.monthlyTarget.targetValue).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`));
                        monthlyTargetCard.querySelector('[data-target-basis]')?.replaceChildren(document.createTextNode(payload.monthlyTarget.targetBasis));
                        renderMonthlyTargetChart(Number(payload.monthlyTarget.progressValue || 0));
                    }

                    if (payload.monthlySale) {
                        renderMonthlySalesChart(payload.monthlySale.labels || [], payload.monthlySale.orders || []);
                    }
                };

                const fetchDashboardData = async (section) => {
                    const salesRangeSelect = document.querySelector('[data-dashboard-sales-range]');
                    const targetMonthSelect = document.querySelector('[data-dashboard-target-month]');
                    if (!salesRangeSelect || !targetMonthSelect) return;

                    const params = new URLSearchParams({
                        ajax: '1',
                        sales_range: salesRangeSelect.value,
                        target_month: targetMonthSelect.value,
                    });

                    if (section) {
                        params.set('section', section);
                    }

                    const url = `${dashboardRoot.dataset.dashboardUrl}?${params.toString()}`;
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Dashboard request failed');
                    }

                    const payload = await response.json();
                    syncDashboardWidgets(payload);

                    const nextUrl = new URL(window.location.href);
                    nextUrl.searchParams.set('sales_range', salesRangeSelect.value);
                    nextUrl.searchParams.set('target_month', targetMonthSelect.value);
                    window.history.replaceState({}, '', nextUrl.toString());
                };

                renderMonthlySalesChart(@json($monthLabels), @json($monthlyOrders));
                renderMonthlyTargetChart(@json($monthlyTargetProgress ?? 0));
                renderStatsChart(initialStats);
                setActiveStatsTab('overview');
                bindMonthlySalesResponsiveResize();

                const salesRangeSelect = document.querySelector('[data-dashboard-sales-range]');
                const targetMonthSelect = document.querySelector('[data-dashboard-target-month]');

                salesRangeSelect?.addEventListener('change', () => {
                    fetchDashboardData('sales').catch(() => {
                        window.location.href = `${dashboardRoot.dataset.dashboardUrl}?sales_range=${encodeURIComponent(salesRangeSelect.value)}&target_month=${encodeURIComponent(targetMonthSelect?.value || '')}`;
                    });
                });

                targetMonthSelect?.addEventListener('change', () => {
                    fetchDashboardData('target').catch(() => {
                        window.location.href = `${dashboardRoot.dataset.dashboardUrl}?sales_range=${encodeURIComponent(salesRangeSelect?.value || 12)}&target_month=${encodeURIComponent(targetMonthSelect.value)}`;
                    });
                });

                const tabs = document.querySelectorAll('[data-stats-tab]');
                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const mode = btn.getAttribute('data-stats-tab') || 'overview';
                        setActiveStatsTab(mode);
                        window.dashboardStatsChart?.updateOptions({
                            series: getStatsSeries(mode, initialStats),
                            legend: {
                                show: getStatsSeries(mode, initialStats).length > 1,
                                position: 'top',
                                horizontalAlign: 'left',
                                fontSize: '11px',
                                markers: { radius: 12 },
                            },
                        });
                    });
                });
            });
        </script>
    @endif
@endpush
