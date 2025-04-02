document.addEventListener("DOMContentLoaded", function () {

    let revenueData = JSON.parse(document.getElementById("revenueData").textContent);
    let ordersData = JSON.parse(document.getElementById("ordersData").textContent);
    let subscriptionData = JSON.parse(document.getElementById("subscriptionData").textContent);
    let topProductData = JSON.parse(document.getElementById("categoryData").textContent);

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

    // Top 5 Product Chart
    if (Array.isArray(topProductData)) {
        let validProducts = topProductData.filter(p => p.y > 0);

        if (validProducts.length >= 5) {
            let productChart = new CanvasJS.Chart("categoryChart", {
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
                    showInLegend: validProducts.length > 0,
                    legendText: "{label}",
                    dataPoints: validProducts
                }]
            });
            productChart.render();
        } else {
            let chartDiv = document.getElementById("categoryChart");

            //Hide the chart
            if (chartDiv) {
                chartDiv.style.display = "";

                //Message display annd styling
                let message = document.createElement("div");
                message.innerHTML = `<p>*No top 5 products list available*</p>`;
                message.style.color = "red";
                message.style.fontSize = "13px";
                message.style.fontWeight = "bold";
                message.style.textAlign = "center";
                message.style.display = "flex";
                message.style.alignItems = "center";
                message.style.justifyContent = "center";
                message.style.height = "100%";

                chartDiv.appendChild(message);
            }
        }
    }


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
            yValueFormatString: "#,##0 orders",
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
            yValueFormatString: "RM#,##0.00",
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
