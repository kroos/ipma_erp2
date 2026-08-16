const { route } = window.data;

$(document).ready(function () {

	$.ajax({
		url: route.staffdaily,
		type: "POST",
		dataType: "json"
	})
	.done(function (data) {
			let summaryTable = $("#summary");

			$.each(data, function (index, value) {
					let row1 = `
							<tr>
									<td class="text-center">${value.date}</td>
									<td class="text-center"><span class="badge text-bg-secondary">${value.working}</span></td>
									<td class="text-center">${value.overallpercentage}%</td>
									<td class="text-center">${value.workingpeople}</td>
									<td class="text-center" colspan="2">${value.outstation}</td>
									<td class="text-center" colspan="2">${value.leave}</td>
									<td class="text-center" colspan="2">${value.absent}</td>
									<td class="text-center" colspan="2">${value.halfabsent}</td>
									<td class="text-center">${value.workday}</td>
							</tr>`;

					let row2 = `
							<tr class="table-light">
									<td class="text-center" colspan="4"></td>
									<td class="text-center" colspan="2">${formatLocations(value.locoutstation)}</td>
									<td class="text-center" colspan="2">${formatLocations(value.locationleave)}</td>
									<td class="text-center" colspan="2">${formatLocations(value.locationabsent)}</td>
									<td class="text-center" colspan="2">${formatLocations(value.locationhalfabsent)}</td>
									<td class="text-center"></td>
							</tr>`;

					summaryTable.append(row1 + row2);
			});

			renderStatCards(data);
			renderChart(data);

			if (data.length > 0) {
				$("#summary-period").text(data[0].date + " — " + data[data.length - 1].date);
			}
	})
	.fail(function (jqXHR, textStatus, errorThrown) {
			console.error("AJAX Error:", textStatus, errorThrown);
	});

	function formatLocations(locations) {
			return $.isEmptyObject(locations) ? "—" : Object.entries(locations).map(([k, v]) => `${k}: ${v}`).join("<br/>");
	}

	function renderStatCards(data) {
			const latest = data[data.length - 1];
			if (!latest) return;

			const cards = [
					{
							icon: "fa-solid fa-chart-simple",
							label: "Attendance",
							value: latest.overallpercentage + "%",
							accent: "accent-green"
					},
					{
							icon: "fa-solid fa-user-check",
							label: "Available Staff",
							value: latest.workingpeople,
							accent: "accent-blue"
					},
					{
							icon: "fa-solid fa-person-walking-luggage",
							label: "Outstation",
							value: latest.outstation,
							accent: "accent-amber"
					},
					{
							icon: "fa-solid fa-mug-hot",
							label: "On Leave",
							value: latest.leave,
							accent: "accent-red"
					}
			];

			let html = cards.map(card => `
					<div class="col-6 col-md-3">
							<div class="stat-card ${card.accent}">
									<div class="d-flex align-items-center gap-3">
											<span class="stat-icon"><i class="${card.icon}"></i></span>
											<div>
													<div class="stat-label">${card.label}</div>
													<div class="stat-value">${card.value}</div>
											</div>
									</div>
							</div>
					</div>`).join("");

			$("#stat-cards").html(html);
	}

	function renderChart(data) {
			new Chart(document.getElementById("myChart"), {
					type: "bar",
					data: {
							labels: data.map(row => row.date),
							datasets: [
									{
											type: "line",
											label: "Total Attendance Percentage By Day (%)",
											data: data.map(row => row.overallpercentage),
											tension: 0.3
									},
									{
											label: "Available Staff",
											data: data.map(row => row.workingpeople),
											backgroundColor: "rgba(75, 192, 192, 0.6)"
									},
									{
											label: "Outstation",
											data: data.map(row => row.outstation),
											backgroundColor: "rgba(255, 206, 86, 0.6)"
									},
									{
											label: "On Leave",
											data: data.map(row => row.leave),
											backgroundColor: "rgba(255, 99, 132, 0.6)"
									},
									{
											label: "Absents",
											data: data.map(row => row.absent),
											backgroundColor: "rgba(153, 102, 255, 0.6)"
									},
									{
											label: "Half Absents",
											data: data.map(row => row.halfabsent),
											backgroundColor: "rgba(54, 162, 235, 0.6)"
									},
									{
											label: "Total Staff",
											data: data.map(row => row.workday),
											backgroundColor: "rgba(201, 203, 207, 0.6)"
									}
							]
					},
					options: {
							responsive: true,
							scales: {
									y: { beginAtZero: true }
							},
							interaction: {
									intersect: false,
									mode: "index"
							},
							plugins: {
									legend: { position: "top" },
									title: { display: true, text: "Attendance Statistic Daily" }
							}
					}
			});
	}
});
