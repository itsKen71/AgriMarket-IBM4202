document.addEventListener("DOMContentLoaded", function () {

    let revenueData = JSON.parse(document.getElementById("revenueData").textContent);
    let ordersData = JSON.parse(document.getElementById("ordersData").textContent);
    let subscriptionData = JSON.parse(document.getElementById("subscriptionData").textContent);
    let topPaymentMethodData = JSON.parse(document.getElementById("paymentData").textContent);
    let topVendorsData = JSON.parse(document.getElementById("vendorData").textContent);
    let shipmentData = JSON.parse(document.getElementById("shipmentData").textContent);

    // Subscription Chart 
    let subscriptionChartContainer = document.getElementById("subscriptionChart");
    if (subscriptionChartContainer) {
        let formatSubscription = subscriptionData.length == 1 ? "#,##0\"%\"" : "#,##0.00\"%\"";

        if (subscriptionData.length == 0) {
            subscriptionChartContainer.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                    <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                        No Subscription Plans Found
                    </div>
                </div>
            `;
        } else {
            let subscriptionChart = new CanvasJS.Chart("subscriptionChart", {
                animationEnabled: true,
                theme: "light2",
                data: [{
                    type: "pie",
                    indexLabel: "{y}",
                    yValueFormatString: formatSubscription,
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
        }
    }

    // Shipment Status Chart 
    let shipmentChartContainer = document.getElementById("shipmentChart");
    if (shipmentChartContainer) {
        let formatShipment = shipmentData.length == 1 ? "#,##0\"%\"" : "#,##0.00\"%\"";

        if (shipmentData.length == 0) {
            shipmentChartContainer.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                    <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                        No Shipment Status Found
                    </div>
                </div>
            `;
        } else {
            let shipmentChart = new CanvasJS.Chart("shipmentChart", {
                animationEnabled: true,
                theme: "light2",
                data: [{
                    type: "pie",
                    indexLabel: "{y}",
                    yValueFormatString: formatShipment,
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "#36454F",
                    indexLabelFontSize: 18,
                    indexLabelFontWeight: "bolder",
                    showInLegend: true,
                    legendText: "{label}",
                    dataPoints: shipmentData
                }]
            });
            shipmentChart.render();
        }
    }

    // Payment Method Chart
    let paymentChartContainer = document.getElementById("paymentChart");
    if (paymentChartContainer) {
        let formatPayment = topPaymentMethodData.length == 1 ? "#,##0\"%\"" : "#,##0.00\"%\"";

        if (topPaymentMethodData.length === 0) {
            paymentChartContainer.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                    <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                        No Payment Methods Found
                    </div>
                </div>
            `;
        } else {
            let paymentChart = new CanvasJS.Chart("paymentChart", {
                animationEnabled: true,
                theme: "light2",
                data: [{
                    type: "pie",
                    indexLabel: "{y}",
                    yValueFormatString: formatPayment,
                    indexLabelPlacement: "inside",
                    indexLabelFontColor: "#36454F",
                    indexLabelFontSize: 18,
                    indexLabelFontWeight: "bolder",
                    showInLegend: true,
                    legendText: "{label}",
                    dataPoints: topPaymentMethodData
                }]
            });
            paymentChart.render();
        }
    }

    // Top Vendor Chart 
    let topVendorChartContainer = document.getElementById("vendorChart");
    if (topVendorChartContainer) {
        if (topVendorsData.length <5) {
            topVendorChartContainer.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                    <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                        No vendors found
                    </div>
                </div>
            `;
        } else {
            let topVendorChart = new CanvasJS.Chart("vendorChart", {
                animationEnabled: true,
                theme: "light2",
                axisY: { title: "Quantity Sold" },
                axisX: { title: "Vendor" },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0 items",
                    dataPoints: topVendorsData
                }]
            });
            topVendorChart.render();
        }
    }

    // Orders Chart 
    let ordersChartContainer = document.getElementById("ordersChart");
    if (ordersChartContainer) {
        if (ordersData.length == 0 || ordersData.every(d => d.total_orders === 0)) {
            ordersChartContainer.innerHTML = `
            <div style="display: flex; justify-content: center; align-items: center; height: 100%; min-height: 300px;">
                <div style="text-align: center; font-size: 20px; font-weight: bold; color: #555;">
                    No orders found
                </div>
            </div>
        `;
        } else {
            let ordersChart = new CanvasJS.Chart("ordersChart", {
                animationEnabled: true,
                theme: "light2",
                axisY: { title: "Total Orders" },
                axisX: { title: "Month" },
                data: [{
                    type: "column",
                    yValueFormatString: "#,##0 orders",
                    dataPoints: ordersData.map(d => ({ label: d.month, y: d.total_orders }))
                }]
            });
            ordersChart.render();
        }
    }

    // Revenue Chart 
    let revenueChartContainer = document.getElementById("revenueChart");
    if (revenueChartContainer) {
        let revenueChart = new CanvasJS.Chart("revenueChart", {
            animationEnabled: true,
            theme: "light2",
            axisY: { title: "Total Revenue (in RM)" },
            axisX: { title: "Month" },
            data: [{
                type: "spline",
                yValueFormatString: "RM#,##0.00",
                dataPoints: revenueData.map(d => ({ label: d.month, y: d.revenue }))
            }]
        });
        revenueChart.render();
    }

});
