////////////// polyfills ////////////////////
// requestSubmit
(function (prototype) {
	if (typeof prototype.requestSubmit == "function") return;

	prototype.requestSubmit = function (submitter) {
		if (submitter) {
			validateSubmitter(submitter, this);
			submitter.click();
		} else {
			submitter = document.createElement("input");
			submitter.type = "submit";
			submitter.hidden = true;
			this.appendChild(submitter);
			submitter.click();
			this.removeChild(submitter);
		}
	};

	function validateSubmitter(submitter, form) {
		submitter instanceof HTMLElement ||
			raise(TypeError, "parameter 1 is not of type 'HTMLElement'");
		submitter.type == "submit" ||
			raise(TypeError, "The specified element is not a submit button");
		submitter.form == form ||
			raise(
				DOMException,
				"The specified element is not owned by this form element",
				"NotFoundError"
			);
	}

	function raise(errorConstructor, message, name) {
		throw new errorConstructor(
			"Failed to execute 'requestSubmit' on 'HTMLFormElement': " +
				message +
				".",
			name
		);
	}
})(HTMLFormElement.prototype);
///////////////////////
/**
 * Removes all childnodes of the passed element
 *
 * @param	 Element	el
 *
 * @return	void
 */
function removeChildren(el) {
	while (el.firstChild) {
		el.removeChild(el.firstChild);
	}
}

/**
 * Copy content to clipboard
 *
 * @param   Event  e
 *
 * @return  void
 */
function copyToClipboard(e) {
	const copyTargetId = this.dataset.copytarget;
	if (!copyTargetId) return;

	const copyTarget = document.getElementById(copyTargetId);
	copyTarget.focus();
	copyTarget.setSelectionRange(0, copyTarget.value.length);

	// copy the selection
	var success;

	try {
		success = document.execCommand("copy");
	} catch (e) {
		success = false;
	}

	(window.getSelection ? window.getSelection() : document.selection).empty();
	copyTarget.blur();

	if (success) {
		copyTarget.classList.add("bg-warning");

		setTimeout(() => {
			copyTarget.classList.remove("bg-warning");
		}, 500);
	}
}

/**
 * Create HTML Elements for toast
 *
 * @return	void
 */
function createToastElement() {
	const tpl = document.createElement("div");
	//tpl.id = 'nhmToast';
	tpl.className =
		"toast align-items-center position-fixed top-0 end-0 me-3 mt-4 bg-success text-light";
	tpl.role = "assertive";
	tpl.setAttribute("aria-atomic", "true");
	tpl.setAttribute("data-bs-delay", "3000");
	tpl.setAttribute("data-bs-animation", true);
	tpl.innerHTML = `<div class="d-flex justify-content-between flex-fill">
		<div class="toast-body fs-6" id="toastBody">OK</div>
		<button type="button" class="btn-close me-2 m-auto fs-6 text-white" data-bs-dismiss="toast" aria-label="Close"></button>
	</div>`;

	document.body.appendChild(tpl);
	return new bootstrap.Toast(tpl, {
		animation: true,
		autohide: true,
		delay: 6000,
	});
}

/**
 * Show Loading Spinner for active autocomplete search. Spinner will be shown over right side of el.
 *
 * @param	 Element	el	input field
 *
 * @return	void
 */
function showLoadingAnimation(el) {
	if (!document.getElementById("acLoader")) {
		// create loading spinner Element
		const acLoader = document.createElement("div");
		acLoader.className = "spinner-border text-secondary";
		acLoader.id = "acLoader";

		// get Coordinates and dimesions for loading spinner
		const bcr = el.getBoundingClientRect();
		const xPos = bcr.x + window.scrollX + bcr.width - bcr.height;
		const yPos = bcr.y + 4 + window.scrollY;
		const dim = bcr.height - 8;
		acLoader.setAttribute(
			"style",
			`position:absolute; left: ${xPos}px; top: ${yPos}px; width: ${dim}px; height: ${dim}px; z-index: 1000`
		);
		acLoader.innerHTML = '<span class="visually-hidden">Loading...</span>';
		document.body.appendChild(acLoader);
	}
}
window.showLoadingAnimation = showLoadingAnimation;

/**
 * hide Loading Spinner for Autocomplete field
 *
 * @return	void
 */
