# RPiPC (Raspberry Pi Process Control)

## ABOUT ##

Manage Processes on an Raspberry Pis and use it for digital signage.
Maybe good to use in Churches and Free Churches.

Here are some scripts which allow you to start an stop processes on a Raspberry Pi (or other linux systems) with the rules from a PHP script on a web server.

The idea is, to define time based rules on a web page. The Raspberry Pi start and stop the programs automatically. 

Such programs can be:

* start and stop omxplayer to view external videos
* start and stop vlc to play audio
* start and stop a webpage in fullscreen
* start and stop AirPlay, VNC and other services
* ... and more

BE CAREFUL: PROTECT YOUR ACCESS TO THE PHP SCRIPTS. The scheduler try to start and stop ANY programs, which you define on the (external or local) webserver. Any person who can change the rules on the webserver, can damage or spy the Raspberry Pi. Use HTACCESS or put the Raspberry Pi in an protected internal network, to protect the Pi, other hardware and your network. Great opportunities require great responsibility.

---

## INSTALL ##

Download and install Raspberry Pi OS with desktop and recommended software

    see: https://www.raspberrypi.org/software/operating-systems/

Download the scripts:

    pi@raspberrypi:~ $ git clone https://github.com/tm-dd/RPiPC

Install the scheduler:

    pi@raspberrypi:~ $ sudo cp -a RPiPC/usr/local/bin/scheduler.pl /usr/local/bin/
    pi@raspberrypi:~ $ sudo cp -a RPiPC/etc/systemd/system/rpipc_scheduler.service /etc/systemd/system/
    pi@raspberrypi:~ $ sudo chown root:root /usr/local/bin/scheduler.pl /etc/systemd/system/rpipc_scheduler.service
    pi@raspberrypi:~ $ sudo chmod 755 /usr/local/bin/scheduler.pl /etc/systemd/system/rpipc_scheduler.service

If you want to use the PHP scripts with a local Apache 2 web server on the Raspberry Pi:

    pi@raspberrypi:~ $ sudo apt install apache2 php
    pi@raspberrypi:~ $ sudo systemctl restart apache2
    
    pi@raspberrypi:~ $ sudo cp -a RPiPC/var/www/html/rpipc /var/www/html/
    pi@raspberrypi:~ $ sudo chown -R www-data:www-data /var/www/html/rpipc
    pi@raspberrypi:~ $ sudo chmod 755 /var/www/html/rpipc
    pi@raspberrypi:~ $ sudo chmod 644 /var/www/html/rpipc/*

OR, OPTIONAL (if the PHP scripts are running on a different host) you can use the following script:

    pi@raspberrypi:~ $ sudo cp -a RPiPC/usr/local/bin/sync_configs.sh /usr/local/bin/
    pi@raspberrypi:~ $ sudo chown root:root /usr/local/bin/sync_configs.sh
    pi@raspberrypi:~ $ sudo chmod 755 /usr/local/bin/sync_configs.sh
    
    pi@raspberrypi:~ $ sudo crontab -e
    crontab: installing new crontab
    pi@raspberrypi:~ $ sudo crontab -l | tail -n 2
    * * * * * /usr/local/bin/sync_configs.sh /home/pi/plan.csv https://test.example.org/pids/plan.csv 'login' 'Passw0rd'
    * * * * * /usr/local/bin/sync_configs.sh /home/pi/action-settings.csv https://test.example.org/pids/action-settings.csv 'login' 'Passw0rd'
    pi@raspberrypi:~ $

    pi@raspberrypi:~ $ sudo nano /usr/local/bin/scheduler.pl
    pi@raspberrypi:~ $ diff /usr/local/bin/scheduler.pl RPiPC/usr/local/bin/scheduler.pl
    43,44c43,44
    < my $csvFileToRead='/home/pi/plan.csv';                              # the csv file actions, parameters, start and stop times
    < my $settingsFileToRead='/home/pi/action-settings.csv';              # a csv file with actions settings
    ---
    > my $csvFileToRead='/var/www/html/rpipc/plan.csv';                   # the csv file actions, parameters, start and stop times
    > my $settingsFileToRead='/var/www/html/rpipc/action-settings.csv';   # a csv file with actions settings
    pi@raspberrypi:~ $
    
Start the scheduler:

    pi@raspberrypi:~ $ sudo systemctl enable rpipc_scheduler
    Created symlink /etc/systemd/system/multi-user.target.wants/rpipc_scheduler.service → /etc/systemd/system/rpipc_scheduler.service.
    pi@raspberrypi:~ $ sudo systemctl start rpipc_scheduler
    pi@raspberrypi:~ $ 

Set the audio output device for the example action type "audio":

    pi@raspberrypi:~ $ sudo raspi-config

Install tightvncviewer to try/use the vlc examples action types:

    pi@raspberrypi:~ $ sudo apt install xtightvncviewer

Optional settings:

    - protect the PHP scripts with htaccess based password protection
    - enable SSH and VNC server to control the Raspberry Pi

    - change Desktop background
    - to disable the screen sleep see: https://raspberry-projects.com/pi/pi-operating-systems/raspbian/gui/disable-screen-sleep
    - to disable the mouse pointer see: https://raspberrypi.stackexchange.com/questions/53127/how-to-permanently-hide-mouse-pointer-or-cursor-on-raspberry-pi 

Change the actions and rules with the PHP scripts and TRY the actions.


Thomas Mueller <><
