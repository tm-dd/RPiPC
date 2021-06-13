<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--

    Copyright (C) 2021, Thomas Mueller <><
    
    Redistribution and use in SOURCE and BINARY forms, with or without
    modification, are permitted provided that the following conditions are met:
    
    1. Redistributions of source code must retain the above copyright notice, this
       list of conditions and the following disclaimer.
    2. Redistributions in binary form must reproduce the above copyright notice,
       this list of conditions and the following disclaimer in the documentation
       and/or other materials provided with the distribution.
    
    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
    ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
    ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
    ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

-->

<?php

/*****************

  Error handling and variables

*****************/

        // show errors
        ini_set('display_errors',1);
        ini_set('display_startup_errors',1);
        error_reporting(E_ALL);

        // get variables
        if (isset($_POST['do'])) { $do=$_POST['do']; } else { $do=''; }
        if (isset($_POST['ID'])) { $ID=$_POST['ID']; } else { $ID=0; }
        if (isset($_POST['byear'])) { $byear=$_POST['byear']; } else { $byear=0; }
        if (isset($_POST['bmonth'])) { $bmonth=$_POST['bmonth']; } else { $bmonth=0; }
        if (isset($_POST['bday'])) { $bday=$_POST['bday']; } else { $bday=0; }
        if (isset($_POST['bhour'])) { $bhour=$_POST['bhour']; } else { $bhour=0; }
        if (isset($_POST['bminute'])) { $bminute=$_POST['bminute']; } else { $bminute=0; }
        if (isset($_POST['bsecond'])) { $bsecond=$_POST['bsecond']; } else { $bsecond=0; }
        if (isset($_POST['eyear'])) { $eyear=$_POST['eyear']; } else { $eyear=0; }
        if (isset($_POST['emonth'])) { $emonth=$_POST['emonth']; } else { $emonth=0; }
        if (isset($_POST['eday'])) { $eday=$_POST['eday']; } else { $eday=0; }
        if (isset($_POST['ehour'])) { $ehour=$_POST['ehour']; } else { $ehour=0; }
        if (isset($_POST['eminute'])) { $eminute=$_POST['eminute']; } else { $eminute=0; }
        if (isset($_POST['esecond'])) { $esecond=$_POST['esecond']; } else { $esecond=0; }
        if (isset($_POST['actionType'])) { $actionType=$_POST['actionType']; } else { $actionType=0; }
        if (isset($_POST['actionValue'])) { $actionValue=$_POST['actionValue']; } else { $actionValue=0; }
        if (isset($_POST['repeatAction'])) { $repeatAction=$_POST['repeatAction']; } else { $repeatAction=0; }
        if (isset($_POST['actionValue'])) { $repeatEvery=$_POST['repeatEvery']; } else { $repeatEvery=0; }
        if (isset($_POST['lyear'])) { $lyear=$_POST['lyear']; } else { $lyear=0; }
        if (isset($_POST['lmonth'])) { $lmonth=$_POST['lmonth']; } else { $lmonth=0; }
        if (isset($_POST['lday'])) { $lday=$_POST['lday']; } else { $lday=0; }
        if (isset($_POST['lhour'])) { $lhour=$_POST['lhour']; } else { $lhour=0; }
        if (isset($_POST['lminute'])) { $lminute=$_POST['lminute']; } else { $lminute=0; }
        if (isset($_POST['lsecond'])) { $lsecond=$_POST['lsecond']; } else { $lsecond=0; }
        
/*****************

  INCLUDE UND CO.

*****************/

        // include the config file
        include "./config.php";
        
        // include the function config
        include "./functions.php";


/*****************

  HTML START

*****************/

		echo "<html>\n";
		echo "    <head><title>Manage Actions on display '".$displayName."'</title></head>\n";
		echo "    <body>\n";
		echo "    <h1>MANAGE ACTIONS on display '".$displayName."'</h1>\n";


/*************

  READ ALL ACTIONS

**************/

        // read all defined presentations
        $csvValues=readCsvFile($csvFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);

        // read all defined actions
        $actionRules=readCsvFile($actionRulesFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);

        
