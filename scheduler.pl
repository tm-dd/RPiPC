#!/usr/bin/perl
#
# by Thomas Mueller (thomas@mueller-dresden.de)
#
# A scheduler script, which start and stop programs, based on the timestamps from a CSV file.
# 
# Copyright (C) 2020, Thomas Mueller
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
# for more debugging
#
# use Data::Dumper;
# qw (Dumper);
# print Dumper $referenceOfArray; 


#
# settings
#

my $sleepTimeBeforeRetry=5;                                 # seconds to sleep for the next loop
my $csvFileToRead='./phpscripts/plan.csv';                  # the csv file actions, parameters, start and stop times
my $settingsFileToRead='./phpscripts/action-settings.csv';  # a csv file with actions settings
my $killWith=3;                                             # 1 by PID; 2 by command ; 3 by PID and command
my $DEBUG=1;                                                # define the debug output level (0, 1 or 2)
my %actionProgramMap;
my %programOptionPrefix;
my %programOptionSuffix;
my %programKillCommand;


#
# central variables for all functions and main
#

# 2d array of any lines and fields
my @csvArrayOfAllFields;

# 2d arrays of the currently activ and last active lines of CSV
my @curActiveCsvLines;
my @lastActiveCsvLines;

# 1d arrays with the indexes to the 2d arrays to @curActiveCsvLines and @lastActiveCsvLines
my @curIndexesCsvFile;
my @curActionList;
my @lastActionList;

# 1d arrays of actions to start, stop and check
my @actionsToStart;
my @actionsToStop;
my @actionsToCheck;
my @actionsToCheckProcesses;

# 1d array with pids of all running actions
my @pidsOfActions;


#
# functions
#

# read CSV file and put all valid values to @csvArrayOfAllFields
sub readCsvFileToArray
{
    $fileName = shift;
    
    my $line; my $firstChar; my @csvArray;
    open (fileHandler,'<'.$fileName) or die("Error: Could not read file '$fileName'. $!");
    while (<fileHandler>)
    {
        chomp($line=$_);
        $firstChar=substr($line,0,1);
        # ignore all lines, started with '#'
        if ($firstChar ne '#')
        {
            if ($DEBUG>=3) { print '  DEBUG: fullline CSV file: '.$line."\n"; }
            # remove first and last '"' in any line
            $line=substr($line,1);
            $line=substr($line,0,-1);
            my @csvLine = split '";"' , $line;
            
            # set the line to the array in the position of the value of the first row
            $lineNumber=$csvLine[0];
            $csvArrayOfAllFields[$lineNumber]=\@csvLine;
            
            # remember the number of the action
            push (@curIndexesCsvFile,$lineNumber);
        }
    }
    close fileHandler;
}

# read a CSV file for the settings for actions
sub readActionSettings
{
    $fileName = shift;
    
    my $line; my $firstChar; my @csvArray; my $actionName;
    open (fileHandler,'<'.$fileName) or die("Error: Could not read file '$fileName'. $!");
    while (<fileHandler>)
    {
        chomp($line=$_);
        $firstChar=substr($line,0,1);
        # ignore all lines, started with '#'
        if ($firstChar ne '#')
        {
            if ($DEBUG>=3) { print '  DEBUG: settings CSV file: '.$line."\n"; }
            # remove first and last '"' in any line
            $line=substr($line,1);
            $line=substr($line,0,-1);
            my @csvLine = split '";"' , $line;
            
            # set the values from the line to the hashes
            $actionName=$csvLine[0];
            $actionProgramMap{$actionName}=$csvLine[1];
            $programOptionPrefix{$actionName}=$csvLine[2];
            $programOptionSuffix{$actionName}=$csvLine[3];
            $programKillCommand{$actionName}=$csvLine[4];
        }
    }
    close fileHandler;
}

# print a 1D array
sub print1dArray
{
    $fieldDelimiters = shift;
    $referenceOfArray = shift;
    foreach $line (@{$referenceOfArray})
    {
            print $line.$fieldDelimiters;
    }
    print "\n";
}

# print a 2D array
sub print2dArray
{
    $fieldDelimiters = shift;
    $referenceOfArray = shift;
    $printEmptyLines = shift;  # 0 == no ; 1 == yes
    
    foreach $line (@{$referenceOfArray})
    {
        my $lineNotEmpty=$printEmptyLines;
        foreach $element (@$line)
        {
            if ( $element ne '' ) { print $element.$fieldDelimiters; $lineNotEmpty=1; }
        }
        if ( $lineNotEmpty == 1 ) { print "\n"; }
    }
}

