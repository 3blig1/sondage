import './bootstrap';
import Chart from 'chart.js/auto';

const createDateRow = (value = '') => {
	const row = document.createElement('div');
	row.className = 'date-slot-card';
	row.setAttribute('data-date-row', '');
	row.innerHTML = `
		<div class="date-slot-head">
			<div>
				<div class="date-slot-title">
					<span class="date-slot-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
							<rect x="3" y="4" width="18" height="18" rx="3" ry="3"></rect>
							<line x1="16" y1="2.5" x2="16" y2="6"></line>
							<line x1="8" y1="2.5" x2="8" y2="6"></line>
							<line x1="3" y1="10" x2="21" y2="10"></line>
						</svg>
					</span>
					<span class="date-slot-index">Date</span>
				</div>
				<p class="date-slot-help">Choisis un créneau à proposer à tes participants.</p>
			</div>
			<button type="button" class="btn-secondary sm:min-w-32" data-remove-date>Retirer</button>
		</div>
		<input type="date" name="dates[]" class="field-input" value="${value}" min="${new Date().toISOString().split('T')[0]}">
	`;

	return row;
};

const syncRemoveButtons = (container) => {
	const rows = [...container.querySelectorAll('[data-date-row]')];

	rows.forEach((row, index) => {
		const button = row.querySelector('[data-remove-date]');
		const label = row.querySelector('.date-slot-index');

		if (!button) {
			return;
		}

		if (label) {
			label.textContent = `Date ${index + 1}`;
		}

		button.classList.toggle('hidden', rows.length <= 2 && index < 2);
		button.disabled = rows.length <= 2;
	});
};

const initDashboardCharts = () => {
	const performanceChart = document.querySelector('[data-performance-chart]');
	const choiceChart = document.querySelector('[data-choice-chart]');
	const trendChart = document.querySelector('[data-trend-chart]');
	const datePopularityChart = document.querySelector('[data-date-popularity-chart]');

	if (performanceChart instanceof HTMLCanvasElement) {
		const labels = JSON.parse(performanceChart.dataset.labels ?? '[]');
		const values = JSON.parse(performanceChart.dataset.values ?? '[]');

		if (labels.length > 0) {
			new Chart(performanceChart, {
				type: 'bar',
				data: {
					labels,
					datasets: [{
						label: 'Réponses',
						data: values,
						borderRadius: 10,
						backgroundColor: ['#22d3ee', '#38bdf8', '#6366f1', '#8b5cf6', '#14b8a6', '#0ea5e9'],
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: {
							backgroundColor: '#0f172a',
							titleColor: '#f8fafc',
							bodyColor: '#cbd5e1',
							borderColor: 'rgba(148, 163, 184, 0.2)',
							borderWidth: 1,
						},
					},
					scales: {
						x: {
							ticks: { color: '#94a3b8' },
							grid: { display: false },
						},
						y: {
							beginAtZero: true,
							ticks: {
								color: '#94a3b8',
								precision: 0,
							},
							grid: { color: 'rgba(148, 163, 184, 0.12)' },
						},
					},
				},
			});
		}
	}

	if (choiceChart instanceof HTMLCanvasElement) {
		const labels = JSON.parse(choiceChart.dataset.labels ?? '[]');
		const values = JSON.parse(choiceChart.dataset.values ?? '[]');

		if (labels.length > 0) {
			new Chart(choiceChart, {
				type: 'doughnut',
				data: {
					labels,
					datasets: [{
						data: values,
						backgroundColor: ['#38bdf8', '#8b5cf6'],
						borderColor: 'rgba(15, 23, 42, 0.85)',
						borderWidth: 4,
						hoverOffset: 8,
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					cutout: '68%',
					plugins: {
						legend: {
							position: 'bottom',
							labels: {
								color: '#cbd5e1',
								usePointStyle: true,
								padding: 18,
							},
						},
						tooltip: {
							backgroundColor: '#0f172a',
							titleColor: '#f8fafc',
							bodyColor: '#cbd5e1',
							borderColor: 'rgba(148, 163, 184, 0.2)',
							borderWidth: 1,
						},
					},
				},
			});
		}
	}

	if (trendChart instanceof HTMLCanvasElement) {
		const labels = JSON.parse(trendChart.dataset.labels ?? '[]');
		const values = JSON.parse(trendChart.dataset.values ?? '[]');

		if (labels.length > 0) {
			new Chart(trendChart, {
				type: 'line',
				data: {
					labels,
					datasets: [{
						label: 'Réponses',
						data: values,
						borderColor: '#22d3ee',
						backgroundColor: 'rgba(34, 211, 238, 0.18)',
						fill: true,
						tension: 0.35,
						pointRadius: 4,
						pointHoverRadius: 6,
						pointBackgroundColor: '#38bdf8',
					}],
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: {
							backgroundColor: '#0f172a',
							titleColor: '#f8fafc',
							bodyColor: '#cbd5e1',
							borderColor: 'rgba(148, 163, 184, 0.2)',
							borderWidth: 1,
						},
					},
					scales: {
						x: {
							ticks: { color: '#94a3b8' },
							grid: { color: 'rgba(148, 163, 184, 0.08)' },
						},
						y: {
							beginAtZero: true,
							ticks: {
								color: '#94a3b8',
								precision: 0,
							},
							grid: { color: 'rgba(148, 163, 184, 0.12)' },
						},
					},
				},
			});
		}
	}

	if (datePopularityChart instanceof HTMLCanvasElement) {
		const labels = JSON.parse(datePopularityChart.dataset.labels ?? '[]');
		const values = JSON.parse(datePopularityChart.dataset.values ?? '[]');

		if (labels.length > 0) {
			new Chart(datePopularityChart, {
				type: 'bar',
				data: {
					labels,
					datasets: [{
						label: 'Choix',
						data: values,
						borderRadius: 10,
						backgroundColor: ['#f59e0b', '#f97316', '#fb7185', '#38bdf8', '#22c55e', '#8b5cf6', '#14b8a6', '#eab308'],
					}],
				},
				options: {
					indexAxis: 'y',
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: { display: false },
						tooltip: {
							backgroundColor: '#0f172a',
							titleColor: '#f8fafc',
							bodyColor: '#cbd5e1',
							borderColor: 'rgba(148, 163, 184, 0.2)',
							borderWidth: 1,
						},
					},
					scales: {
						x: {
							beginAtZero: true,
							ticks: {
								color: '#94a3b8',
								precision: 0,
							},
							grid: { color: 'rgba(148, 163, 184, 0.12)' },
						},
						y: {
							ticks: { color: '#94a3b8' },
							grid: { display: false },
						},
					},
				},
			});
		}
	}
};

const initModals = () => {
	const modalMap = new Map();

	document.querySelectorAll('[data-modal]').forEach((modal) => {
		if (!(modal instanceof HTMLElement) || !modal.id) {
			return;
		}

		modalMap.set(modal.id, modal);
	});

	if (modalMap.size === 0) {
		return;
	}

	const openModal = (modal) => {
		modal.classList.remove('hidden');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('modal-open');
	};

	const closeModal = (modal) => {
		modal.classList.add('hidden');
		modal.setAttribute('aria-hidden', 'true');

		const hasOpenModal = [...modalMap.values()].some((item) => !item.classList.contains('hidden'));

		if (!hasOpenModal) {
			document.body.classList.remove('modal-open');
		}
	};

	document.querySelectorAll('[data-open-modal-target]').forEach((trigger) => {
		trigger.addEventListener('click', (event) => {
			const modalId = trigger.getAttribute('data-open-modal-target');
			const modal = modalId ? modalMap.get(modalId) : null;

			if (!modal) {
				return;
			}

			event.preventDefault();
			openModal(modal);
		});
	});

	modalMap.forEach((modal) => {
		modal.querySelectorAll('[data-close-modal]').forEach((button) => {
			button.addEventListener('click', () => closeModal(modal));
		});
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') {
			return;
		}

		const activeModal = [...modalMap.values()].find((modal) => !modal.classList.contains('hidden'));

		if (activeModal) {
			closeModal(activeModal);
		}
	});

	const hashedModal = window.location.hash ? modalMap.get(window.location.hash.slice(1)) : null;

	if (hashedModal) {
		openModal(hashedModal);
	}

	modalMap.forEach((modal) => {
		if (modal.dataset.modalOpenDefault === 'true') {
			openModal(modal);
		}
	});
};