/*************

  FIRST STEP FOR ACTIONS

**************/

      if ($do=="showNewAction")
      {
            echo "<h2>new actions</h2>\n";
            printActionLineAsTableToEditOrCopy('','copy',$actionRules);
      }

      if ($do=="showEditAction")
      {
            echo "<h2>edit actions</h2>\n";
            $csvLine=findLineNumberByFirstValue($csvValues,$ID);
            printActionLineAsTableToEditOrCopy($csvValues[$csvLine],'edit',$actionRules);
      }

      if ($do=="showCopyAction")
      {
            echo "<h2>copy actions</h2>\n";
            $csvLine=findLineNumberByFirstValue($csvValues,$ID);
            printActionLineAsTableToEditOrCopy($csvValues[$csvLine],'copy',$actionRules);
      }
      
      if ($do=="showDeleteAction")
      {
            echo "<h2>delete actions</h2>\n";
            $csvLine=findLineNumberByFirstValue($csvValues,$ID);
            printActionLineAsTableToDelete($csvValues[$csvLine]);
      }


/*************

  SECOND STEP FOR ACTIONS

**************/
        
      if ($do=="saveEditAction")
      {
            if (($actionType=='*** please choose ***') || ($actionType=='---'))
            {
                echo "<h2>ERROR: You forgot to setup a valid action type. Please try it again.</h2>";
            }
            else
            {
        
                $begin=getTimpstampOfDateTimeSting("$byear-$bmonth-$bday $bhour:$bminute:$bsecond");
                $end=getTimpstampOfDateTimeSting("$eyear-$emonth-$eday $ehour:$eminute:$esecond");
                $repeatEnd=getTimpstampOfDateTimeSting("$lyear-$lmonth-$lday $lhour:$lminute:$lsecond");
                if (($repeatAction=='---')||($repeatAction=='no repeat')) { $repeatAction='-'; }
                $newLine = array("$ID","$begin","$end","$actionType","$actionValue","$repeatAction","$repeatEvery","$repeatEnd");
                
                editValueInCsvFile($csvFilePath,$csvValues,'edit',$ID,$newLine,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
    
                echo "<h2>action changed</h2>\n";
    
                // read new defined actions
                $csvValues=readCsvFile($csvFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
            }
      }

      if ($do=="saveCopyAction")
      {
            if (($actionType=='*** please choose ***') || ($actionType=='---'))
            {
                echo "<h2>ERROR: You forgot to setup a valid action type. Please try it again.</h2>";
            }
            else
            {
                $newId=getNextFreeID($csvValues);
                $begin=getTimpstampOfDateTimeSting("$byear-$bmonth-$bday $bhour:$bminute:$bsecond");
                $end=getTimpstampOfDateTimeSting("$eyear-$emonth-$eday $ehour:$eminute:$esecond");
                $repeatEnd=getTimpstampOfDateTimeSting("$lyear-$lmonth-$lday $lhour:$lminute:$lsecond");
                if (($repeatAction=='---')||($repeatAction=='no repeat')) { $repeatAction='-'; }
                $newLine = array("$newId","$begin","$end","$actionType","$actionValue","$repeatAction","$repeatEvery","$repeatEnd");
                
                editValueInCsvFile($csvFilePath,$csvValues,'createNew',$newId,$newLine,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
    
                // read new defined actions
                $csvValues=readCsvFile($csvFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
    
                echo "<h2>new actions saved</h2>\n";
            }
      }
      
      if ($do=="deleteLineAction")
      {
            // create new array without line
            editValueInCsvFile($csvFilePath,$csvValues,'delete',$ID,'',$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
            
            echo "<h2>action deleted</h2>\n";

            // read new defined actions
            $csvValues=readCsvFile($csvFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
      }


/*************

  PRINT ALL ACTIONS AS TABLE

**************/

        if ($do!='') { echo "<br><hr><br>\n"; }

        echo "<h2>current planed actions</h2>\n";
    
        // sort the array (sorted by row 1)
        $csvValues=sortArrayOfCsvValues($csvValues,1);
        
        // print all actions as table 
        printActionsAsTableToEdit($csvValues,$debug);

        echo '<form action="./index.php" method="post"><input type="hidden" name="ID" value="-1">';
        echo '<input type="hidden" name="do" value="showNewAction"><input type="submit" value="create new action"></form>'."\n";


        if ($allowManageRules == 'y')
        {
            echo "<br><a href='./manage_rules.php'>manage rules (for advanced users only)</a>\n";
        }


/*****************

  HTML END

*****************/

		echo "    <br>\n";
		echo "    </body>\n";
		echo "</html>\n";

?>
