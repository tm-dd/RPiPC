# RPiPC (Raspberry Pi Process Control)

## ABOUT ##

Manage Processes on an Raspberry Pis and use it for digital signage.
Maybe good to use in Churches and Free Churches.

Here are some scripts which allow you to start an stop processes on a Raspberry Pi (or other linux systems) with the rules from a PHP script on a web server.

The idea is, to define time based rules on a web page. The Raspberry Pi start and stop the programs automatically. It's also possible to use a switch, attached to the GPIO sockets, to switch between defined actions.

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

    git clone https://github.com/tm-dd/RPiPC

Edit the user and group in the scheduler.pl, if the local user and group is not pi

    specialuser@raspberrypi:~ $ whoami
    specialuser
    specialuser@raspberrypi:~ $ 

    vim RPiPC/etc/systemd/system/rpipc_scheduler.service
    vim RPiPC/etc/systemd/system/rpipc_switcher.service

    specialuser@raspberrypi:~ $ grep "User\|Group" RPiPC/etc/systemd/system/rpipc_scheduler.service RPiPC/etc/systemd/system/rpipc_switcher.service
    RPiPC/etc/systemd/system/rpipc_scheduler.service:User=specialuser
    RPiPC/etc/systemd/system/rpipc_scheduler.service:Group=specialuser
    RPiPC/etc/systemd/system/rpipc_switcher.service:User=specialuser
    RPiPC/etc/systemd/system/rpipc_switcher.service:Group=specialuser
    specialuser@raspberrypi:~ $ 

Install the scheduler:

    sudo cp -a RPiPC/usr/local/bin/scheduler.pl RPiPC/usr/local/bin/read_gpio_switch.py /usr/local/bin/
    sudo cp -a RPiPC/etc/systemd/system/rpipc_scheduler.service RPiPC/etc/systemd/system/rpipc_switcher.service /etc/systemd/system/
    sudo chown root:root /usr/local/bin/scheduler.pl /usr/local/bin/read_gpio_switch.py /etc/systemd/system/rpipc_scheduler.service /etc/systemd/system/rpipc_switcher.service
    sudo chmod 755 /usr/local/bin/scheduler.pl /usr/local/bin/read_gpio_switch.py
    sudo chmod 644 /etc/systemd/system/rpipc_scheduler.service /etc/systemd/system/rpipc_switcher.service

If you want to use the PHP scripts with a local Apache 2 web server on the Raspberry Pi:

    sudo apt install apache2 php
    sudo systemctl restart apache2
    
    sudo cp -a RPiPC/var/www/html/rpipc /var/www/html/
    sudo chown -R www-data:www-data /var/www/html/rpipc
    sudo chmod 755 /var/www/html/rpipc
    sudo chmod 644 /var/www/html/rpipc/*

OR, OPTIONAL (if the PHP scripts are running on a different host) you can use the following scripts:

    sudo cp -a RPiPC/usr/local/bin/sync_configs.sh /usr/local/bin/
    sudo chown root:root /usr/local/bin/sync_configs.sh
    sudo chmod 755 /usr/local/bin/sync_configs.sh

    sudo nano /usr/local/bin/sync_rpipc_csv_files.sh 

    specialuser@raspberrypi:~ $ cat /usr/local/bin/sync_rpipc_csv_files.sh 
    #!/bin/bash
    /usr/local/bin/sync_configs.sh /home/pi/plan.csv https://test.example.org/pids/plan.csv 'login' 'Passw0rd'
    /usr/local/bin/sync_configs.sh /home/pi/action-settings.csv https://test.example.org/pids/action-settings.csv 'login' 'Passw0rd'
    exit 0
    specialuser@raspberrypi:~ $ 

    sudo chmod 755 /usr/local/bin/sync_rpipc_csv_files.sh 
    sudo chown root:root /usr/local/bin/sync_rpipc_csv_files.sh 

    sudo nano /usr/local/bin/scheduler.pl
    specialuser@raspberrypi:~ $ diff /usr/local/bin/scheduler.pl RPiPC/usr/local/bin/scheduler.pl
    43,44c43,44
    < my $csvFileToRead='/home/pi/plan.csv';                              # the csv file actions, parameters, start and stop times
    < my $settingsFileToRead='/home/pi/action-settings.csv';              # a csv file with actions settings
    ---
    > my $csvFileToRead='/var/www/html/rpipc/plan.csv';                   # the csv file actions, parameters, start and stop times
    > my $settingsFileToRead='/var/www/html/rpipc/action-settings.csv';   # a csv file with actions settings
    specialuser@raspberrypi:~ $

Enable and start the scheduler:

    sudo systemctl enable rpipc_scheduler
    sudo systemctl start rpipc_scheduler

Optional, enable and start the switch analyser process:

    sudo systemctl enable rpipc_switcher
    sudo systemctl start rpipc_switcher

Set the audio output device for the example action type "audio":

    sudo raspi-config

Install tightvncviewer to try/use the vlc examples action types:

    sudo apt install xtightvncviewer

Optional to use AirPlay, install the AirPlay Server:

    Install the AirPlay server with the instructions from https://github.com/FD-/RPiPlay .

Optional, protect the PHP scripts with HTACCESS (login and password):

	root@raspberrypi:~# chmod 644 /var/www/html/rpipc/.htaccess
	
	root@raspberrypi:~# htpasswd -B -c /var/www/html/.htpasswd admin
	New password: 
	Re-type new password: 
	Adding password for user admin
	root@raspberrypi:~# 
	
	root@raspberrypi:~# a2enmod rewrite
	Enabling module rewrite.
	To activate the new configuration, you need to run:
	  systemctl restart apache2
	root@raspberrypi:~# 
		
	root@raspberrypi:~# cp -a /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default_org.conf 
	root@raspberrypi:~# vim /etc/apache2/sites-available/000-default.conf
	root@raspberrypi:~# diff /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default_org.conf
	14,21d13
	< 	<Directory /var/www/html/rpipc/>
	< 		Options -Indexes -FollowSymLinks
	< 		AuthType Basic
	< 		AuthUserFile /var/www/html/.htpasswd
	< 		Require valid-user
	< 		AuthName "protected access"
	< 	</Directory>
	< 
	root@raspberrypi:~# 
	
	root@raspberrypi:~# systemctl restart apache2

Optional, but usefull settings:

    - enable SSH and VNC server to control the Raspberry Pi
    - change Desktop background
    - to disable the screen sleep, see: https://raspberry-projects.com/pi/pi-operating-systems/raspbian/gui/disable-screen-sleep
    - to disable the mouse pointer, see: https://raspberrypi.stackexchange.com/questions/53127/how-to-permanently-hide-mouse-pointer-or-cursor-on-raspberry-pi 
    - disable password less access with sudo for the user pi, to protect the system

Change the actions and rules with the PHP scripts and TRY the actions.


Thomas Mueller <><
