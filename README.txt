Installation Instructions
=========================

This is the module element of the Moodle direct version 2 package, which contains 3 elements for each respective plugin on Moodle. Each plugin requires that you are using Moodle 2.3 or higher. Before installing these plugins firstly make sure you are logged in as an Administrator.

Module
------

The main plugin code is required by both of the other plugins to work. To install, all you need to do is copy the turnitintooltwo directory in to your moodle installations module directory /mod. You should then go to "Site Administration" > "Notifications" and follow the on screen instructions.

To configure the plugin go to "Site Administration" > "Plugins" > "Activity Modules" > "Turnitin Assignment 2" and enter your Turnitin account Id, shared key and API URL.

Note that the URL is different for this package to previous Turnitin plugins. It should be https://api.turnitin.com, https://submit.ac.uk. or https://sandbox.turnitin.com.

Troubleshooting
---------------

You may need to ensure that within your designated moodledata directory; the turnitintooltwo subdirectory and the subsequent logs subdirectory have the correct permissions to be able to create directories and files.

Pop-ups will need to be enabled on the browser being used if access to the Turnitin Document Viewer is required.