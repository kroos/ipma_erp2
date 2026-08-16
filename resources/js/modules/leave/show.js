// Leave (show) — print / back helpers
// Exposed on window because the blade's buttons use inline onclick="printPage()" / onclick="back()"
window.printPage = function () {
	window.print();
};

window.back = function () {
	window.history.back();
};
