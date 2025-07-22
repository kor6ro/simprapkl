// -- chart line
const lineOptions = {
    chart: { height: 280, type: "area", toolbar: { show: false } },
    dataLabels: { enabled: false },
    stroke: { curve: "smooth", width: 3 },
    series: [
        { name: "Online", data: [34, 40, 28, 52, 42, 109, 100] },
        { name: "Offline", data: [32, 60, 34, 46, 34, 52, 41] },
    ],
    xaxis: {
        type: "datetime",
        categories: [
            "2018-09-19T00:00:00",
            "2018-09-19T01:30:00",
            "2018-09-19T02:30:00",
            "2018-09-19T03:30:00",
            "2018-09-19T04:30:00",
            "2018-09-19T05:30:00",
            "2018-09-19T06:30:00",
        ],
    },
    grid: { borderColor: "#f1f1f1" },
    tooltip: { x: { format: "dd/MM/yy HH:mm" } },
}

new ApexCharts(
    document.querySelector("#line_chart"),
    lineOptions
).render();

// -- radial chart
const radialOptions = {
    chart: { height: 300, type: "radialBar" },
    series: [44, 55, 67, 83],
    labels: ["Computer", "Tablet", "Laptop", "Mobile"],
}

new ApexCharts(
    document.querySelector("#radial_chart"),
    radialOptions
).render();
