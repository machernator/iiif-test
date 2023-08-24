/*
	Dependencies:
	jquery
	jsTree https://www.jstree.com/

	Usage:
	SystematicsTree(
		wrapperId,			Id of the wrapping html Element
		callback			function to be called when selection is made. Receives the following arguments:
							- the tree as a jquery Element and
							- the clicked treeJs node (see treeJs Documentation https://www.jstree.com/api/#/?f=changed.jstree)
	)

	The HTML must be set up with the following elements and Attributes:

	wrapperId Element
		* data-selectiondata 	JSON String of data that will be passed on entry selection.
	|_ form
		* data-searchform="1"
		|_ input:text
			* data-searchfield="1"
	|_ element - this will be used by jstree to show tree
		* data-treeselect="1"
	   	* data-searchurl="path/to/searchresult"
	   	* data-treeurl="path/to/treeJSON"
	|_ optional one or multiple reset buttons
		* data-reset="1"

*/
const SystematicsTree = (treeId, treeCallback) => {
	let theTree,
		jsTree,
		wrapperId,
		wrapper,
		selectionData,
		ajaxUrl,
		onSelectEntry,
		searchField,
		searchResult,
		searchResultEl,
		btnReset,
		searchUrl;

	const defaultCoreData = {
		url: function (node) {
			const id = node.id == "#" ? "#" : node.li_attr["data-entryid"];
			return id === "#" ? ajaxUrl + "0" : ajaxUrl + id;
		},
		data: function (node) {
			return { id: node.id };
		},
	};

	const treeConf = {
		core: {
			core: {
				themes: {
					stripes: true,
					icons: false,
				},
			},
			// checkbox: {
			// 	three_state: false,
			// 	cascade: "undetermined",
			// },
			data: defaultCoreData,
			plugins: ["wholerow", "changed"],
		},
	};

	/**
	 * initialises tree, loads tree data
	 *
	 * @param   Element id        wrapper Element for all treeselect elements
	 * @param   function  callback  called after selection of node
	 *
	 * @return  Element		tree element
	 */
	function init(id, callback) {
		wrapperId = id;
		wrapper = document.getElementById(wrapperId);

		if (!wrapper) return null;
		// jquery Object
		theTree = $(wrapper).find("[data-treeselect]");

		// thesaurus id, data to be passed on selection
		const selDat = wrapper.dataset.selectiondata || "{}";
		try {
			selectionData = JSON.parse(selDat);
		} catch (e) {
			selectionData = {};
		}

		onSelectEntry = callback;
		ajaxUrl = theTree.data("treeurl");
		searchUrl = theTree.data("searchurl");

		// Search
		const searchForm = wrapper.querySelector("[data-searchform]");

		if (searchForm) {
			searchField = searchForm.querySelector("[data-searchfield]");
			if (searchForm.tagName === "FORM") {
				searchForm.addEventListener("submit", (e) => {
					e.preventDefault();
					search();
				});
			}
			else {
				const searchBtn = searchForm.querySelector('button[type="submit"]')
				searchBtn.addEventListener("pointerup", (e) => {
					e.preventDefault();
					search();
				});
			}

			searchField.addEventListener("keydown", (e) => {
				if (e.key === "Enter") {
					e.preventDefault();
					search();
				}
			});

			// collapsible bootstrap search results
			searchResult = new bootstrap.Collapse(
				`#${searchForm.id} .nhm-search-result`,
				{
					toggle: false,
				}
			);

			searchResultEl = searchResult._element;

			searchResultEl.addEventListener("pointerup", onSearchResultClicked);
			searchResultEl.addEventListener("hidden.bs.collapse", (event) => {
				searchResultEl.innerHTML = "";
			});
		}

		jsTree = theTree.jstree(treeConf);
		jsTree.on("changed.jstree", onTreeChanged);
		jsTree.on("loaded.jstree", onTreeLoaded);

		// reset Buttons
		btnReset = document.querySelectorAll("[data-reset]");
		btnReset.forEach((btn) =>
			btn.addEventListener("pointerup", function (e) {
				jsTree.close_all();
			})
		);

		return theTree;
	}

	/**
	 * After tree was loaded check if nodes should be opened. globaltreePath contains
	 * the ids of the nodes to be opened
	 *
	 * @param   Element el
	 * @param   Object  data
	 *
	 * @return  void
	 */
	function onTreeLoaded(el, data) {
		if (treePath !== undefined) {
			if (treePath.length > 1) {
				openTreeNode(prefix + treePath[openNodeCount].NODE_ID);
			} else if (treePath.length === 1) {
				const lastId = prefix + treePath[openNodeCount].NODE_ID;
				theTree.jstree("select_node", lastId, true);
				document
					.getElementById(lastId)
					.scrollIntoView({ block: "center" });
			}
		}
	}

	/**
	 * Opens all nodes defined in treePath.
	 *
	 * @param   string id
	 *
	 * @return  void
	 */
	function openTreeNode(id) {
		theTree.jstree("open_node", id, onTreeNodeOpened);
	}

	/**
	 * Called recursively when treenode ist open
	 *
	 * @return  void
	 */
	function onTreeNodeOpened() {
		openNodeCount++;
		if (openNodeCount < treePathLen) {
			openTreeNode(prefix + treePath[openNodeCount].NODE_ID);
		}
		if (openNodeCount === treePathLen || treePathLen === 1) {
			const lastId = prefix + treePath[openNodeCount].NODE_ID;
			theTree.jstree("select_node", lastId, true);
			document.getElementById(lastId).scrollIntoView({ block: "center" });
		}
	}

	/**
	 * jstree callback - action on tree element has been called.
	 *
	 * @param   Event  e
	 * @param   Object  data
	 *
	 * @return  void
	 */
	function onTreeChanged(e, data) {
		// ready action does not contain node
		if (data.action === "ready") return;

		const selNode = data.node;
		const entryId = selNode ? selNode.li_attr["data-entryid"] : "";
		const currentSelectionData = {
			...selectionData,
			jsTree: theTree,
			selectedNode: selNode,
			entryId: entryId,
			action: null
		}

		if (data.action === "select_node") {
			// unselect and disable child nodes
			if (selNode.children_d.length > 0) {
				selNode.children_d.forEach(function (wrapperId, i) {
					const currentNode = theTree.jstree("get_node", wrapperId);
					if (theTree.jstree("is_checked", currentNode)) {
						theTree.jstree("uncheck_node", currentNode);
					}
					//theTree.jstree("disable_node", currentNode);
				});
				// because of unchecking children selNode is set to undetermined and is unchecked. we have to check it again
				theTree.jstree("check_node", selNode);
			}
			currentSelectionData.action = "select_node";
			// Call external function
			onSelectEntry(entryId,  data.instance.get_path(selNode, " / "), currentSelectionData);
		} else if (data.action === "deselect_node") {
			// unselect and disable child nodes
			// if (selNode.children_d.length > 0) {
			// 	selNode.children_d.forEach(function (wrapperId, i) {
			// 		const currentNode = theTree.jstree("get_node", wrapperId);
			// 		theTree.jstree("enable_node", currentNode);
			// 	});
			// }
			currentSelectionData.action = "deselect_node";

			// Call external function
			onSelectEntry(entryId,  data.instance.get_path(selNode, " / "), currentSelectionData);
		}
	}

	/**
	 * Select entry in search results. Call onTreeClicked callback function
	 *
	 * @param   Event  e
	 *
	 * @return  void
	 */
	function onSearchResultClicked(e) {
		const row = e.target.closest("li");
		const id = row.dataset.entryid;

		const name = row.innerText;
		const currentSelectionData = {
			...selectionData,
			jsTree: theTree,
			selectedNode: null,
			entryId: id,
			action: 'select_node'
		}

		onSelectEntry(id, name, currentSelectionData);
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
		if (!e.target.closest("#thesaurusSearchResult")) {
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
			searchResult.hide();
		}
	}

	/**
	 * Load new data and replace current Tree. Check, if data contains message that too many entries were found.
	 * Redraw new tree. *
	 *
	 * @return  void
	 */
	function search() {
		const searchText = searchField.value.trim();
		if (searchText) {
			const fd = new FormData();
			fd.append("search", searchText);
			showLoadingAnimation(searchField);
			const html = loadSearch(fd);
		}
	}

	/**
	 * Fetches tree data. Shows error message, if an error occured
	 *
	 * @param   FormData  data
	 *
	 * @return  Promise
	 */
	async function loadSearch(data) {
		const response = await fetch(searchUrl, {
			method: "POST",
			body: data,
		});
		const html = await response.text();
		searchResultEl.innerHTML = html;
		document.body.addEventListener("pointerup", onBodyClicked);
		document.addEventListener("keyup", onEscapePressed);
		searchResult.show();
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
		searchResult.hide();
		searchField.focus();
	}

	// nodes  are opened on tree load
	let openNodeCount = 0;
	// number of nodes to be opened excluding the currents node
	let treePathLen = 0;

	const treePath = window.treePath;

	if (treePath !== undefined) {
		treePathLen = treePath.length - 1;
	}
	let prefix = "thes_";

	init(treeId, treeCallback);

	return {
		tree: theTree,
		wrapper: wrapper
	};
};

window.SystematicsTree = SystematicsTree;

export { SystematicsTree as default };