# save the currently active lines from @csvArrayOfAllFields in @curActiveCsvLines
sub getcurActiveCsvLines
{
    my $curTimeStamp = time();
    if ($DEBUG>=1) { print "current TimeStamp: $curTimeStamp\n"; }
    foreach $id (@curIndexesCsvFile)
    {
        my $startTimestamp="$csvArrayOfAllFields[$id][1]\n";
        my $endTimestamp="$csvArrayOfAllFields[$id][2]\n";
        if (($curTimeStamp>=$startTimestamp)&&($curTimeStamp<=$endTimestamp))
        {
            if ($DEBUG>=3) { print '  DEBUG, Found valid line: '; print1dArray(' | ',$csvArrayOfAllFields[$id]); }
            $curActiveCsvLines[$id]=$csvArrayOfAllFields[$id];
            push (@curActionList,$id);
        }
    } 
}

# find differences between old and new CSV values
sub diffCurToLastActiveCsvLines
{
    
    my $found=0;

    # find actions to stop (all numbers in @lastActionList who are not in @curActionList)
    foreach $lastNumber (@lastActionList)
    {
        $found=0;
        foreach $curNumber (@curActionList)
        {
            if ( $lastNumber eq $curNumber )
            {
                $found=1;
                if ($DEBUG>=3) { print "  DEBUG: Found same number $lastNumber in \@lastActionList and \@curActionList.\n"; }
            }
        }
        if ( $found == 0 )
        {
            if ($DEBUG>=2) { print " DEBUG: Missing number $lastNumber in \@curActionList. This action should to STOP, later.\n"; }
            push (@actionsToStop,$lastNumber);
        }
    }
    
    # find actions to start (all numbers in @curActionList who are not in @lastActionList)
    foreach $curNumber (@curActionList)
    {
        $found=0;
        foreach $lastNumber (@lastActionList)
        {
            if ( $curNumber eq $lastNumber )
            {
                $found=1;
                if ($DEBUG>=3) { print "  DEBUG: Found same number $curNumber in \@curActionList and \@lastActionList.\n"; }
            }
        }
        if ( $found == 0 )
        {
            if ($DEBUG>=2) { print " DEBUG: Missing number $curNumber in \@lastActionList. This action should to START, later.\n"; }
            push (@actionsToStart,$curNumber);
        }
    }

    # find possible running actions (who are in @curActionList and in @lastActionList) and put it in @actionsToStop and @actionsToStart
    foreach $lastNumber (@lastActionList)
    {
        $found=0;
        foreach $curNumber (@curActionList)
        {
            if ( $lastNumber eq $curNumber )
            {
                $found=1;
            }
        }
        if ( $found == 1 )
        {
            if ($DEBUG>=2) { print " DEBUG: Found number $lastNumber in \@curActionList. This action should to CHECK for different settings, later.\n"; }
            push (@actionsToCheck,$lastNumber);
        }
    }  
    
}

# find the actions to start and stop
sub diffCurToLastRunningActions
{
    foreach $index (@actionsToCheck)
    {
        my $changed=0;

        # check if action or option changed
        for (my $j=3; $j<5; $j++)
        {
            if ( "$curActiveCsvLines[$index][$j]" ne "$lastActiveCsvLines[$index][$j]" )
            {
                $changed=1;
                if ($DEBUG>=2) { print "  DEBUG: Value $curActiveCsvLines[$index][$j] differ to $lastActiveCsvLines[$index][$j] .\n"; }
            }
            else
            {
                if ($DEBUG>=3) { print "  DEBUG: Value $curActiveCsvLines[$index][$j] is equal to $lastActiveCsvLines[$index][$j] .\n"; }
            }
        }
        
        if ( $changed==1 )
        {
            if ($DEBUG>=2) { print " DEBUG: Action with index $index in \@curActionList was CHANGED and need to RESTART.\n"; }
            push (@actionsToStop,$index);
            push (@actionsToStart,$index);
        }
        
        else
        {
            if ($DEBUG>=2) { print " DEBUG: Action with index $index in \@curActionList was NOT CHANGED.\n"; }
            push (@actionsToCheckProcesses,$index);
        }
    }
}

# an option function to check if the current actions still running
sub checkActions
{
    foreach $index (@actionsToCheckProcesses)
    {
        my $processID=$pidsOfActions[$index];
        my $existsProcess = kill (0,$processID);
        
        if ($existsProcess)
        {
            if ($DEBUG>=1) { if ($existsProcess) { print "The action $index (checked pid: $processID) should running.\n"; } }
        }
        else
        {
            if ($DEBUG>=1) { if ($existsProcess) { print "The action $index (checked pid: $processID) is NOT running. Put the action in the array of actions to start.\n"; } }
            push (@actionsToStart,$index);
        }
    }
}

