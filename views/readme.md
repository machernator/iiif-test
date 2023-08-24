<style>
	code {
		background: #FCFCFC;
		padding: 0.125em 0.25em;
		color: #323232;
		border-radius: 3px;
	}
</style>
# Views

## Configuration

`app.ini.php` sets the paths to the folders described below.

<pre>
; Template folders global verfügbar machen
tpl_views = ../views/
tpl_layout = ../views/layout/
tpl_content = ../views/content/
tpl_components = ../views/components/
tpl_sidebars = ../views/sidebars/
tpl_nav = ../views/nav/
tpl_modals = ../views/modals/
</pre>

## Structure
The folder `views` is used by [fatfree frameworks](https://fatfreeframework.com/) templating system. The file `views/index.html` is the main file. It includes all the other components of a page. Views are stored in these folders:

* layout
* content
* sidebars
* components
* forms
* modals
* ~~nav~~

`views/nav` is not used in this project.

### layout

This folder contains all the main elements of the sites html layout elements.

### content

Each pages route loads an html page from this folder. The Controller function called by the route sets the content like this: `$f3->set('content', $this->content('home'));` The name of the content is the same as the html files name without the .html extension.

### sidebars

Sidebar content is optional and can be set in the routes Controller function like this: `$f3->set('contentSidebar', 'sidebar-creators');`. The name of the content is the same as the html files name without the .html extension. Sidebar is optional.

### components

Components are layout elements that can be included in multiple pages in different layout regions. For example status, filters, search elements... should be stored in this folder.

###