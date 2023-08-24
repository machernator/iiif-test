/**
 * Creates new routes, controller, model, content, actionbar and sidebar files
 *
 * npm run new-routes routeName
 *
 * @param {string} routeName
 */

const fs = require("fs");
const { argv } = require("process");

const mode = 'dev'; // 'dev' or 'prod

let route = argv[2];
if (!route) {
	console.error("No route specified");
	return;
}

// camel case
route = route
	.toLowerCase()
	.replace(/[^a-zA-Z0-9]+(.)/g, (m, chr) => chr.toUpperCase());
const routeUC1 = route.charAt(0).toUpperCase() + route.slice(1);
const char1 = route.charAt(0);
const char1UC = char1.toUpperCase();

const files = {
	controller: {
		src: __dirname + "/tpl/Controller.tpl.php",
		dest: `../app/Controllers/${routeUC1}Controller.php`,
		destdev: __dirname + `/test/${routeUC1}Controller.php`,
	},
	model: {
		src: __dirname + "/tpl/Model.tpl.php",
		dest: __dirname + `/../app/Models/${routeUC1}Model.php`,
		destdev: __dirname + `/test/${routeUC1}Model.php`,
	},
	content: {
		src: __dirname + "/tpl/content.tpl.html",
		dest: __dirname + `/../views/content/${route}.html`,
		destdev: __dirname + `/test/${route}.html`,
	},
	actionbar: {
		src: __dirname + "/tpl/actionbar.tpl.html",
		dest: __dirname + `/../views/actionbars/actionbar-${route}.html`,
		destdev: __dirname + `/test/actionbar-${route}.html`,
	},
	sidebar: {
		src: __dirname + "/tpl/sidebar.tpl.html",
		dest: __dirname + `/../views/sidebars/sidebar-${route}.html`,
		destdev: __dirname + `/test/sidebar-${route}.html`,
	},
	controllerconfig: {
		src: __dirname + "/tpl/controllerconfig.tpl.ini",
		dest: __dirname + `/../controllerconfig/${route}-edit.ini`,
		destdev: __dirname + `/test/${route}-edit.ini`,
	},
	formconfig: {
		src: __dirname + "/tpl/formconfig.tpl.json",
		dest: __dirname + `/../formconfig/${route}Edit.json`,
		destdev: __dirname + `/test/${route}Edit.json`,
	}
};

const replacements = {
	"{route}": route,
	"{routeUC1}": routeUC1,
	"{char1}": char1,
};

// append routes to file
const routesPath = __dirname + "/../config/routes.ini";

let routesString = `
; ******** {routeUC1}s ********
GET 	@{route}: 	/{route} = \\Controllers\\{routeUC1}Controller->index
GET 	@{route}add: /{route}/create = \\Controllers\\{routeUC1}Controller->create
GET 	@{route}edit: /{route}/@{char1}id = \\Controllers\\{routeUC1}Controller->edit
POST 	@{route}save: /{route}/@{char1}id = \\Controllers\\{routeUC1}Controller->save
POST 	@{route}search: /{route}/search = \\Controllers\\{routeUC1}Controller->search
GET 	@{route}delete: /{route}/delete/@{char1}id = \\Controllers\\{routeUC1}Controller->delete
`;

for (const [placeholder, value] of Object.entries(replacements)) {
	routesString = routesString.replaceAll(placeholder, value);
}

fs.appendFile(routesPath, routesString, (err) => {
	console.log(routesString);

	if (err) {
		console.error(`Error appending to file: ${err}`);
		return;
	}
	console.log(
		`Routes were appended to the file '${routesPath}'.`
	);
});

// Define the file paths and the object containing the replacement values
for (const prop in files) {
	file = files[prop];
	const inputFile = file.src;
	const outputFile = mode === 'dev' ? file.destdev : file.dest;

	// Read the contents of the input file
	fs.readFile(inputFile, "utf8", (err, data) => {
		if (err) {
			console.error(`Error reading file: ${err}`);
			return;
		}

		// Replace the placeholders with values from the replacements object
		let updatedData = data;
		for (const [placeholder, value] of Object.entries(replacements)) {
			updatedData = updatedData.replaceAll(placeholder, value);
		}

		// Write the updated content to the output file
		fs.writeFile(outputFile, updatedData, { flag: 'w' }, (err) => {
			if (err) {
				console.error(`Error writing file: ${err}`);
				return;
			}
			console.log(`File saved as ${outputFile}`);
		});
	});
};