const initSelectableOptions = () => {
	document.querySelectorAll('[data-selectable-group]').forEach((group) => {
		const syncState = () => {
			group.querySelectorAll('[data-selectable-option]').forEach((option) => {
				const input = option.querySelector('input[type="radio"], input[type="checkbox"]');

				if (!(input instanceof HTMLInputElement)) {
					return;
				}

				option.classList.toggle('is-selected', input.checked);
			});
		};

		group.addEventListener('change', syncState);
		syncState();
	});
};

document.addEventListener('DOMContentLoaded', () => {
	const container = document.querySelector('[data-date-fields]');
	const addButton = document.querySelector('[data-add-date]');

	if (container && addButton) {
		syncRemoveButtons(container);

		addButton.addEventListener('click', () => {
			container.appendChild(createDateRow());
			syncRemoveButtons(container);
		});

		container.addEventListener('click', (event) => {
			const target = event.target;

			if (!(target instanceof HTMLElement) || !target.matches('[data-remove-date]')) {
				return;
			}

			target.closest('[data-date-row]')?.remove();
			syncRemoveButtons(container);
		});
	}

	document.querySelectorAll('[data-copy-button]').forEach((button) => {
		button.addEventListener('click', async () => {
			const selector = button.getAttribute('data-copy-button');
			const source = selector ? document.querySelector(selector) : null;

			if (!(source instanceof HTMLInputElement)) {
				return;
			}

			try {
				await navigator.clipboard.writeText(source.value);
				const originalText = button.textContent;

				button.textContent = 'Lien copié';

				window.setTimeout(() => {
					button.textContent = originalText;
				}, 1800);
			} catch {
				source.select();
			}
		});
	});

	initSelectableOptions();
	initModals();
	initDashboardCharts();
});