function hideLoadingAnimation() {
	const acLoader = document.getElementById("acLoader");
	if (acLoader) {
		acLoader.remove();
	}
}
window.hideLoadingAnimation = hideLoadingAnimation;

/**
 * Callback for custom showToast Event. Shows Toast.
 * args.text ist the text to be shown in the toast.
 *
 * @param  object 	args
 *
 * @return  void
 */
function onShowToast(args) {
	if (!"text" in args) return;
	toast.show();
	toastBody.innerText = args.text;
}
/*********************** Init App	************************/
// counter to generate unique ids in combination with element ids
let duplicatesCounter = 0;
let toast = createToastElement();

const toastBody = document.getElementById("toastBody");
Signals.add("showToast", onShowToast);

const tooltipTriggerList = document.querySelectorAll(
	'[data-bs-toggle="tooltip"]'
);
const tooltipList = [...tooltipTriggerList].map(
	(tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
);

/*********************** Multiline Inputs initialisation	************************/
// set to height of content
const multilineInputs = document.querySelectorAll("[data-ismultiline='1']");
multilineInputs.forEach((el) => {
	el.value = el.value; el.style.height = el.scrollHeight + 'px';
});

/*********************** Query Strings	************************/
const locationSearch = window.location.search;
let queryString = null;
if (locationSearch) {
	queryString = new URLSearchParams(locationSearch);
}

/*********************** Tabs	************************/
const triggerTabList = [].slice.call(
	document.querySelectorAll(".nav-tabs button")
);

// preselect tab
if (queryString) {
	const selectedTab = queryString.get("tab");

	if (selectedTab) {
		const tabEl = document.querySelector("[data-bs-toggle]#" + selectedTab);
		if (tabEl) {
			const tab = new bootstrap.Tab(tabEl);
			tab.show();
		}
	}
}

triggerTabList.forEach(function (triggerEl) {
	const tabTrigger = new bootstrap.Tab(triggerEl);
	triggerEl.addEventListener("click", function (event) {
		event.preventDefault();
		tabTrigger.show();
	});

	triggerEl.addEventListener("show.bs.tab", (e) =>
		document.getElementById("btnFormSubmit").click()
	);
});

/*********************** Copy Link	************************/
//const copyTarget = document.getElementById("copyTargetPath");
let copyTriggers = document.querySelectorAll('[data-copytrigger="true"]');
copyTriggers.forEach((el) => el.addEventListener("click", copyToClipboard));

/*********************** Autocomplete ************************/
const acEls = document.querySelectorAll(".autocomplete-wrapper");
acEls.forEach((acEl) => initAutocomplete(acEl));

/*********************** close alerts ************************/
const closeAlerts = document.querySelectorAll(".btn-close-alert");
if (closeAlerts) {
	closeAlerts.forEach((btn) => {
		btn.onclick = (e) => e.currentTarget.parentElement.remove();
	});
}

/********************** Store opened/closed collapsibles  ************************/
const collapsibles = document.querySelectorAll('[data-bs-toggle="collapse"]');

let openPanels = {};
let storedItems = localStorage.getItem("collapsibles");

if (storedItems) {
	openPanels = JSON.parse(storedItems);
}
localStorage.setItem("collapsibles", '');
// open stored panels
for (const id in openPanels) {
	if (openPanels[id] === true) {
		const el = document.getElementById(id);
		if (el) {
			const c = new bootstrap.Collapse(el).show();
		}
	}
}

if (collapsibles.length > 0) {
	collapsibles.forEach((el) => {
		const accordion = el.closest('.accordion');

		accordion.addEventListener("show.bs.collapse", function (e) {
			// data-nostore prevents saving to localstorage. Used for searchresults.
			const nostore = e.target.dataset.nostore;
			if(nostore) return;
			const id = e.target.id;
			openPanels[id] = true;
			localStorage.setItem("collapsibles", JSON.stringify(openPanels));
		});

		accordion.addEventListener("hide.bs.collapse", function (e) {
			// data-nostore prevents saving to localstorage. Used for searchresults.
			const nostore = e.target.dataset.nostore;
			if(nostore) return;
			const id = e.target.id;
			openPanels[id] = false;
			localStorage.setItem("collapsibles", JSON.stringify(openPanels));
		});
	});
}
