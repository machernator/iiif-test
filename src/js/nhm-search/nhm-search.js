/*

	Usage:
	NHMSearch(
		wrapperId,		Id of the wrapping html Element
		callback		function to be called when selection is made. Receives the following arguments:
						- entryId - the id of the selected entry
						- entryText - the text of the selected entry,
		cSelector		optional, selector for element in which context the callback will be executed
	)

	The HTML must be set up with the following elements and Attributes:

	wrapperId Element
	* data-searchWrapper="1"
    * data-action=searchUrl
	|_ input:text
		* data-searchfield="1"
	|_ button
		* data-searchbutton="1"
*/
const NHMSearch = (elOrId, callback, cSelector) => {
	const collapsibles = {};
	const callBacks = {};
	let currentCollapsible;
	let currentSearchField;
	let currentsearchWrapper;
	let contextSelector = ".crudbox";

	/**
	 * initialises search
	 *
	 * @param   string | Element el        wrapper Element
	 * @param   function  callback  called after selection of node
	 * @param   string cSelector  Selector for element in which context the callback will be executed, optional
	 *
	 * @return  Element		tree element
	 */
	function init(elOrId, callback, cSelector) {
		let wrapper;
		if (elOrId instanceof Element || elOrId instanceof Document) {
			wrapper = elOrId;
		} else {
			wrapper = document.getElementById(elOrId);
		}

		if (!wrapper) return null;

		if (cSelector) {
			contextSelector = cSelector;
		}

		// Search
		const searchWrapper = wrapper.querySelector("[data-searchwrapper]");
		if (searchWrapper) {
			callBacks[searchWrapper.id] = callback;
			const searchButton = searchWrapper.querySelector(
				'[data-searchbutton="1"]'
			);
			searchButton.addEventListener(
				"pointerup",
				onSearch.bind(searchWrapper)
			);
			const searchInput = searchWrapper.querySelector(
				'[data-searchfield="1"]'
			);
			// Trigger search on enter
			searchInput.addEventListener("keydown", (e) => {
				if (e.key === "Enter") {
					e.stopPropagation();
					e.preventDefault();
					// Alternative: searchWrapper.requestSubmit();
					onSearch.call(searchWrapper);
				}
			});
			const searchResultEl =
				searchWrapper.querySelector(".nhm-search-result");
			// collapsible bootstrap search results
			collapsibles[searchWrapper.id] = new bootstrap.Collapse(
				searchResultEl,
				{
					toggle: false,
				}
			);
			searchResultEl.addEventListener("pointerup", onSearchResultClicked);
			searchResultEl.addEventListener("hidden.bs.collapse", (event) => {
				searchResultEl.innerHTML = "";
			});
		}
	}

	/**
	 * Select entry in search results. Call  callback function
	 *
	 * @param   Event  e
	 *
	 * @return  void
	 */
	function onSearchResultClicked(e) {
		const row = e.target.closest("li") ?? e.target.closest("tr");
		const id = row.dataset.entryid;
		const name = row.dataset.entryname;

		const callBackContext = row.closest(contextSelector);

		currentSearchField.value = "";
		currentSearchField.focus();

		if (name !== undefined && id !== undefined) {
			// call in context of crudbox
			callBacks[currentsearchWrapper.id].call(callBackContext, id, name);
		}

		closeSearch();
	}

	/**
	 * When search results are shown, click outside of results closes dropdown.
	 *
	 * @param   Event  e
	 *
	 * @return  void
	 */
	function onBodyClicked(e) {
		if (!e.target.closest(".nhm-search-result")) {
			closeSearch();
		}
		return false;
	}

	/**
	 * When search results are shown, pressing "Escape" closes dropdown.
	 *
	 * @param   Event  e
	 *
	 * @return  void
	 */
	function onEscapePressed(e) {
		if (e.key == "Escape") {
			closeSearch();
		}
	}

	/**
	 * Calls async loadSearch. The context ist the wrapper of the current input field
	 *
	 * @return  void
	 */
	function onSearch() {
		const form = this;
		const html = loadSearch(form);
	}

	/**
	 * Fetches tree data. Shows error message, if an error occured
	 *
	 * @param   FormData  data
	 *
	 * @return  Promise
	 */
	async function loadSearch(searchWrapper) {
		// cache elements
		currentsearchWrapper = searchWrapper;
		const searchField = searchWrapper.querySelector(
			'[data-searchfield="1"]'
		);

		currentSearchField = searchField;
		const searchUrl = searchWrapper.dataset.action;
		const searchText = searchField.value.trim();
		showLoadingAnimation(searchField);

		// formData
		const fd = new FormData();
		fd.append("search", searchText);
		const response = await fetch(searchUrl, {
			method: "POST",
			body: fd,
		});
		const html = await response.text();

		const searchResultEl =
			searchWrapper.querySelector(".nhm-search-result");
		searchResultEl.innerHTML = html;
		// click outside closes results
		document.body.addEventListener("pointerup", onBodyClicked);
		document.addEventListener("keyup", onEscapePressed);
		currentCollapsible = searchWrapper.id;
		collapsibles[currentCollapsible].show();
		hideLoadingAnimation();
	}

	/**
	 * Close search result dropdown, remove Listeners, focus search field.
	 *
	 * @return  void
	 */
	function closeSearch() {
		document.removeEventListener("keyup", onEscapePressed);
		document.body.removeEventListener("pointerup", onBodyClicked);
		collapsibles[currentCollapsible].hide();
		const searchField =
			currentsearchWrapper.querySelector("[data-searchfield]");
		currentCollapsible = "";
		currentSearchField.focus();
	}

	init(elOrId, callback, cSelector);

	return {
		search: onSearch,
	};
};

window.NHMSearch = NHMSearch;

export { NHMSearch as default };