# start new actions and save the PIDs of the actions
sub startAction
{
    foreach $index (@actionsToStart)
    {
        my $actionName=$curActiveCsvLines[$index][3];
        my $programToStart=$actionProgramMap{$actionName};
        my $defaultPrefixProgram=$programOptionPrefix{$actionName};
        my $userOptionsProgram=$curActiveCsvLines[$index][4];
        my $defaultSuffixProgram=$programOptionSuffix{$actionName};
        
        if ($DEBUG>=1) { print "START: $programToStart $defaultPrefixProgram $userOptionsProgram $defaultSuffixProgram\n"; }
    
        # return >=0 if successful or undef if not successful
        my $pid = fork();
        die "ERROR: CAN'T FORK. $!" unless defined $pid;
        
        if ($pid == 0)
        {
            # with exec (instead of system) to parent process can kill the child process 2
            if ( "$userOptionsProgram" eq " " ) { exec "$programToStart $defaultPrefixProgram $defaultSuffixProgram; exit"; }
            else { exec "$programToStart $defaultPrefixProgram '$userOptionsProgram' $defaultSuffixProgram; exit"; }
        }
        else
        {
            # run this if this is the parent process
            $pidsOfActions[$index]=$pid;
            if ($DEBUG>=1) { print "DEBUG: The new actions was started with the PID $pid in the background.\n"; }
        }
    }
}

# stop old actions with the PIDs of the actions
sub stopAction
{
    foreach $index (@actionsToStop)
    {
        my $commandLine="$lastActiveCsvLines[$index][3] OPT: $lastActiveCsvLines[$index][4]";
        if ($DEBUG>=2) { print "STOP: PRG: $commandLine \n"; }
        
        if (($killWith == 1) || ($killWith == 3))
        {
            
            # return >=0 if successful or undef if not successful and 0 in the client process
            my $pid = fork();
            die "ERROR: CAN'T FORK. $!" unless defined $pid;
            
            if ($pid == 0)
            {
                if ($DEBUG>=2) { print "DEBUG: KILL PID: $pidsOfActions[$index]\n"; }
                exec "kill $pidsOfActions[$index]; sleep 10; kill -9 $pidsOfActions[$index]";
                exit;
            }
        }
        
        if (($killWith == 2) || ($killWith == 3))
        {
            
            # return >=0 if successful or undef if not successful and 0 in the client process
            my $pid = fork();
            die "ERROR: CAN'T FORK. $!" unless defined $pid;
            
            if ($pid == 0)
            {
                my $actionName=$lastActiveCsvLines[$index][3];
                my $killCommand=$programKillCommand{$actionName};
                if ($DEBUG>=2) { print "DEBUG: KILL WITH COMMAND: $killCommand\n"; }
                exec "$killCommand";
                exit;
            }
        }
    }
}


#
# main loop
#

$SIG{CHLD}="IGNORE";                                    # do not keep zombie processes, if started programs are crashed

my $break=0;
while ( $break != 1 )
{
    
    # read local (copy of the) CSV file with actions
    readCsvFileToArray($csvFileToRead);
    
    # get the active lines from the CSV file
    getcurActiveCsvLines();
    
    # read local (copy of the) CSV file with actions settings (or rules)
    readActionSettings($settingsFileToRead);

    if ($DEBUG>=2)
    {    
        print "\ncsvArrayOfAllFields:\n";
        print2dArray(' | ',\@csvArrayOfAllFields,0);
        
        print "\nlastActiveCsvLines:\n";
        print2dArray(' | ',\@lastActiveCsvLines,0);
    
        print "\ncurActiveCsvLines:\n";
        print2dArray(' | ',\@curActiveCsvLines,0);
        
        print "\nlist of current actions: ";
        print1dArray(' | ',\@curActionList);
    
        print "\nlist of last actions: ";
        print1dArray(' | ',\@lastActionList);
    }

    if ($DEBUG>=1) { print "\nCheck actions to stop and start, now.\n"; }
    
    diffCurToLastActiveCsvLines();

    if ($DEBUG>=2)
    {
        print "\nActions to check: ";
        print1dArray(' | ',\@actionsToCheck);
    }
    
    if ($DEBUG>=1) {  print "\nCheck actions for changes, now.\n"; }
    
    diffCurToLastRunningActions();
    
    if ($DEBUG>=2)
    {
        print "\nactions to stop: ";
        print1dArray(' | ',\@actionsToStop);
    }
    
    if ($DEBUG>=1) {  print "\nStop old actions, now.\n"; }
    
    stopAction();
    
    if ($DEBUG>=1) {  print "\nCheck active actions, now.\n"; }
    
    checkActions();
    
    if ($DEBUG>=2)
    {
        print "\nactions to start: ";
        print1dArray(' | ',\@actionsToStart);
    }
    
    if ($DEBUG>=1) {  print "\nStart new actions, now.\n"; }
    
    startAction();

    # exit 0;
    
    if ($DEBUG>=1) {  print "\n------- sleep and start new loop ------\n\n"; }
    
    sleep $sleepTimeBeforeRetry;

    # define current actions as old actions
    @lastActiveCsvLines=@curActiveCsvLines;
    @lastActionList=@curActionList;
    
    # clean Elements for the next loop
    undef @csvArrayOfAllFields;
    undef @curActiveCsvLines;
    undef @curIndexesCsvFile;
    undef @curActionList;
    undef @actionsToStop;
    undef @actionsToStart;
    undef @actionsToCheck;
    undef @actionsToCheckProcesses;
    
}

exit 0;
