// Admin Dashboard Charts

document.addEventListener('DOMContentLoaded', function () {
    fetch('../api/get_analytics.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                initCharts(data);
            }
        })
        .catch(error => console.error('Error loading analytics:', error));
});

function initCharts(data) {
    // 1. User Growth Line Chart
    const ctxGrowth = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: data.growth.labels,
            datasets: [{
                label: 'New Users',
                data: data.growth.data,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'User Growth (Last 6 Months)' }
            }
        }
    });

    // 2. Popular Brands Doughnut Chart
    const ctxBrands = document.getElementById('brandChart').getContext('2d');
    new Chart(ctxBrands, {
        type: 'doughnut',
        data: {
            labels: data.brands.labels,
            datasets: [{
                data: data.brands.data,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' },
                title: { display: true, text: 'Top 5 Car Brands' }
            }
        }
    });

    // 3. Car Status Bar Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'bar',
        data: {
            labels: data.sales.labels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                label: 'Cars',
                data: data.sales.data,
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Listing Status Distribution' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
