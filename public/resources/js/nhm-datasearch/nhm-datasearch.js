"use strict";
/*
	Allows autocomplete Search
	The searchid defined in wrapper Element will be used for the other necessary Elements as id prefix.
	HTML Elements:
	- Wrapper Element
		- class nhm-data-search
		- data-idprefix 	Prefix of all Element Ids
		- must be passed to init function
		- data-searchurl - fetch JSON with results from this url
		- data-choice - when set to single only one entry is allowed
	- Text Input - Id has to be searchid + "Search"
	- Clear Button must set id = searchid + "Clear"
	- Element with class current-selection must contain ul with id = searchid + "Selection"
	- ul that shows results must set id = searchid + "Results"

	- Returned JSON Format:
		[{"id":1,"name":"Will Milne"}, {"id":2,"name":"Meghan Adamovsky"}, ... ]
*/

// store all autocomplete Fields. Uses element id as key.
let fields = Object.create(null);

/**
 * Initialise AC Search für the passed Element
 *
 * @param   {[type]}  el  Text Input field
 *
 * @return  void
 */
function init(el) {
	const searchid = el.dataset.idprefix;

	// data needed for each field
	fields[searchid] = Object.create(null);
	fields[searchid].id = searchid;
	fields[searchid].el = el;
	fields[searchid].url = el.dataset.searchurl;
	fields[searchid].searchField = el.querySelector(`[data-id=${searchid}Search]`);
	fields[searchid].results = el.querySelector(`[data-id=${searchid}Results]`);
	fields[searchid].selectedElemsWrapper = el.querySelector(`[data-id=${searchid}Selection]`);
	fields[searchid].btnClear = el.querySelector(`[data-id=${searchid}Clear]`);
	fields[searchid].btnClearSearch = el.querySelector(`.btn-clearsearch`);
	fields[searchid].formField = el.querySelector(`[data-id=${searchid}]`);
	fields[searchid].searchResultsWrapper = el.querySelector('.nhm-search-wrapper');
	fields[searchid].searchResultsWrapper.style.display = 'none';
	fields[searchid].selectedData = [];
	fields[searchid].singleChoice = (el.dataset.choice && el.dataset.choice == 'single') ? true : false;

	// check if entries already exist
	if (fields[searchid].selectedElemsWrapper) {
		const prevData = fields[searchid].selectedElemsWrapper.querySelectorAll('li');
		const selectedData = fields[searchid].selectedData;
		prevData.forEach(el => {
			const entryId = el.dataset.entryid;
			const text = el.dataset.text;
			selectedData.push({
				'id': entryId,
				'text': text
			});
		});
		updateSelectionField(fields[searchid]);
	}

	/******* Events **********/
	// Clear search and reset search results
	fields[searchid].btnClear.addEventListener('click', e => {
		fields[searchid].searchField.value = '';
		clearChildren(fields[searchid].results);
		fields[searchid].searchResultsWrapper.style.display = 'none';
	});

	// clear only search field
	fields[searchid].btnClearSearch.addEventListener('click', e => {
		fields[searchid].searchField.value = '';
		clearChildren(fields[searchid].results);
		fields[searchid].searchResultsWrapper.style.display = 'none';
	});


	// fetch Results on keyup. At least two characters input trigger fetch
	fields[searchid].searchField.addEventListener('keyup', function (e) {
		const val = this.value;
		const id = this.closest('[data-idprefix]').dataset.idprefix;
		const field = fields[id];

		// Clear if less than two characters are available
		if (val.length < 2) {
			clearChildren(field.results);
			fields[searchid].searchResultsWrapper.style.display = 'none';
			return;
		}

		// Delay input to minimize number of requests
		clearTimeout(window.searchTimeoutFinished);
		window.searchTimeoutFinished = setTimeout(function () {
			getResults(field);
		}, 800);
	});

	// Click on result adds element to selectedData and selectedElemsWrapper (DOM)
	fields[searchid].results.addEventListener('click', function (e) {
		const id = e.target.closest('[data-idprefix]').dataset.idprefix;
		const text = e.target.innerText;
		const entryid = e.target.dataset.entryid;

		// if entry is already in selected elements, return
		for (const key in fields[id].selectedData) {
			if (entryid === fields[id].selectedData[key].id) {
				return;
			}
		}

		// allow only one entry when singleChoice is true
		if (fields[id].singleChoice) {
			fields[id].selectedData = [{
				'id': entryid,
				'text': text
			}];
		} else {
			fields[id].selectedData.push({
				'id': entryid,
				'text': text
			});
		}

		// Add new entry
		updateSelection(fields[id]);
		fields[searchid].searchField.value = '';
		clearChildren(fields[searchid].results);
		fields[searchid].searchField.focus();
	});

	// Delete child elements
	fields[searchid].selectedElemsWrapper.addEventListener('click', function (e) {
		const t = e.target;

		if (t.classList.contains('btn-close')) {
			deleteEntry(t);
		}
	});

	// Sortable Selected Elements
	if (fields[searchid].selectedElemsWrapper.classList.contains('sortable-search-items')) {
		const sortableConf = {
			animation: 150,
			ghostClass: 'sortable-ghost',
			// onUpdate: function (e) {
			// 	console.log(searchid, e);
			// }
		}
		new window.Sortable(fields[searchid].selectedElemsWrapper, sortableConf);
	}
}

