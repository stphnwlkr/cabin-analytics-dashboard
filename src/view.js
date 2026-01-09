document.addEventListener('DOMContentLoaded', function() {
	const widgets = document.querySelectorAll('.cabin-analytics-widget');
	
	widgets.forEach(widget => {
		initializeWidget(widget);
	});
});

function initializeWidget(container) {
	const domain = container.dataset.domain;
	const dashboardUrl = container.dataset.dashboardUrl || `https://withcabin.com/dashboard/${domain}`;
	let chartType = container.dataset.chartType || 'bar';
	let dateRange = parseInt(container.dataset.dateRange || '7');
	const allowSwitching = container.dataset.allowSwitching !== 'false';

	if (!domain) {
		container.innerHTML = '<div class="cabin-analytics-error" role="alert">Domain not configured. Please set up your Cabin Analytics settings.</div>';
		return;
	}

	let chartData = null;
	let abortController = null;

	function formatDate(date) {
		return date.toLocaleDateString('en-CA');
	}

	function formatDateLabel(dateStr) {
		const date = new Date(dateStr + 'T00:00:00');
		return `${date.getMonth() + 1}/${date.getDate()}`;
	}

	function formatDateFull(dateStr) {
		const date = new Date(dateStr + 'T00:00:00');
		const options = { month: 'short', day: 'numeric', year: 'numeric' };
		return date.toLocaleDateString('en-US', options);
	}
	
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function render() {
		container.innerHTML = `
			<div class="cabin-analytics-header">
				<div class="cabin-analytics-title-wrapper">
					<h2 class="cabin-analytics-title">Cabin Analytics Dashboard</h2>
					<div class="cabin-analytics-domain" aria-label="Domain: ${escapeHtml(domain)}">${escapeHtml(domain)}</div>
				</div>
				${allowSwitching ? `
					<div class="cabin-analytics-controls">
						<div class="cabin-analytics-chart-toggle" role="group" aria-label="Chart type">
							<button class="cabin-analytics-chart-button ${chartType === 'bar' ? 'active' : ''}" data-type="bar" aria-label="Bar Chart" aria-pressed="${chartType === 'bar'}">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
									<rect x="4" y="12" width="4" height="8"/>
									<rect x="10" y="8" width="4" height="12"/>
									<rect x="16" y="4" width="4" height="16"/>
								</svg>
								<span class="screen-reader-text">Bar Chart</span>
							</button>
							<button class="cabin-analytics-chart-button ${chartType === 'line' ? 'active' : ''}" data-type="line" aria-label="Line Chart" aria-pressed="${chartType === 'line'}">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
									<polyline points="4 18 8 12 12 14 16 8 20 10"/>
								</svg>
								<span class="screen-reader-text">Line Chart</span>
							</button>
						</div>
						<div class="cabin-analytics-button-group" role="group" aria-label="Date range">
							<button class="cabin-analytics-button ${dateRange === 7 ? 'active' : ''}" data-range="7" aria-pressed="${dateRange === 7}">7d</button>
							<button class="cabin-analytics-button ${dateRange === 14 ? 'active' : ''}" data-range="14" aria-pressed="${dateRange === 14}">14d</button>
							<button class="cabin-analytics-button ${dateRange === 30 ? 'active' : ''}" data-range="30" aria-pressed="${dateRange === 30}">30d</button>
							<button class="cabin-analytics-button ${dateRange === 90 ? 'active' : ''}" data-range="90" aria-pressed="${dateRange === 90}">90d</button>
						</div>
					</div>
				` : ''}
			</div>
			<div class="cabin-analytics-loading" role="status" aria-live="polite">Loading analytics data...</div>
		`;

		if (allowSwitching) {
			const rangeButtons = container.querySelectorAll('[data-range]');
			rangeButtons.forEach(btn => {
				btn.addEventListener('click', () => {
					dateRange = parseInt(btn.dataset.range);
					fetchData();
				});
			});

			const chartButtons = container.querySelectorAll('[data-type]');
			chartButtons.forEach(btn => {
				btn.addEventListener('click', () => {
					chartType = btn.dataset.type;
					renderChart();
				});
			});
		}

		fetchData();
	}

	function fetchData() {
		if (abortController) {
			abortController.abort();
		}
		
		abortController = new AbortController();
		
		const endDate = new Date();
		const startDate = new Date();
		startDate.setDate(endDate.getDate() - (dateRange - 1));

		const startDateStr = formatDate(startDate);
		const endDateStr = formatDate(endDate);

		fetch(`/wp-json/cabin-analytics/v1/stats?domain=${encodeURIComponent(domain)}&start_date=${startDateStr}&end_date=${endDateStr}`, {
			signal: abortController.signal
		})
			.then(response => {
				if (!response.ok) {
					throw new Error('Failed to fetch analytics data');
				}
				return response.json();
			})
			.then(data => {
				chartData = processData(data, startDateStr, endDateStr);
				renderChart();
			})
			.catch(error => {
				if (error.name === 'AbortError') {
					return;
				}
				container.innerHTML = `
					<div class="cabin-analytics-header">
						<div class="cabin-analytics-title-wrapper">
							<h2 class="cabin-analytics-title">Cabin Analytics Dashboard</h2>
							<div class="cabin-analytics-domain">${escapeHtml(domain)}</div>
						</div>
					</div>
					<div class="cabin-analytics-error" role="alert">Error loading analytics: ${escapeHtml(error.message)}</div>
				`;
			});
	}

	function processData(data, startDate, endDate) {
		if (!data || !data.daily_data || !Array.isArray(data.daily_data)) {
			return { dates: [], pageviews: [], visitors: [], totalPageviews: 0, totalVisitors: 0, bounceRate: 0, startDate, endDate };
		}

		const dates = [];
		const pageviews = [];
		const visitors = [];

		data.daily_data.forEach(day => {
			if (day.timestamp && (day.page_views !== null || day.unique_visitors !== null)) {
				const date = new Date(day.timestamp);
				const year = date.getUTCFullYear();
				const month = String(date.getUTCMonth() + 1).padStart(2, '0');
				const dayNum = String(date.getUTCDate()).padStart(2, '0');
				const dateStr = `${year}-${month}-${dayNum}`;
				
				dates.push(dateStr);
				pageviews.push(parseInt(day.page_views) || 0);
				visitors.push(parseInt(day.unique_visitors) || 0);
			}
		});

		const totalPageviews = data.summary?.page_views || 0;
		const totalVisitors = data.summary?.unique_visitors || 0;
		const bounceRate = data.summary?.bounce_rate || 0;

		return { dates, pageviews, visitors, totalPageviews, totalVisitors, bounceRate, startDate, endDate };
	}

	function renderChart() {
		if (!chartData) return;

		const bounceRatePercent = ((1 - chartData.bounceRate) * 100).toFixed(1);

		const statsHTML = `
			<div class="cabin-analytics-stats">
				<div class="cabin-analytics-stat">
					<div class="cabin-analytics-stat-label" id="total-views-label">Total Views</div>
					<div class="cabin-analytics-stat-value" aria-labelledby="total-views-label">${chartData.totalPageviews.toLocaleString()}</div>
				</div>
				<div class="cabin-analytics-stat">
					<div class="cabin-analytics-stat-label" id="unique-visitors-label">Unique Visitors</div>
					<div class="cabin-analytics-stat-value" aria-labelledby="unique-visitors-label">${chartData.totalVisitors.toLocaleString()}</div>
				</div>
				<div class="cabin-analytics-stat">
					<div class="cabin-analytics-stat-label" id="bounce-rate-label">Bounce Rate</div>
					<div class="cabin-analytics-stat-value" aria-labelledby="bounce-rate-label">${bounceRatePercent}%</div>
				</div>
			</div>
		`;

		const header = container.querySelector('.cabin-analytics-header').outerHTML;
		
		const footerHTML = `
			<div class="cabin-analytics-footer">
				<div class="cabin-analytics-date-range" role="status">
					${formatDateFull(chartData.startDate)} - ${formatDateFull(chartData.endDate)}
				</div>
				${dashboardUrl ? `<a href="${escapeHtml(dashboardUrl)}" target="_blank" rel="noopener noreferrer" class="cabin-analytics-dashboard-link">Go to Dashboard →</a>` : ''}
			</div>
		`;
		
		container.innerHTML = header + statsHTML + '<div class="cabin-analytics-chart-container"><svg class="cabin-analytics-chart" role="img" aria-label="Analytics chart showing page views and unique visitors over time"></svg></div>' + footerHTML;

		const svg = container.querySelector('.cabin-analytics-chart');
		
		if (chartType === 'bar') {
			renderBarChart(svg, chartData);
		} else {
			renderLineChart(svg, chartData);
		}

		if (allowSwitching) {
			const rangeButtons = container.querySelectorAll('[data-range]');
			rangeButtons.forEach(btn => {
				btn.addEventListener('click', () => {
					dateRange = parseInt(btn.dataset.range);
					
					// Update active class and aria-pressed for all range buttons
					rangeButtons.forEach(b => {
						b.classList.remove('active');
						b.setAttribute('aria-pressed', 'false');
					});
					btn.classList.add('active');
					btn.setAttribute('aria-pressed', 'true');
					
					fetchData();
				});
			});

			const chartButtons = container.querySelectorAll('[data-type]');
			chartButtons.forEach(btn => {
				btn.addEventListener('click', () => {
					chartType = btn.dataset.type;
					
					// Update active class and aria-pressed for all chart type buttons
					chartButtons.forEach(b => {
						b.classList.remove('active');
						b.setAttribute('aria-pressed', 'false');
					});
					btn.classList.add('active');
					btn.setAttribute('aria-pressed', 'true');
					
					renderChart();
				});
			});
		}
	}

	function renderBarChart(svg, data) {
		const width = svg.clientWidth;
		const height = svg.clientHeight;
		const padding = { top: 20, right: 20, bottom: 40, left: 50 };
		const chartWidth = width - padding.left - padding.right;
		const chartHeight = height - padding.top - padding.bottom;

		const maxValue = Math.max(...data.pageviews);
		const barWidth = chartWidth / data.dates.length;

		svg.innerHTML = '';
		svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

		for (let i = 0; i <= 4; i++) {
			const y = padding.top + (chartHeight / 4) * i;
			const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line.setAttribute('x1', padding.left);
			line.setAttribute('y1', y);
			line.setAttribute('x2', width - padding.right);
			line.setAttribute('y2', y);
			line.setAttribute('stroke', '#e0e0e0');
			line.setAttribute('stroke-width', '1');
			svg.appendChild(line);

			const value = Math.round(maxValue * (1 - i / 4));
			const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
			text.setAttribute('x', padding.left - 10);
			text.setAttribute('y', y + 4);
			text.setAttribute('text-anchor', 'end');
			text.setAttribute('font-size', '12');
			text.setAttribute('fill', '#666');
			text.textContent = value;
			svg.appendChild(text);
		}

		data.dates.forEach((date, i) => {
			const x = padding.left + i * barWidth;
			const visitorsHeight = (data.visitors[i] / maxValue) * chartHeight;
			const pageviewsHeight = (data.pageviews[i] / maxValue) * chartHeight;

			const visitorsBar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
			visitorsBar.setAttribute('x', x + barWidth * 0.1);
			visitorsBar.setAttribute('y', padding.top + chartHeight - visitorsHeight);
			visitorsBar.setAttribute('width', barWidth * 0.8);
			visitorsBar.setAttribute('height', visitorsHeight);
			visitorsBar.setAttribute('fill', '#2271b1');
			visitorsBar.setAttribute('rx', '2');
			visitorsBar.style.cursor = 'pointer';
			visitorsBar.setAttribute('role', 'button');
			visitorsBar.setAttribute('tabindex', '0');
			visitorsBar.setAttribute('aria-label', `${formatDateFull(date)}: ${data.visitors[i]} unique visitors, ${data.pageviews[i]} total views`);
			svg.appendChild(visitorsBar);

			const pageviewsBar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
			pageviewsBar.setAttribute('x', x + barWidth * 0.1);
			pageviewsBar.setAttribute('y', padding.top + chartHeight - pageviewsHeight);
			pageviewsBar.setAttribute('width', barWidth * 0.8);
			pageviewsBar.setAttribute('height', pageviewsHeight - visitorsHeight);
			pageviewsBar.setAttribute('fill', '#72aee6');
			pageviewsBar.setAttribute('rx', '2');
			pageviewsBar.style.cursor = 'pointer';
			pageviewsBar.setAttribute('role', 'button');
			pageviewsBar.setAttribute('tabindex', '0');
			pageviewsBar.setAttribute('aria-label', `${formatDateFull(date)}: ${data.visitors[i]} unique visitors, ${data.pageviews[i]} total views`);
			svg.appendChild(pageviewsBar);

			if (i % Math.ceil(data.dates.length / 10) === 0) {
				const dateLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
				dateLabel.setAttribute('x', x + barWidth / 2);
				dateLabel.setAttribute('y', height - padding.bottom + 20);
				dateLabel.setAttribute('text-anchor', 'middle');
				dateLabel.setAttribute('font-size', '11');
				dateLabel.setAttribute('fill', '#666');
				dateLabel.textContent = formatDateLabel(date);
				svg.appendChild(dateLabel);
			}

			[visitorsBar, pageviewsBar].forEach(bar => {
				bar.addEventListener('mouseenter', (e) => {
					showTooltip(e, date, data.pageviews[i], data.visitors[i]);
				});
				bar.addEventListener('mouseleave', hideTooltip);
				bar.addEventListener('focus', (e) => {
					showTooltip(e, date, data.pageviews[i], data.visitors[i]);
				});
				bar.addEventListener('blur', hideTooltip);
				bar.addEventListener('keydown', (e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						showTooltip(e, date, data.pageviews[i], data.visitors[i]);
					}
				});
			});
		});
	}

	function renderLineChart(svg, data) {
		const width = svg.clientWidth;
		const height = svg.clientHeight;
		const padding = { top: 20, right: 20, bottom: 40, left: 50 };
		const chartWidth = width - padding.left - padding.right;
		const chartHeight = height - padding.top - padding.bottom;

		const maxValue = Math.max(...data.pageviews);

		svg.innerHTML = '';
		svg.setAttribute('viewBox', `0 0 ${width} ${height}`);

		for (let i = 0; i <= 4; i++) {
			const y = padding.top + (chartHeight / 4) * i;
			const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
			line.setAttribute('x1', padding.left);
			line.setAttribute('y1', y);
			line.setAttribute('x2', width - padding.right);
			line.setAttribute('y2', y);
			line.setAttribute('stroke', '#e0e0e0');
			line.setAttribute('stroke-width', '1');
			svg.appendChild(line);

			const value = Math.round(maxValue * (1 - i / 4));
			const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
			text.setAttribute('x', padding.left - 10);
			text.setAttribute('y', y + 4);
			text.setAttribute('text-anchor', 'end');
			text.setAttribute('font-size', '12');
			text.setAttribute('fill', '#666');
			text.textContent = value;
			svg.appendChild(text);
		}

		const pointSpacing = chartWidth / (data.dates.length - 1);
		const pageviewsPoints = data.pageviews.map((value, i) => {
			const x = padding.left + i * pointSpacing;
			const y = padding.top + chartHeight - (value / maxValue) * chartHeight;
			return `${x},${y}`;
		});

		const visitorsPoints = data.visitors.map((value, i) => {
			const x = padding.left + i * pointSpacing;
			const y = padding.top + chartHeight - (value / maxValue) * chartHeight;
			return `${x},${y}`;
		});

		const pageviewsLine = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
		pageviewsLine.setAttribute('points', pageviewsPoints.join(' '));
		pageviewsLine.setAttribute('fill', 'none');
		pageviewsLine.setAttribute('stroke', '#72aee6');
		pageviewsLine.setAttribute('stroke-width', '3');
		pageviewsLine.setAttribute('stroke-linecap', 'round');
		pageviewsLine.setAttribute('stroke-linejoin', 'round');
		svg.appendChild(pageviewsLine);

		const visitorsLine = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
		visitorsLine.setAttribute('points', visitorsPoints.join(' '));
		visitorsLine.setAttribute('fill', 'none');
		visitorsLine.setAttribute('stroke', '#2271b1');
		visitorsLine.setAttribute('stroke-width', '3');
		visitorsLine.setAttribute('stroke-linecap', 'round');
		visitorsLine.setAttribute('stroke-linejoin', 'round');
		svg.appendChild(visitorsLine);

		data.dates.forEach((date, i) => {
			const x = padding.left + i * pointSpacing;
			
			const pageviewsY = padding.top + chartHeight - (data.pageviews[i] / maxValue) * chartHeight;
			const pageviewsCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			pageviewsCircle.setAttribute('cx', x);
			pageviewsCircle.setAttribute('cy', pageviewsY);
			pageviewsCircle.setAttribute('r', '4');
			pageviewsCircle.setAttribute('fill', '#72aee6');
			pageviewsCircle.setAttribute('stroke', '#fff');
			pageviewsCircle.setAttribute('stroke-width', '2');
			pageviewsCircle.style.cursor = 'pointer';
			pageviewsCircle.setAttribute('role', 'button');
			pageviewsCircle.setAttribute('tabindex', '0');
			pageviewsCircle.setAttribute('aria-label', `${formatDateFull(date)}: ${data.visitors[i]} unique visitors, ${data.pageviews[i]} total views`);
			svg.appendChild(pageviewsCircle);

			const visitorsY = padding.top + chartHeight - (data.visitors[i] / maxValue) * chartHeight;
			const visitorsCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
			visitorsCircle.setAttribute('cx', x);
			visitorsCircle.setAttribute('cy', visitorsY);
			visitorsCircle.setAttribute('r', '4');
			visitorsCircle.setAttribute('fill', '#2271b1');
			visitorsCircle.setAttribute('stroke', '#fff');
			visitorsCircle.setAttribute('stroke-width', '2');
			visitorsCircle.style.cursor = 'pointer';
			visitorsCircle.setAttribute('role', 'button');
			visitorsCircle.setAttribute('tabindex', '0');
			visitorsCircle.setAttribute('aria-label', `${formatDateFull(date)}: ${data.visitors[i]} unique visitors, ${data.pageviews[i]} total views`);
			svg.appendChild(visitorsCircle);

			if (i % Math.ceil(data.dates.length / 10) === 0) {
				const dateLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
				dateLabel.setAttribute('x', x);
				dateLabel.setAttribute('y', height - padding.bottom + 20);
				dateLabel.setAttribute('text-anchor', 'middle');
				dateLabel.setAttribute('font-size', '11');
				dateLabel.setAttribute('fill', '#666');
				dateLabel.textContent = formatDateLabel(date);
				svg.appendChild(dateLabel);
			}

			[pageviewsCircle, visitorsCircle].forEach(circle => {
				circle.addEventListener('mouseenter', (e) => {
					showTooltip(e, date, data.pageviews[i], data.visitors[i]);
					circle.setAttribute('r', '6');
				});
				circle.addEventListener('mouseleave', (e) => {
					hideTooltip();
					circle.setAttribute('r', '4');
				});
				circle.addEventListener('focus', (e) => {
					showTooltip(e, date, data.pageviews[i], data.visitors[i]);
					circle.setAttribute('r', '6');
				});
				circle.addEventListener('blur', (e) => {
					hideTooltip();
					circle.setAttribute('r', '4');
				});
				circle.addEventListener('keydown', (e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						showTooltip(e, date, data.pageviews[i], data.visitors[i]);
					}
				});
			});
		});
	}

	function showTooltip(event, date, pageviews, visitors) {
		hideTooltip();
		
		const tooltip = document.createElement('div');
		tooltip.className = 'cabin-analytics-tooltip';
		tooltip.setAttribute('role', 'tooltip');
		tooltip.innerHTML = `
			<strong>${escapeHtml(formatDateFull(date))}</strong>
			<div><span class="tooltip-color" style="background: #72aee6;" aria-hidden="true"></span> Total Views: ${pageviews.toLocaleString()}</div>
			<div><span class="tooltip-color" style="background: #2271b1;" aria-hidden="true"></span> Unique Visitors: ${visitors.toLocaleString()}</div>
		`;
		
		document.body.appendChild(tooltip);
		
		const rect = event.target.getBoundingClientRect();
		tooltip.style.left = rect.left + window.scrollX + 'px';
		tooltip.style.top = rect.top + window.scrollY - tooltip.offsetHeight - 10 + 'px';
	}

	function hideTooltip() {
		const tooltip = document.querySelector('.cabin-analytics-tooltip');
		if (tooltip) {
			tooltip.remove();
		}
	}

	render();
}