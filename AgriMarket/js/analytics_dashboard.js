document.addEventListener("DOMContentLoaded", function () {
    let revenueData = JSON.parse(document.getElementById("revenueData").textContent);
    let ordersData = JSON.parse(document.getElementById("ordersData").textContent);
    let subscriptionData = JSON.parse(document.getElementById("subscriptionData").textContent);
    let categoryData = JSON.parse(document.getElementById("categoryData").textContent);

    // Subscription Chart
    let subscriptionChart = new CanvasJS.Chart("subscriptionChart", {
        animationEnabled: true,
        theme: "light2",
        data: [{
            type: "pie",
            indexLabel: "{y}%",
            yValueFormatString: "#,##0.00\"\"",
            indexLabelPlacement: "inside",
            indexLabelFontColor: "#36454F",
            indexLabelFontSize: 18,
            indexLabelFontWeight: "bolder",
            showInLegend: true,
            legendText: "{label}",
            dataPoints: subscriptionData
        }]
    });
    subscriptionChart.render();

    // Top 5 Product Categories Chart
    let categoryChart = new CanvasJS.Chart("categoryChart", {
        animationEnabled: true,
        theme: "light2",
        data: [{
            type: "pie",
            indexLabel: "{y}%",
            yValueFormatString: "#,##0.00\"\"",
            indexLabelPlacement: "inside",
            indexLabelFontColor: "#36454F",
            indexLabelFontSize: 18,
            indexLabelFontWeight: "bolder",
            showInLegend: true,
            legendText: "{label}",
            dataPoints: categoryData
        }]
    });
    categoryChart.render();

    // Orders Chart
    let ordersChart = new CanvasJS.Chart("ordersChart", {
        animationEnabled: true,
        theme: "light2",
        axisY: {
            title: "Total Orders"
        },
        axisX: {
            title: "Month"
        },
        data: [{
            type: "column",
            yValueFormatString: "#,##0 Orders",
            dataPoints: ordersData.map(d => ({ label: d.month, y: d.total_orders }))
        }]
    });
    ordersChart.render();

    // Revenue Chart
    let revenueChart = new CanvasJS.Chart("revenueChart", {
        animationEnabled: true,
        theme: "light2",
        axisY: {
            title: "Total Revenue (in RM)"
        },
        axisX: {
            title: "Month"
        },
        data: [{
            type: "spline",
            yValueFormatString: "RM:#,##0.00",
            dataPoints: revenueData.map(d => ({ label: d.month, y: d.revenue }))
        }]
    });
    revenueChart.render();

    // Get Month from Date 
    function getMonthName(monthNumber) {
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        return months[monthNumber - 1]; 
    }
});
