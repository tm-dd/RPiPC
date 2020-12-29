<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--

    Copyright (C) 2020, Thomas Mueller <><
    
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
<html>
    <head><title>Manage Rules</title></head>
    <body>

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
    if (isset($_POST['actionType'])) { $actionType=$_POST['actionType']; } else { $actionType=0; }
    if (isset($_POST['command'])) { $command=$_POST['command']; } else { $command=0; }
    if (isset($_POST['optionPrefix'])) { $optionPrefix=$_POST['optionPrefix']; } else { $optionPrefix=0; }
    if (isset($_POST['optionSuffix'])) { $optionSuffix=$_POST['optionSuffix']; } else { $optionSuffix=0; }               
    if (isset($_POST['killCommand'])) { $killCommand=$_POST['killCommand']; } else { $killCommand=0; }

        
/*****************

  INCLUDE UND CO.

*****************/

    // include the config file
    include "./config.php";
    
    // include the function config
    include "./functions.php";

 
/*****************

    Check if it's allowed to manage rules

*****************/

    if ($allowManageRules != 'y')
    {
        echo '<h1>Please set $allowManageRules to "y" in config.php, to allow changing the rules here.</h1></body></html>'."\n";
        exit ();
    }
    else
    {
        echo "<h1>MANAGE RULES (for advanced users only)</h1>\n";
    }
 
        
/*************

  READ ALL RULES

**************/

    // read all defined actions
    $actionRules=readCsvFile($actionRulesFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);


/*************

  FIRST STEP FOR RULES

**************/

      if ($do=="showNewRule")
      {
            echo "<h2>new rule</h2>\n";
            printRuleLineAsTableToEditOrCopy('','copy',$actionRules);
      }

      if ($do=="showEditRule")
      {
            echo "<h2>edit rule</h2>\n";
            $csvLine=findLineNumberByFirstValue($actionRules,$ID);
            printRuleLineAsTableToEditOrCopy($actionRules[$csvLine],'edit',$actionRules);
      }

      if ($do=="showCopyRule")
      {
            echo "<h2>copy rule</h2>\n";
            $csvLine=findLineNumberByFirstValue($actionRules,$ID);
            printRuleLineAsTableToEditOrCopy($actionRules[$csvLine],'copy',$actionRules);
      }
      
      if ($do=="showDeleteRule")
      {
            echo "<h2>delete rule</h2>\n";
            $csvLine=findLineNumberByFirstValue($actionRules,$ID);
            printRuleLineAsTableToDelete($actionRules[$csvLine]);
      }


/*************

  SECOND STEP FOR RULES

**************/
        
      if ($do=="saveEditRule")
      {            
            // error if changing the name of the action type
            if ("$actionType" != "$ID")
            {
                echo "<h2>ERROR: Do not change the name of the action type, by editing. Use copy to get a new rule.</h2>";
            }
            
            // save the edited rule
            else
            {

                $newLine = array("$actionType","$command","$optionPrefix","$optionSuffix","$killCommand");                
                editValueInCsvFile($actionRulesFilePath,$actionRules,'edit',$actionType,$newLine,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
    
                echo "<h2>rule changed</h2>\n";
    
                // read new defined rules
                $actionRules=readCsvFile($actionRulesFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
            }
      }

      if ($do=="saveCopyRule")
      {
            // error if NOT changing the name of the action type
            if ("$actionType" == "$ID")
            {
                echo "<h2>ERROR: You forgot to define a new name for the action type. Please try it again.</h2>";
            }

            // save the edited rule
            else
            {

                $newLine = array("$actionType","$command","$optionPrefix","$optionSuffix","$killCommand");                
                editValueInCsvFile($actionRulesFilePath,$actionRules,'createNew',$actionType,$newLine,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
    
                echo "<h2>new rule saved</h2>\n";
    
                // read new defined rules
                $actionRules=readCsvFile($actionRulesFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
            }
      }
      
      if ($do=="deleteLineRule")
      {
            // create new array without this line (rule)
            editValueInCsvFile($actionRulesFilePath,$actionRules,'delete',$ID,'',$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
            
            echo "<h2>rule deleted</h2>\n";

            // read new defined rules
            $actionRules=readCsvFile($actionRulesFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug);
      }


/*************

  PRINT ACTION AS RULES

**************/
    
        echo "<br><hr><br>\n";
    
        echo "<h2>current allowed actions (rules)</h2>\n";

        // sort the array (sorted by row 0)
        $actionRules=sortArrayOfCsvValues($actionRules,0);
        
        // print all actions as table 
        printRulesAsTableToEdit($actionRules);

        echo '<form action="./manage_rules.php" method="post"><input type="hidden" name="ID" value="-1">';
        echo '<input type="hidden" name="do" value="showNewRule"><input type="submit" value="create new rule"></form>'."\n";
 
?>

    <br>
    <a href="./index.php">manage actions</a>
    <br>
    </body>
</html>