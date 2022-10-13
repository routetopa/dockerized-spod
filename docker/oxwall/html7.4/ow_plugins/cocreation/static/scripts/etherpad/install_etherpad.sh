#!/usr/bin/env bash
BCOLOR=3
ABSOLUTE_PATH="/var/www/html/ow_plugins/cocreation/static/scripts/etherpad"
DBPWD="$1"

#define functions
createUser()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "1. User Management - Create etherpad user\r\r"
    tput sgr0
    #Commands
    useradd --create-home etherpad
    #su - etherpad
    cd /home/etherpad
    tput setaf 2
    echo "done"
}

cloneReposytory()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "2. Clone Etherpad-lite project in the user directory\r\r"
    tput sgr0
    #Commands
    git clone https://github.com/ether/etherpad-lite.git
    chown -R etherpad:etherpad etherpad-lite
    tput setaf 2
    echo "done"
}

createDatabase()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "3. Create database\r\r"
    tput sgr0
    #Commands
    mysql -h mysql -u root -p${DBPWD} -e "CREATE USER 'etherpad'@'%' IDENTIFIED BY 'etherpad';"
    mysql -h mysql -u root -p${DBPWD} -e "create database etherpadLite; grant all privileges on etherpadLite.* to 'etherpad'@'%';"
    tput setaf 2
    echo "done"
}

copySettings()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "3. Copy files - setting and stylesheets\r\r"
    tput sgr0
    #Commands
    cd ${ABSOLUTE_PATH}
    mkdir -p /home/etherpad/etherpad-lite/src/static/css
    cp settings.json /home/etherpad/etherpad-lite/
    chmod 777 /home/etherpad/etherpad-lite/settings.json
    cp APIKEY.txt /home/etherpad/etherpad-lite/
    cp pad.css /home/etherpad/etherpad-lite/src/static/css
    cp timeslider.css /home/etherpad/etherpad-lite/src/static/css
    tput setaf 2
    echo "done"
}

installDeps()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "4. Install Etherpad deps\r\r"
    tput sgr0
    #Commands
    cd /home/etherpad/etherpad-lite/
    ./bin/installDeps.sh
    tput setaf 2
    echo "done"
}

alterTables()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "5. Alter etherpad tables for production\r\r"
    tput sgr0
    #Commands
    mysql -h mysql -u root -p${DBPWD} -e "alter database etherpadLite character set utf8 collate utf8_bin; use etherpadLite; alter table store convert to character set utf8 collate utf8_bin;"
    tput setaf 2
    echo "done"
}

installService()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "6. Install Etherpad-lite service\r\r"
    tput sgr0
    #Commands
    mkdir /var/log/etherpad-lite
    chown etherpad /var/log/etherpad-lite
    chown -R etherpad /var/log/etherpad-lite
    cd ${ABSOLUTE_PATH}
    cp etherpad-lite.conf /etc/init
    tput setaf 2
    echo "done"
}

startService()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "7. Start etherpad-lite service\r\r"
    tput sgr0
    #Commands
    service etherpad-lite start
    tput setaf 2
    echo "done"
}

installPlugins()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "8. Install Etherpad plugins\r\r"
    tput sgr0
    #Commands
    apt-get install abiword
    cd /home/etherpad/etherpad-lite
    npm install ep_page_view
    npm install ep_comments_page
    npm install ep_document_import_hook
    npm install ep_font_family
    npm install ep_font_size
    npm install ep_mammoth_custom
    #npm install ep_disable_change_author_name
    tput setaf 2
    echo "done"
}


installRequiredlibraries()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "9.Install the required Libraries(npm, nodejs, etc...)\r\r"
    tput sgr0
    #Commands
    apt-get install npm
    apt-get install nodejs
    ln -s /usr/bin/nodejs /usr/bin/node
    tput setaf 2
    echo "done"

    #in case of fire
    #sudo npm cache clean -f
    #sudo npm install -g n
    #sudo n 0.10.36
}

settingSudoUser(){
    tput bold
    tput setaf ${BCOLOR}
    echo "10. Make www-data able to start etherpad-lite service\r\r"
    tput sgr0
    #Commands
    #IP = "$(ifconfig | grep -A 1 'eth0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1)"
    IP="$(hostname -I | cut -d' ' -f1)"
    if grep -q "Host_Alias LOCAL=${IP}" /etc/sudoers ;
        then
           tput setaf 3
           echo "Host_Alias already created"
        else
           echo "Host_Alias LOCAL=${IP}" >> /etc/sudoers
    fi
    if grep -q "www-data       LOCAL=NOPASSWD:/usr/bin/service etherpad-lite" /etc/sudoers ;
        then
           tput setaf 3
           echo "Start/Stop rules already created"
        else
           echo "www-data       LOCAL=NOPASSWD:/usr/bin/service etherpad-lite start" >> /etc/sudoers
           echo "www-data       LOCAL=NOPASSWD:/usr/bin/service etherpad-lite stop" >> /etc/sudoers
    fi
    tput setaf 2
    echo "done"
    #in case of panic
    #pkexec visudo
}

exitProg()
{
    tput sgr0
}

#main
# clear the screen
tput clear

# Move cursor to screen location X,Y (top left is 0,0)
tput cup 3 15
# Set a foreground colour using ANSI escape
tput bold
tput setaf ${BCOLOR}
echo "ISISLab"
tput sgr0
 
tput cup 5 17
# Set reverse video mode
tput rev
tput setaf ${BCOLOR}
tput bold
echo "I N S T A L L  - E T H E R P A D  S E R V I C E"
tput cup 6 17
echo "     this script must be executed as root      "
tput sgr0

tput cup 7 15
echo "1.  All"

tput cup 8 15
echo "2.  User Management - Create etherpad user"

tput cup 9 15
echo "3.  Clone Etherpad-lite project in the user directory"

tput cup 10 15
echo "4.  Create database"

tput cup 11 15
echo "5.  Copy files - setting and stylesheets"

tput cup 12 15
echo "6.  Install Etherpad deps"

tput cup 13 15
echo "7.  Alter etherpad tables for production"

tput cup 14 15
echo "8.  Install Etherpad-lite service"

tput cup 15 15
echo "9.  Install Etherpad plugins"

tput cup 16 15
echo "10. Install the required Libraries(npm, nodejs, etc...)"

tput cup 17 15
echo "11. Make www-data able to start etherpad-lite service"

# Set bold mode
tput bold
tput cup 18 15
read -p "Enter your choice [1-10] " choice

case $choice in
   1) createUser
      cloneReposytory
      createDatabase
      copySettings
      installDeps
      alterTables
      installService
      settingSudoUser
      startService
      installPlugins
      ;;
   2) createUser ;;
   3) cloneReposytory ;;
   4) createDatabase ;;
   5) copySettings ;;
   6) installDeps ;;
   7) alterTables ;;
   8) installService ;;
   9) installPlugins ;;
   10) installRequiredlibraries ;;
   11) settingSudoUser ;;
esac

exitProg

