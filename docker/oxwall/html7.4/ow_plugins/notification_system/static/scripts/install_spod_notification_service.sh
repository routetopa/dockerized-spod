#!/usr/bin/env bash
BCOLOR=3
ABSOLUTE_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"
#define functions
startService()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "3.1 Start notification service\r\r"
    tput sgr0
    #Commands
    service spod-notification-service start
    tput setaf 2
    echo "done"
}

installService()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "3. Install notification service\r\r"
    tput sgr0
    #Commands
    mkdir /var/log/spod-notification-service
    cd ${ABSOLUTE_PATH}
    cp spod-notification-service.conf /etc/init

    startService

    tput setaf 2
    echo "done"
}

installRequiredLibraries()
{
    tput bold
    tput setaf ${BCOLOR}
    echo "2.Install the required Libraries(nodejs, etc...)\r\r"
    tput sgr0
    #Commands
    apt-get install nodejs
    ln -s /usr/bin/nodejs /usr/bin/node
    tput setaf 2
    echo "done"
}

settingSudoUser(){
    tput bold
    tput setaf ${BCOLOR}
    echo "10. Make www-data able to start etherpad-lite service\r\r"
    tput sgr0
    #Commands
    IP="$(hostname -I | cut -d' ' -f1)"
    if grep -q "Host_Alias LOCAL=${IP}" /etc/sudoers ;
        then
           tput setaf ${BCOLOR}
           echo "Host_Alias already created"
        else
           echo "Host_Alias LOCAL=${IP}" >> /etc/sudoers
    fi
    if grep -q "www-data       LOCAL=NOPASSWD:/usr/bin/service spod-notification-service" /etc/sudoers ;
        then
           tput setaf 3
           echo "Start/Stop rules already created"
        else
           echo "www-data       LOCAL=NOPASSWD:/usr/bin/service spod-notification-service start" >> /etc/sudoers
           echo "www-data       LOCAL=NOPASSWD:/usr/bin/service spod-notification-service stop" >> /etc/sudoers
    fi

    tput setaf 2
    echo "done"
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
echo "I N S T A L L  - N O T I F I C A T I O N  S E R V I C E"
tput cup 6 17
echo "     this script must be executed as root      "
tput sgr0

tput cup 7 15
echo "1.  All"

tput cup 8 15
echo "2. Install the required Libraries(nodejs, etc...)"

tput cup 9 15
echo "3. Install notification service"

tput cup 10 15
echo "4. Make www-data able to start notification service"

# Set bold mode
tput bold
tput cup 11 15
read -p "Enter your choice [1-4] " choice

case $choice in
   1) installRequiredLibraries
      installService
      settingSudoUser
      startService
      ;;
   2) installRequiredLibraries ;;
   3) installService ;;
   4) settingSudoUser ;;
esac

exitProg

