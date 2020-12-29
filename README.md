# RPiPC (Raspberry Pi Process Control)

## ABOUT ##

Manage Processes on an Raspberry Pis and use it for digital signage.

Here are some scripts which allow you to start an stop processes on a Raspberry Pi (or other linux systems) with the rules from a PHP script on a web server.

The idea is, to define time based rules on a web page. The Raspberry Pi start and stop the programs automatically. 

Such programs can be:

* start and stop omxplayer to view external videos
* start and stop vlc to play audio
* start and stop a webpage in fullscreen
* start and stop AirPlay, VNC and other services
* ... and more

BE CAREFUL: PROTECT YOUR ACCESS TO THE PHP SCRIPTS. The scheduler try to start and stop ANY programs, which you define on the (external or local) webserver. Any person who can change the rules on the webserver, can damage or spy the Raspberry Pi. Use HTACCESS or put the Raspberry Pi in an protected internal network, to protect the Pi, other hardware and your network. Great opportunities require great responsibility.

## INSTALL ##

Download the scripts:

    pi@raspberrypi:~ $ git clone https://github.com/tm-dd/RPiPC

Install the scheduler:

    pi@raspberrypi:~ $ sudo cp -a RPiPC/usr/local/bin/scheduler.pl /usr/local/bin/
    pi@raspberrypi:~ $ sudo cp -a RPiPC/etc/systemd/system/rpipc_scheduler.service /etc/systemd/system/
    pi@raspberrypi:~ $ sudo chown root:root /usr/local/bin/scheduler.pl /etc/systemd/system/rpipc_scheduler.service
    pi@raspberrypi:~ $ sudo chmod 755 /usr/local/bin/scheduler.pl /etc/systemd/system/rpipc_scheduler.service

Optional (if the php scripts are running on a different host) you can use the following script

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
    
OR, if you want to use the PHP scripts with an Apache 2 server to control:

    pi@raspberrypi:~ $ sudo apt install apache2 php
    pi@raspberrypi:~ $ sudo systemctl restart apache2
    
    pi@raspberrypi:~ $ sudo cp -a RPiPC/var/www/html/rpipc /var/www/html/
    pi@raspberrypi:~ $ sudo chown -R www-data:www-data /var/www/html/rpipc
    pi@raspberrypi:~ $ sudo chmod 755 /var/www/html/rpipc
    pi@raspberrypi:~ $ sudo chmod 644 /var/www/html/rpipc/*

Start the scheduler:

    pi@raspberrypi:~ $ sudo systemctl enable rpipc_scheduler
    Created symlink /etc/systemd/system/multi-user.target.wants/rpipc_scheduler.service → /etc/systemd/system/rpipc_scheduler.service.
    pi@raspberrypi:~ $ sudo systemctl start rpipc_scheduler
    pi@raspberrypi:~ $ 

Change the actions and rules with the php scripts and try the actions.


Thomas Mueller <><
