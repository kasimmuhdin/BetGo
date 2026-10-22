import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement, BarController, BarElement, CategoryScale, DoughnutController,
    Filler, Legend, LinearScale, LineController, LineElement, PointElement, Tooltip,
);

const COLORS = { cyan: '#00c8ff', indigo: '#818cf8', amber: '#fbbf24', green: '#3ddc97' };

const full = new Intl.NumberFormat('en');
const compact = new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 });

// Dark theme + reduced motion for every chart.
Chart.defaults.color = '#9b9ba6';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = "'Figtree', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.animation = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 600 };
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Object.assign(Chart.defaults.plugins.tooltip, {
    backgroundColor: '#0f0d0d',
    borderColor: '#3a3a3d',
    borderWidth: 1,
    padding: 10,
    titleColor: '#ffffff',
    bodyColor: '#cfd0d8',
    displayColors: true,
    usePointStyle: true,
});

const base = { responsive: true, maintainAspectRatio: false };

/** Soft gradient under a line. Needs a 7-character hex colour. */
const fade = (color) => (context) => {
    const { ctx, chartArea } = context.chart;
    if (!chartArea) return 'transparent';
    const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
    g.addColorStop(0, color + '45');
    g.addColorStop(1, color + '00');
    return g;
};

const timeScales = (format = (v) => compact.format(v)) => ({
    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, maxRotation: 0, autoSkip: true } },
    y: { beginAtZero: true, ticks: { precision: 0, callback: format }, grid: { color: 'rgba(255,255,255,0.05)' } },
});

const centerText = {
    id: 'centerText',
    afterDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;
        const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
        const x = (chartArea.left + chartArea.right) / 2;
        const y = (chartArea.top + chartArea.bottom) / 2;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#ffffff';
        ctx.font = "800 28px 'Figtree', sans-serif";
        ctx.fillText(full.format(total), x, y - 8);
        ctx.fillStyle = '#9b9ba6';
        ctx.font = "500 12px 'Figtree', sans-serif";
        ctx.fillText('listings', x, y + 14);
        ctx.restore();
    },
};

function init(d) {
    const el = (id) => document.getElementById(id);

    // Revenue over time
    if (el('chart-revenue')) {
        new Chart(el('chart-revenue'), {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [{
                    label: 'Revenue (ETB)',
                    data: d.revenue,
                    borderColor: COLORS.cyan,
                    backgroundColor: fade(COLORS.cyan),
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                }],
            },
            options: {
                ...base,
                interaction: { mode: 'index', intersect: false },
                scales: timeScales(),
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => ` ${full.format(c.parsed.y)} ETB` } },
                },
            },
        });
    }

    // New users and new listings
    if (el('chart-growth')) {
        const line = (label, data, color) => ({
            label, data, borderColor: color, backgroundColor: color,
            tension: 0.35, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4,
        });

        new Chart(el('chart-growth'), {
            type: 'line',
            data: {
                labels: d.labels,
                datasets: [line('New users', d.users, COLORS.indigo), line('New listings', d.listings, COLORS.amber)],
            },
            options: {
                ...base,
                interaction: { mode: 'index', intersect: false },
                scales: timeScales(),
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }

    // Listings by status
    if (el('chart-status')) {
        new Chart(el('chart-status'), {
            type: 'doughnut',
            data: {
                labels: d.status.labels,
                datasets: [{
                    data: d.status.values,
                    backgroundColor: d.status.colors,
                    borderColor: '#1e1b1b',
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },
            options: {
                ...base,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (c) => {
                                const total = c.dataset.data.reduce((a, b) => a + b, 0);
                                return ` ${c.label}: ${full.format(c.parsed)} (${Math.round((c.parsed / total) * 100)}%)`;
                            },
                        },
                    },
                },
            },
            plugins: [centerText],
        });
    }

    // Horizontal bars (revenue by plan, top areas)
    const bars = (id, labels, values, color, format) => {
        if (!el(id)) return;
        new Chart(el(id), {
            type: 'bar',
            data: { labels, datasets: [{ data: values, backgroundColor: color, borderRadius: 6, maxBarThickness: 20 }] },
            options: {
                ...base,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, callback: (v) => compact.format(v) }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { grid: { display: false } },
                },
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => ' ' + format(c.parsed.x) } } },
            },
        });
    };

    bars('chart-plans', d.plans.labels, d.plans.values, COLORS.cyan, (v) => `${full.format(v)} ETB`);
    bars('chart-areas', d.areas.labels, d.areas.values, COLORS.indigo, (v) => `${full.format(v)} live`);
}

const dataEl = document.getElementById('dashboard-data');
if (dataEl) {
    try {
        init(JSON.parse(dataEl.textContent));
    } catch (error) {
        console.warn('Dashboard charts could not be drawn.', error);
    }
}