// --- 11. REPORTS MODULE ---
bindReportsEvents() {
    document.getElementById('refresh-report-btn').addEventListener('click', () => this.loadReportsData());
    document.getElementById('print-report-btn').addEventListener('click', () => window.print());
    document.getElementById('line-chart-interval').addEventListener('change', () => this.loadReportsData());
},
async loadReportsData() {
    const interval = document.getElementById('line-chart-interval').value;
    const result = await this.api('reports', 'GET', { interval });
    if (result.success) {
        this.state.charts.reportsData = result.data;
        this.render.reports();
    }
},