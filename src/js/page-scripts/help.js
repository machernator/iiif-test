/**
 * Create TOC from headings in help pages main content.
 * Enable simple page text search
 */
(function () {
	// get all headings in main content
	const main = document.querySelector("main");
	const sideBarHelp = document.getElementById("sidebarHelp");
	const helpContent = document.getElementById("helpContent");
	const headings = main.querySelectorAll("h2, h3, h4, h5, h6");
	const searchField = document.getElementById("helpSearchValue");
	const markTag = "mark";

	// create TOC
	const helpToc = document.createElement("ul");
	helpToc.className = "help-toc list-unstyled";
	let currentLevel = 0;
	let currentList = helpToc;
	let prevList = null;
	let countHeading = 0;

	headings.forEach((heading) => {
		const level = parseInt(heading.tagName.substring(1));
		if (heading.id === "") {
			heading.id = "helpHeading_" + countHeading;
			countHeading++;
		}
		const listItem = document.createElement("li");
		const link = document.createElement("a");
		link.href = "#" + heading.id;
		listItem.className = "level-" + level;

		link.textContent = heading.textContent;
		link.href = "#" + heading.id;
		listItem.appendChild(link);

		currentList.appendChild(listItem);
		currentLevel = level;
	});

	// Style jump mark target
	window.addEventListener('hashchange', e => {
		if (!headings) return;
		headings.forEach(el => el.classList.remove('current'));

		const id = location.hash.substring(1);
		if (!id) return;

		const targetEl = document.getElementById(id);
		if (!targetEl) return;
		if (targetEl) {
			targetEl.classList.add('current');
		}
	});

	sideBarHelp.appendChild(helpToc);

	document.addEventListener("alpine:init", (e) => {
		Alpine.data("help", function () {
			return {
				searchValue: "",
				currResult: 0,
				results: [],
				showResultsNav: false,
				/**
				 * Search for text in dom of helpContent
				 *
				 * @param   Event  e
				 *
				 * @return  void
				 */
				searchText(e) {
					e.preventDefault();
					// https://dev.to/kokaneka/highlight-searched-text-on-a-page-with-just-javascript-17b3
					this.searchValue = this.searchValue.trim();
					if (this.searchValue === "") {
						searchField.focus();
						return;
					}
					this.resetSearchResults();
					let nrResults = 0;

					const searchNodes = helpContent.querySelectorAll("*");
					searchNodes.forEach((node) => {
						node.childNodes.forEach((child) => {
							if (child.nodeType === Node.TEXT_NODE) {
								const text = child.textContent;
								if (text === "") {
									return;
								}

								// find text in node
								const regex = new RegExp(this.searchValue, "gi");
								// found entry, mark it
								if (regex.test(text)) {
									const replacedText = text.replace(
										regex,
										`<${markTag} id="helpResult_${nrResults}">$&</${markTag}>`
									);
									nrResults++;
									// create temp Element to allow innerHTML
									const newNode =
										document.createElement("span");
									newNode.innerHTML = replacedText;
									node.replaceChild(newNode, child);
									// replace newly created node with its contents, we do not want to keep the span
									while (newNode.firstChild) {
										node.insertBefore(
											newNode.firstChild,
											newNode
										);
									}
									node.removeChild(newNode);
									// store search result to be able to navigate to it
									this.results.push(node.querySelector(markTag));
								}
							}
						});
					});

					if (this.results.length > 0) {
						this.showResultsNav = true;
						this.results[this.currResult].scrollIntoView();
						this.results[this.currResult].classList.add('current');
					}

				},
				/**
				 * Clear previoully marked text
				 *
				 * @return  void
				 */
				clearSearch() {
					this.searchValue = '';
					this.resetSearchResults();
					helpContent.querySelectorAll(markTag).forEach((node) => {
						const parent = node.parentNode;
						parent.replaceChild(
							document.createTextNode(node.textContent),
							node
						);
						// now text nodes are split, normalize them
						parent.normalize();
					});
				},
				jumpToResult(forwards = true) {
					if (this.results.length === 0) {
						return;
					}

					if (forwards) {
						this.currResult++;
					}
					else {
						this.currResult--;
					}

					if (this.currResult >= this.results.length) {
						this.currResult = 0;
					}
					else if (this.currResult < 0) {
						this.currResult = this.results.length - 1;
					}

					this.results[this.currResult].scrollIntoView();
					this.results.forEach(el => el.classList.remove('current'));
					this.results[this.currResult].classList.add('current');
				},
				/**
				 * Reset previous search results
				 *
				 * @return  void
				 */
				resetSearchResults() {
					helpContent.querySelectorAll(markTag).forEach((node) => {
						const parent = node.parentNode;
						const text = document.createTextNode(node.innerText);
						parent.replaceChild(text, node);
						parent.normalize();
					});
					this.results = [];
					this.showResultsNav = false;
					this.currResult = 0;
				}
			};
		});
	});
})();