/**
 * Fetch JSON data and pass it to updateResults
 *
 * @param   Object  field
 *
 * @return  void
 */
function getResults(field) {
	window.showLoadingAnimation(field.searchField);
	const fd = new FormData();
	fd.append('token', csrftoken);
	fd.append('search', field.searchField.value);
	field.searchResultsWrapper.style.display = 'block';

	const response = fetch(field.url, {
		method: 'post',
		'body': fd
	}).
	then(response => response.json()).
	then(data => {
		window.hideLoadingAnimation(field.searchField);
		updateResults(field, data);
	});
}

/**
 * Show Results
 *
 * @param   Object  field All data for current search element
 * @param   Object data   JSON Response
 *
 * @return  void
 */
function updateResults(field, data) {
	const results = field.results;
	clearChildren(results);
	let frag = document.createDocumentFragment();

	for (const key in data) {
		const entry = data[key];
		const li = document.createElement('li');
		li.setAttribute('data-entryid', entry.id);
		li.setAttribute('data-entrytext', entry.text);
		const text = document.createTextNode(entry.text);
		li.appendChild(text);
		frag.appendChild(li);
	}
	results.appendChild(frag);
}

/**
 * Updates DOM Elements representing current selected entries
 *
 * @param   Object  field
 *
 * @return  void
 */
function updateSelection(field) {
	const selectedElemsWrapper = field.selectedElemsWrapper;
	clearChildren(selectedElemsWrapper);
	if (field.selectedData.length > 0) {
		// Delete old entries
		let lis = document.createDocumentFragment();
		// Create entries
		field.selectedData.forEach(function (el) {
			const li = document.createElement('li');
			li.setAttribute('data-entryid', el.id);
			li.setAttribute('data-text', el.text);
			const btn = document.createElement('button');
			btn.setAttribute('type', 'button');
			btn.className = 'btn btn-sm btn-close';
			btn.setAttribute('data-entryid', el.id);
			//btn.setAttribute('data-idprefix', field.id);
			li.append(btn, el.text);
			lis.append(li)
		});
		selectedElemsWrapper.appendChild(lis);
	}
	updateSelectionField(field);
}

/**
 * Update hidden Field with currently selected ids. Ids are stored as comma-separated values.
 *
 * @param   Object  field
 *
 * @return  void
 */
function updateSelectionField(field) {
	let val = '';
	let del = '';
	field.selectedData.forEach(function (el) {
		val += del + el.id;
		del = ',';
	});
	field.formField.value = val;
}

/**
 * Delete Entry from current selectedData and update selected Elements DOM
 *
 * @param   Element  btn
 *
 * @return  void
 */
function deleteEntry(btn) {
	const field = fields[btn.closest('[data-idprefix]').dataset.idprefix];
	const entryid = btn.dataset.entryid;
	const selectedData = field.selectedData;

	for (const key in selectedData) {
		const entry = selectedData[key];
		if (entry.id == entryid) {
			selectedData.splice(key, 1);
			updateSelection(field);
		}

	}
}

/**
 * Removes all entries from html Element
 *
 * @param   Element  el
 *
 * @return  void
 */
function clearChildren(el) {
	while (el.firstChild) {
		el.removeChild(el.firstChild);
	}
}
///////////////////// export /////////////////

const nhmDataSearch = {
	init: init
}

export {
	nhmDataSearch as
	default
};