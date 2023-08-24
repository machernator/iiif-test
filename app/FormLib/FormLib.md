# FormLib

A Form Library for displaying and validating forms. Depends on CSRF.php and [wixel/gump](https://github.com/Wixel/GUMP) Library.

There are two main Classes:

- Form.php
- Input.php

Form.php receives a configuration array and generates all its input fields. It can validate either POST or GET data with the help of GUMP validation library. Its render method displays the whole form.

Input and its derived classes display the fields with labels, descriptions and errors.

Render can be called to render the whole field. Inidividual render methods are available for rendering

- label
- field
- error
- description

## Configuration

The form tag itself and the individual fields are initialised by some neutral defaults and the passed configuration.

### Form configuration

 Name | Default |Required|Description
---|---|---|---
 id | "" | yes | html id of the form
 action | "" |  no| URL of script to send the form to
 method | post | no | get or post http method
 enctype | "" | no | set to mulitpart/form-data when uploading files