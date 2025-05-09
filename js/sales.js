// Function to generate the sales chart using Chart.js
function generateSalesChart(labels, data, chartType) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    // If a chart exists, destroy it before creating a new one
    if (window.salesChartInstance) {
        window.salesChartInstance.destroy();
    }

    // Create a new chart
    const salesChart = new Chart(ctx, {
        type: 'line', // Line chart for sales
        data: {
            labels: labels, // X-axis labels (time periods)
            datasets: [{
                label: chartType + ' Sales',
                data: data, // Y-axis: sales data
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: chartType + ' Period'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (₱)'
                    }
                }
            }
        }
    });

    // Store the chart instance in a global variable for future reference
    window.salesChartInstance = salesChart;
}

// Function to initialize all the sales data on page load
function initializeSalesData() {
    // Sample data (to be dynamically generated from the backend)
    const monthlyLabels = ['2022-01', '2022-02', '2022-03'];
    const monthlyData = [10000, 15000, 20000];

    const dailyLabels = ['2022-01-01', '2022-01-02', '2022-01-03'];
    const dailyData = [500, 700, 800];

    const weeklyLabels = ['2022-W01', '2022-W02', '2022-W03'];
    const weeklyData = [2000, 2500, 3000];

    const yearlyLabels = ['2022', '2023', '2024'];
    const yearlyData = [100000, 120000, 130000];

    // Initially show the monthly chart
    generateSalesChart(monthlyLabels, monthlyData, 'Monthly');

    // Handle the dropdown change event
    document.getElementById('chartType').addEventListener('change', function() {
        const selectedChartType = this.value;

        // Switch the data based on selected chart type
        if (selectedChartType === 'daily') {
            generateSalesChart(dailyLabels, dailyData, 'Daily');
        } else if (selectedChartType === 'weekly') {
            generateSalesChart(weeklyLabels, weeklyData, 'Weekly');
        } else if (selectedChartType === 'monthly') {
            generateSalesChart(monthlyLabels, monthlyData, 'Monthly');
        } else if (selectedChartType === 'yearly') {
            generateSalesChart(yearlyLabels, yearlyData, 'Yearly');
        }
    });
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', function () {
    initializeSalesData();
});

