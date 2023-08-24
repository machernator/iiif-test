/**
 * Table Sort, based on https://codepen.io/ryangjchandler/pen/WNQQKeR
 *
 * Table must have thead and tbody
 * th is clickable. Add data-nosort to disable sorting on that column.
 */
"use strict";
const tableSort = (elOrId) => {
	function sortByColumn(e) {
		// toggle asc/desccl
		const t = e.target.closest("th");
		if (e.target.dataset.nosort) return;

		if (sortBy === t.innerText) {
			if (sortAsc) {
				sortBy = "";
				sortAsc = false;
			} else {
				sortAsc = !sortAsc;
			}
		} else {
			sortBy = t.innerText;
		}

		let rows = getTableRows()
			.sort(
				sortCallback(
					// get index of clicked column
					Array.from(t.parentNode.children).indexOf(t)
				)
			)
			.forEach((tr) => {
				tableBody.appendChild(tr);
			});
	}

	function getTableRows() {
		return Array.from(tableBody.querySelectorAll("tr"));
	}

	function getCellValue(row, index) {
		return row.children[index].innerText;
	}

	function sortCallback(index) {
		return (a, b) =>
			((row1, row2) => {
				return row1 !== "" &&
					row2 !== "" &&
					!isNaN(row1) &&
					!isNaN(row2)
					? row1 - row2
					: row1.toString().localeCompare(row2);
			})(
				getCellValue(sortAsc ? a : b, index),
				getCellValue(sortAsc ? b : a, index)
			);
	}

	let sortBy = "";
	let sortAsc = false;
	const table =
		typeof elOrId === "string" ? document.getElementById(elOrId) : elOrId;
	const tableBody = table.querySelector("tbody");
	const tableHead = table.querySelector("thead");
	tableHead.addEventListener("pointerup", sortByColumn);

	return {
		sortByColumn: sortByColumn
	}
};

window.tableSort = tableSort;

export { tableSort as default };
