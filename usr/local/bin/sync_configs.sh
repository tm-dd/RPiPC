#!/bin/bash
#
# by Thomas Mueller (developer@mueller-dresden.de)
#
# a script to automatically download and check a CVS file from a web page
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

# settings
checkThisStringLastLine='# phpds,version='
neededVersionString='3'

# check parameters
if [ "${2}" = '' ]
then
    echo "ERROR USAGE: $0 FILE URL [LOGIN] [PASSWORD]"
    echo "EXAMPLE: ./sync_configs.sh /home/pi/test.csv https://test.example.org/pids/plan.csv 'login' 'Passw0rd'"
    exit 0
fi

# parameters
URL=$1
LOCALFILE=$2
LOGIN=$3
PASSWORD=$4
AUTH=''

# download new copy
if [ "${4}" != '' ]
then
    AUTH='--user '${LOGIN}':'${PASSWORD}
fi

curl $AUTH -o "${1}.download.tmp" "${2}"

# check the new config
if [ "${checkThisStringLastLine}" != '' ]
then
    check=`tail -n 1 "${1}.download.tmp" | grep "${checkThisStringLastLine}"`
    if [ "$check" != '' ]
    then
        version=`echo $check | awk -F 'version=' '{ print $2 }'`
        if [ "${neededVersionString}" != "${version}" ]
        then
            echo "ERROR: Wrong version number in downloaded file."
        else
            # replace the new config
            mv "${1}.download.tmp" "${1}"
        fi
    else
        echo "ERROR: String '${checkThisStringLastLine}' is missing in downloaded file."
    fi 
fi

exit 0
