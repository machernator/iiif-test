/**
 * Form Utilities to be used in combination with php FormLib Classes
 */

/**
 * Only numbers (positive or negative) will be allowed
 *
 * @param  string  value
 *
 * @return  string
 */
function onlyNumbers(value) {
	if (value === "-") return value;
	// Remove invalid characters from the input
	value = value.replace(/[^\d-]+/g, "");
	// if value has more than one minus, remove remaining minus. Allow minus only at first place
	const minusSplit = value.split("-");
	if (minusSplit.length === 2 && minusSplit[0] === "") {
		// first character was a zero, so minus was removed. Add it again
		value = "-" + minusSplit[1];
	} else if (minusSplit.length === 2 && minusSplit[1] === "") {
		value = minusSplit[0];
	} else if (minusSplit.length > 2) {
		let [first, ...rest] = minusSplit;
		if (first === "") {
			first = "-";
		}
		const clean = rest.join("");
		value = first + clean;
	}
	return value;
}

/**
 * Only numbers (positive or negative) and one comma will be allowed
 *
 * @param   string value
 *
 * @return  string
 */
function onlyNumbersAndOneComma(value) {
	if (value === "-") return value;
	const regex = /^-?\d+(,\d*)?$/;
	if (!regex.test(value)) {
		// Remove invalid characters from the input
		value = value.replace(/[^-\d,]/g, "");

		// if value is only minus, allow it
		if (value !== "-") {
			// if value has more than one comma, remove remaining commas. Prevent starting with comma
			const commaSplit = value.split(",");
			if (commaSplit.length === 2 && commaSplit[0] === "") {
				value = commaSplit[1];
			} else if (commaSplit.length > 2) {
				const [first, ...rest] = commaSplit;
				const clean = rest.join("");
				value = first + "," + clean;
			}

			// if value has more than one minus, remove remaining minus. Allow minus only at first place
			const minusSplit = value.split("-");
			if (minusSplit.length === 2 && minusSplit[0] === "") {
				if (minusSplit[1] === ",") {
					minusSplit[1] = "";
				}
				value = "-" + minusSplit[1];
			} else if (minusSplit.length > 2) {
				let [first, ...rest] = minusSplit;
				if (first === "") {
					first = "-";
				}
				const clean = rest.join("");
				value = first + clean;
			}
		}
	}

	return value;
}

/**
 * Convert number in certain locale to machine readable float
 *
 * @param   string  germanNumber
 *
 * @return  float
 */
function parseNumber(germanNumber, locale = "de-DE") {
	const numberFormat = new Intl.NumberFormat(locale);
	const floatNumber = numberFormat.format(germanNumber);
	return floatNumber;
}
/**
 * Allow date in format YYYY-MM-DD or YYYY-MM or YYYY. Allow minus sign.
 * Automatically filter all other characters.
 *
 * @param   string  value
 *
 * @return  string
 */
function dateOptionalMonthDay(value) {
	value = value.replace(/[^0-9-]/g, "");
	let last;
	let len = value.length;
	const lastChar = value[len - 1];

	switch (len) {
		case 1:
		case 2:
		case 3:
		case 4:
			value = value.replace("-", "");
			break;
		case 5:
		case 8:
			// insert minus if not already there
			last = len - 1;
			if (value[last] !== "-") {
				value = value.slice(0, -1) + "-" + value.slice(-1);
			}
			break;
		case 6:
			if (lastChar !== "-" && lastChar > 1) {
				value = value.slice(0, -1);
			} else if (lastChar === "-") {
				value = value.slice(0, -1);
			}
			break;
		case 7:
			if (lastChar === "-") {
				value = value.slice(0, -1);
			}
			// allow only 12 months
			if (value[5] === "1" && value[6] > 2) {
				value = value.slice(0, -1);
			}
			break;
		case 9:
			if (lastChar !== "-" && lastChar > 3) {
				value = value.slice(0, -1);
			}
		case 10:
			// insert minus if not already there
			if (lastChar === "-") {
				value = value.slice(0, -1);
			}
			// only allow 31 days
			if (value[8] === "3" && value[9] > 1) {
				value = value.slice(0, -1);
			}
			break;
		case 11:
			value = value.slice(0, -1);
	}

	return value;
}

/**
 * Delete file from server and remove it from DOM
 *
 * @param   Element el
 *
 * @return  void
 */
function deleteFile(el) {
	const fileId = el.dataset.fileid;
	if (!fileId) {
		return;
	}

	axios.delete("/file/" + fileId).then(() => {
		const currentFile = el.closest(".current-file");
		currentFile.remove();
	});
}
////////// Custom Data Search Fields initialization //////////
// const dataSearches = document.querySelectorAll(
// 	".wf-input-multiple, .wf-input-single"
// );

// dataSearches.forEach((el) => {
// 	WFSearch(el, () => {}, ".wf-data-search");
// });

const FormUtilities = {
	onlyNumbers: onlyNumbers,
	onlyNumbersAndOneComma: onlyNumbersAndOneComma,
	deleteFile: deleteFile,
	dateOptionalMonthDay: dateOptionalMonthDay,
	parseNumber: parseNumber
};

window.FormUtilities = FormUtilities;
export { FormUtilities as default };
