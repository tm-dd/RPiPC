# RPiPC (Raspberry Pi Process Control)
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

Thomas Mueller <><
