function NHMTreeView (container, options) {
	if (container) {
		if (typeof container === 'string') {
			// if CSS Selector is passed, only first Element will be selected
			container = document.querySelector(container);
		}
		this.container = container;
		this.options = {
			toggleClass: 'tree-view-toggle',
			clickable: 'a',
			onClickableClicked: function (e) {
				e.preventDefault();
				return false;
			}
		};

		// merge options if available
		if (options) {
			this.options = Object.assign(this.options, options);
		}

		this.container.addEventListener('click', onTreeviewClicked.bind(this));
	}
}

function onTreeviewClicked(e) {
	const t = e.target;
	console.log(this.options.toggleClass, t.classList.contains(this.options.toggleClass));

	if (t.matches(this.options.clickable)) {
		this.options.onClickableClicked(e);
	} else if (t.classList.contains(this.options.toggleClass)) {
		const par = t.parentNode;
		const isOpen = !par.classList.contains('closed');

		if (isOpen) {
			closeEntry(par);
			closeSubEntries(par);
		} else {
			openEntry(par);
		}
	}
}

function openEntry(el) {
	// const mySub = el.querySelector('.sub-entry');
	// if (mySub) {
	// 	mySub.style.height = mySub.scrollHeight + 'px';
	// }
	el.classList.remove('closed');
	el.classList.add('open');
}

function closeEntry(el) {
	// const mySub = el.querySelector('.sub-entry');
	// if (mySub) {
	// 	mySub.style.height = 0;
	// }
	el.classList.add('closed');
	el.classList.remove('open');
}

function closeSubEntries(el) {
	const mySubs = el.querySelectorAll('.open');
	mySubs.forEach(subEl => closeEntry(subEl));

}

export default NHMTreeView;