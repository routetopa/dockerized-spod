# CoCreation

### About this plugin

The **CoCreation** is a collaborative space to guide users to collaborate in the construction of knowledge or data. The space is meant to be used by small groups,
where user are invited to participate by the creator of the room. There are two kinds of room: the *Cocreation knowledge room*, where the group is invited to reflect
on Open Data to answer problems and create awareness of data, and a *Cocreation data room*, where it is possible to cocreate datasets.

In the Cocreation you can find:

* Cocreation knowledge room
* Cocreation data room
* View all datasets

Once the users have finished to create a dataset in a *CoCreation data room* the related dataset can be published to be used inside or outside the SPOD.
All published dataset can be accessed in the plugin main page and for each dataset the user can:

* Show the dataset as a flat table
* Download the dataset as CSV file
* Copy the endpoint(URL) of the dataset to use it in a new datalet inside or outside SPOD or in another system
* download the notes related to the dataset, added by the room users, as html format

In the *CoCreation plugin admin panel* the admin user can start/stop the document and spreadsheet server and active/deactivate the creation of dataset/knowledge room

### Installation guide

To install *CoCreation* plugin:

* Clone this project by following the github instruction on *SPOD_INSTALLATION_DIR/ow_plugins*
* Install the plugin on SPOD by *admin plugins panel*
* Install SPOD version of Etherpad :

  - Run the installation script in *SPOD_INSTALLATION_DIR/COCREATION_PLUGIN_INSTALLATION_DIR/static/script/etherpad/install_etherpad.sh ROOT_DB_PASSWORD* and select *All*.
    **This script must be run ad root**

* Install SPOD version of Ethersheet :
  - Run the installation script in *SPOD_INSTALLATION_DIR/COCREATION_PLUGIN_INSTALLATION_DIR/static/script/ethersheet/install_ethersheet.sh ROOT_DB_PASSWORD* and select *All*.
    **This script must be run ad root**

After the installation has been successful, the admin user can access to admin panel of CoCreation plugin to verify that document and spreadsheet server work.
Root user can start/stop the document/spreadsheet server via shell by the command:

* *service etherpad-lite start/stop/restart*
* *service ethersheet start/stop/restart*

### Note

*To use this plugin you must install the TChat plugin (spod-tchat-plugin). [TChat plugin](https://github.com/routetopa/spod-plugin-tchat) implements the discussion section in the Data Cocreation Room*



