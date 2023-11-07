/**
 * No need for webpack for most of the time. This script will copy files from node_modules to your project.
 *
 * package.json:	"scripts": {	"copy-dist": "node copy-dist.js"	}
 * Call: npm run copy-dist
 *
 * Codekit: set resources/js to ignore compilation
 */
const fs = require("fs");
const path = require("path");

const fromPath = "./node_modules/";
const destination = "./public/resources/js/"

// List of modules to copy. If files is empty or false, all files will be copied.
// src & dest - only define path within fromPath/toPath. fromPath will be added to src, toPath will be added to dest
const modules = [
	{
		src: "alpinejs/dist/",
		dest: "alpine/",
		files: [],
	},
	{
		src: "@alpinejs/collapse/dist/",
		dest: "alpine/collapse/",
		files: [],
	},
	{
		src: "@alpinejs/persist/dist/",
		dest: "alpine/persist/",
		files: [],
	},
	{
		src: "jquery/dist/",
		dest: "jquery/",
		files: [],
	},
	{
		src: "jstree/dist/",
		dest: "jstree/",
		files: [],
	},
	{
		src: "axios/dist/",
		dest: "axios/",
		files: ['axios.min.js', 'axios.min.js.map'],
	},
	{
		src: "universalviewer/dist/umd/",
		dest: "universalviewer/",
		files: [],
	},
	{
		src: "universalviewer/dist/",
		dest: "universalviewer/",
		files: ['uv.css'],
	},
	{
		src: "mirador/dist/",
		dest: "mirador/",
		files: ['mirador.min.js', 'mirador.min.js.map'],
	},
	{
		src: "filepond/dist/",
		dest: "filepond/",
		files: [],
	},
	// {
	// 	src: "@dbmdz/mirador-downloadmenu/",
	// 	dest: "mirador-downloadmenu/",
	// 	files: ['downloadMenu.min.js', 'downloadMenu.min.css'],
	// },
];

// Recursively copy files from source to destination
function copyFiles(source, destination, files) {
	// Check if source exists
	if (!fs.existsSync(source)) {
		console.log(`Source directory '${source}' does not exist.`);
		return;
	}

	// Check if destination exists, create if not
	if (!fs.existsSync(destination)) {
		console.log(
			`Destination directory '${destination}' does not exist, creating...`
		);
		fs.mkdirSync(destination, { recursive: true });
	}

	// Get list of files in source directory
	console.log("FILES:" , files);

	if(files === false || !files.length){
		files = fs.readdirSync(source);
	}

	// Copy each file
	files.forEach((file) => {
		const sourcePath = path.join(source, file);
		const destinationPath = path.join(destination, file);

		// Check if file is a directory, if so recurse
		if (fs.lstatSync(sourcePath).isDirectory()) {
			copyFiles(sourcePath, destinationPath, []);
		} else {
			// Copy file
			console.log(`Copying file \n\t${sourcePath} to \n\t${destinationPath}`);
			fs.copyFileSync(sourcePath, destinationPath);
		}
	});
}

// Copy files
modules.forEach((mod) => {
	const sourceDir = fromPath + mod.src;
	const destinationDir = destination + mod.dest;
	const files = mod.files;
	copyFiles(sourceDir, destinationDir, files);
});
