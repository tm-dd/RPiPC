#!/usr/bin/python3
#
# by Thomas Mueller (developer@mueller-dresden.de)
#
# write a switch counter to switch between different commands with scheduler.pl after pressing a key
#
# Copyright (C) 2023, Thomas Mueller
# 
# Redistribution and use in SOURCE and BINARY forms, with or without
# modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this
#    list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright notice,
#    this list of conditions and the following disclaimer in the documentation
#    and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
# ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
# WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
# ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
# (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
# LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
# SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
#
# electric circle of the switch (without warranty
#
# [GPIO (4)]---[1k ohm]---*---[switch]----[3.3 V]
#                         |
#                          ----[10k ohm]---[0 V (ground)] 
#


#
## imports
#

import RPi.GPIO as GPIO
import time

# clean up possible old GPIO settings
GPIO.cleanup()


#
# settings
#

# define the max switch position
maxSwitchValue=2

# file with a number inside to switch between different commands by the scheduler.pl
manualSwitchFile='/tmp/manualSwitchFile_for_scheduler.txt'

# use the BCM mode for the GPIO
GPIO.setmode(GPIO.BCM)

# use the GPIO 4 with own pull down electrice resistance
GPIO.setup(4, GPIO.IN , pull_up_down=GPIO.PUD_DOWN)


#
# function
#

# write the current switch value into a file
def setSwitchValue(newValue):
	filehandle=open(manualSwitchFile,'w')
	filehandle.write(newValue)
	filehandle.close()
	print ("current switch value = ",newValue)


#
# program
#

# if the switch is pressed the value is 1, otherwice 0
oldSwitchPress=0

# counters, every time the switch is pressed, the counters should be increase up to the value of maxSwitchValue
curSwitchValue=1
	
# write the default value "1"
setSwitchValue("1")

# run in loop
while True:
	# switch is pressed now
	if GPIO.input(4)==True:
		# switch was not pressed in the last loop
		if oldSwitchPress==0 :
			print ("switch pressed")
			oldSwitchPress=1
			# define new switch value
			curSwitchValue=((curSwitchValue % maxSwitchValue)+1)
			# write the new switch value in the file
			setSwitchValue(str(curSwitchValue))
		time.sleep(0.1)
	# switch is not pressed
	else:
		oldSwitchPress=0

exit()