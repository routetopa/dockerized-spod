# Notification system

### About this plugin

Notification system is a plugin for synchronous notification on SPOD. The notifications are use to get real time feedback about users activities in some
SPOD area for instance:

* Cocreation :
   - Users add/remove a new datalet in a room
   - Users modify metadata information related to the dataset
   - Users add comment in the discussion area

* Agora :
   - Users add comment in the discussion
   - Update information in the graph area

In the *Notification system plugin admin panel* the admin user can start/stop the notification server. **Note that by stopping the server the system will not provide a real time
feedback to the users activities**

### Installation guide

To install *Notification system* plugin:

* Clone this project by following the github instruction on *SPOD_INSTALLATION_DIR/ow_plugins*
* Install the plugin on SPOD by *admin plugins panel*
* Install SPOD Notification server :
  - Run the installation script in *SPOD_INSTALLATION_DIR/NOTIFYCATION_SYSTEM_PLUGIN_INSTALLATION_DIR/static/script/install_spod_notification_service.sh* and select *All*.
    **This script must be run ad root**
