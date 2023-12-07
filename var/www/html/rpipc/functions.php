<?php

/*

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

*/

// read a csv file into a php array
function readCsvFile($csvFilePath,$csvDelimiterChar,$csvEnclosureChar,$debug)
{
    // if nessesary append the current path to the full path
    if ( substr($csvFilePath,0) != '/' ) { $csvFilePath=(getcwd()).'/'.$csvFilePath; }
    
    // if the csv file not exists, stop now
    if (!(file_exists($csvFilePath))) { echo '<h1>ERROR: FILE "'.$csvFilePath.'" NOT EXISTS.<h1>'; exit(); }
        
    $foundLine=array();						// array with the values of the current line
    $valuesArray=array();					// declare the return array, to avoid error messages, if this is empty
    $lineNumber=0;                  		// the current line number in the while loop

    // open the csv file read only
    $fileHandle=fopen($csvFilePath,'r');

    while (($newLine=fgets($fileHandle)) !== FALSE)
    {
        if (substr(trim($newLine[0]),0,1) != '#')
        {

        if ($debug==1) { print "<br>DEBUG: found new value line: $newLine<br>"; }
  
        // remove left and right '"' and "\n"
        $newLine=ltrim($newLine,$csvEnclosureChar);
        $newLine=rtrim(trim($newLine),$csvEnclosureChar);

        if ($debug==1) { print "DEBUG: trimmed value line: $newLine<br>"; }
  
        // split line on '$csvEnclosureChar.$csvDelimiterChar.$csvEnclosureChar' in array fields
        $foundLineArray = explode($csvEnclosureChar.$csvDelimiterChar.$csvEnclosureChar,$newLine);
        // unescape character
        foreach ($foundLineArray as &$value) {
            $value=str_replace($csvDelimiterChar.$csvDelimiterChar,$csvDelimiterChar,$value);
        }
        // write the array in 2D array
        $valuesArray[$lineNumber]=$foundLineArray;
        $lineNumber++;
    
        if ($debug==1) {
            for ($i=0;$i<10;$i++)
            {
              if (isset($foundLineArray[$i])) { echo "value $i: $foundLineArray[$i] <br>\n"; }
            }
        }
        
          }
          else
          {
               if ($debug==1) { print "DEBUG: found new command: $newLine<br>"; }
          }
    }
    
    // close the csv file
    fclose ($fileHandle);

    return $valuesArray;
}

// return the sorted array of $csvValues
function sortArrayOfCsvValues ($csvValues,$rowToSort)
{
      $numArray=array();
      $csvNewValues=array();
      
      // build 1D array of sorted values of the row $rowToSort
      foreach ($csvValues as $dim1val)
      {
            array_push($numArray,$dim1val[$rowToSort]);
      }
      // sort and remove all duplicates
      natsort($numArray);
      $numArray=array_unique($numArray); 
      
      // for all (sorted and uniqed) elements of the 1D array push the whole lines in the array to return 
      foreach ($numArray as $sortedRowValue)
      {
            foreach ($csvValues as $dim1val)
            {
                  if($sortedRowValue == $dim1val[$rowToSort])
                  {
                        array_push($csvNewValues,$dim1val);
                  }
             }
       }

    return $csvNewValues;
}
    
// write a php array into a csv file
function writeNewCsvFile($cvsFile,$valuesArray,$lastCsvLine,$delimiterChar,$enclosureChar)
{
    // if nessesary append the current path to the full path
    if ( substr($cvsFile,0) != '/' ) { $cvsFile=(getcwd()).'/'.$cvsFile; }
    
    // open file to write
    $fileHandle=fopen($cvsFile,'w');

    // only, if the array has an element
    if (isset($valuesArray[0]))
    {    
        // write elements to the array
        foreach ($valuesArray as $newLineArray)
        {
            $valueNR=1;
            foreach ($newLineArray as $value)
            {
                if ( $value=='' ) { $value=' '; }
                // escape character
                $value=str_replace($delimiterChar,$delimiterChar.$delimiterChar,$value);
                // write value
                if ($valueNR==1) { fwrite ($fileHandle,$enclosureChar.$value.$enclosureChar); } else { fwrite ($fileHandle,$delimiterChar.$enclosureChar.$value.$enclosureChar); }
                $valueNR++;
            }
            fwrite ($fileHandle,"\n");
        }
        // write last line
        fwrite ($fileHandle,$lastCsvLine);
    }
    
    // close the file
    fclose ($fileHandle);
}

function editValueInCsvFile($csvFilePath,$csvValues,$todo,$fieldNumer,$newLine,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar)
{
    // create a new line to the csv file
    if ($todo=='createNew')
    {
        array_push($csvValues,$newLine);
    }
    
    // replace the existing line in the csv file
    if ($todo=='edit')
    {
        $csvValues=removeLineFromArray($csvValues,$fieldNumer);
        array_push($csvValues,$newLine);
    }

    // delete element from array
    if ($todo=='delete')
    {
        $csvValues=removeLineFromArray($csvValues,$fieldNumer);
    }

    writeNewCsvFile($csvFilePath,$csvValues,$lastCsvLine,$csvDelimiterChar,$csvEnclosureChar);
}

function removeLineFromArray($csvValues,$ID)
{
    $newArray=array();
    $line=0;
    
    foreach ($csvValues as $dim1val)
    {
        if ("$dim1val[0]" != "$ID")
        {
            $newArray[$line]=$dim1val;
            $line++;
        }
        
    }
    
    return $newArray;
}

function findLineNumberByFirstValue($csvValues,$ID)
{
    $lineNumber='';
    $lineCal=0;
    foreach ($csvValues as $line)
    {
        if ( "$line[0]" == "$ID" ) { $lineNumber=$lineCal; }
        $lineCal++;
    }
    return $lineNumber;
}

// get the year of a timestamp
function getYearOfTimeStamp($timeStamp)
{
    return date("Y",$timeStamp);
}

// get the month of a timestamp
function getMonthOfTimeStamp($timeStamp)
{
    return date("m",$timeStamp);
}

// get the day of a timestamp
function getDayOfTimeStamp($timeStamp)
{
    return date("d",$timeStamp);
}

// get the hour of a timestamp
function getHourOfTimeStamp($timeStamp)
{
    return date("H",$timeStamp);
}

// get the minute of a timestamp
function getMinuteOfTimeStamp($timeStamp)
{
    return date("i",$timeStamp);
}

// get the second of a timestamp
function getSecondOfTimeStamp($timeStamp)
{
    return date("s",$timeStamp);
}

// get the weekday of a timestamp
function getWeekdayOfTimeStamp($timeStamp)
{
    return date("l",$timeStamp);
}

// get a timestamp of a sting
function getTimpstampOfDateTimeSting($dateTimeSting)
{
    return strtotime($dateTimeSting);
    // return mktime($hour,$min,$sec,$mon,$day,$year);
}

// build the next free ID from $csvValues[0]
function getNextFreeID($csvValues)
{

//    // get next ID as max(ID) + 1
//    $id=1;
//    foreach ($csvValues as $line)
//    {
//        if ( $line[0] > $id ) { $id=$line[0]; }
//    }
//    return ($id+1);

    // get the next free ID
    $csvValues = sortArrayOfCsvValues ($csvValues,0);
    $id=1; $found=0;
    while ($found==0)
    {
        foreach ($csvValues as $line)
        {
            if ($line[0] == $id) { $found=1; }
        }
        if ( $found==0 ) { return $id; } else { $found=0; }
        $id++;
        if ($id > 10000) { echo "<h1>ERROR: to many IDs or problem in function getNextFreeID</h1>"; return 10001; }
    }

}

function printSelectField($name,$start,$min,$max)
{
    echo "<select size='1' name='".$name."'>\n";
    if ( $start!='' ) { echo "<option>".$start."</option>"; "<option>---</option>"; }
    for ($i=$min; $i<=$max; $i++) { echo "<option>".$i."</option>\n"; }
    echo "\n</select>\n";
}

// input field for date and time
function inputDateAndTime($timeStamp,$offsetName)
{
    if ($timeStamp == '') { $timeStamp = time(); }
    $year=getYearOfTimeStamp($timeStamp); $month=getMonthOfTimeStamp($timeStamp); $day=getDayOfTimeStamp($timeStamp);
    $hour=getHourOfTimeStamp($timeStamp); $minute=getMinuteOfTimeStamp($timeStamp); $second=getSecondOfTimeStamp($timeStamp);

    echo "date: \n"; printSelectField($offsetName."year",$year,($year-1),($year+2));
    echo " - \n"; printSelectField($offsetName."month",$month,1,12);
    echo " - \n"; printSelectField($offsetName."day",$day,1,31);
    echo "<br>time: \n"; printSelectField($offsetName."hour",$hour,0,23);
    echo " : \n"; printSelectField($offsetName."minute",$minute,0,59);
    echo " : \n"; printSelectField($offsetName."second",$second,0,59);
}

// show date and time
function showDateAndTime($timeStamp)
{
    if ($timeStamp == '') { $timeStamp = time(); }
    $year=getYearOfTimeStamp($timeStamp); $month=getMonthOfTimeStamp($timeStamp); $day=getDayOfTimeStamp($timeStamp);
    $hour=getHourOfTimeStamp($timeStamp); $minute=getMinuteOfTimeStamp($timeStamp); $second=getSecondOfTimeStamp($timeStamp);
    $weekday=getWeekdayOfTimeStamp($timeStamp);

    echo "$weekday, $year-$month-$day $hour:$minute:$second\n";
}

// get a list of action types
function chooseActionType($currentAction,$actionRules)
{
    echo "<select size='1' name='actionType'>\n";
    if ( $currentAction!='' ) { echo "<option>".$currentAction."</option>\n"; echo "<option>---</option>\n"; }
    foreach ($actionRules as $dim1val)
    {
      echo "<option>".$dim1val[0]."</option>";
    }
    echo "\n</select>\n";
}

// get a list of values
function chooseVaulue($currentValue,$arrayOfValues,$valueName)
{
    echo "<select size='1' name='$valueName'>\n";
    if ( $currentValue!='' ) { echo "<option>".$currentValue."</option>\n"; echo "<option>---</option>\n"; }
    foreach ($arrayOfValues as $value)
    {
      echo "<option>".$value."</option>\n";
    }
    echo "\n</select>";
}

// get actions als table
function printActionsAsTableToEdit($csvValues,$debug)
{
    
  global $TEXT,$LANG;
  
  /* Tabelle erzeugen */
  echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
  
  /* Ausgabe Ueberschriften */
  echo "<tr>\n";
  if ($debug==1) { echo "<th>DEBUG: ID</th>\n"; }
  echo "<th>start</th>\n";
  echo "<th>end</th>\n";
  echo "<th>action type</th>\n";
  echo "<th>action parameter</th>\n";
  echo "<th>repeat action</th>\n";
  echo "<th>repeat every (days,...)</th>\n";
  echo "<th>last repeat</th>\n";
  echo "<th>manual switch (optional)</th>\n";
  echo "<th>&nbsp;</th>\n";
  echo "</th></tr>\n";
  
  /* Ausgabe aller Zeilen */
  foreach ($csvValues as $dim1val)
  {

    echo '<tr>'."\n";
    if ($debug==1) { echo "<td>$dim1val[0]</td>\n"; }
    echo '<td>'; showDateAndTime($dim1val[1]); echo "</td>\n";
    echo '<td>'; showDateAndTime($dim1val[2]); echo "</td>\n";
    echo '<td>'.$dim1val[3].'</td>'."\n";
    echo '<td>'.$dim1val[4].'</td>'."\n";
    if ( $dim1val[5] == '-' ) 
    { 
    	// show no repeat settings, if "repeat action" is unused (value "-")
    	echo "<td>-</td>\n<td>-</td>\n<td>-</td>\n"; 
    }
    else
    {
		echo '<td>'.$dim1val[5].'</td>'."\n";
		echo '<td>'.$dim1val[6].'</td>'."\n";
		echo '<td>'; showDateAndTime($dim1val[7]); echo "</td>\n";
    }
    echo '<td>'.$dim1val[8].'</td>'."\n";
    echo '<td>';
    echo '<form action="./index.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showEditAction"><input type="submit" value="edit"></form>'."\n";
    echo '<form action="./index.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showCopyAction"><input type="submit" value="copy"></form>'."\n";
    echo '<form action="./index.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showDeleteAction"><input type="submit" value="delete"></form>'."\n";
    echo '</td>'."\n";
    echo "</tr>\n";      
  }
  
  /* Ende der Ausgabe -> Tablelle wieder schliessen */
  echo "</table><br>\n\n";

}

// get actions als table
function printRulesAsTableToEdit($csvValues)
{
    
  global $TEXT,$LANG;
  
  /* Tabelle erzeugen */
  echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
  
  /* Ausgabe Ueberschriften */
  echo "<tr>\n";
  echo "<th>action type</th>\n";
  echo "<th>command</th>\n";
  echo "<th>option prefix</th>\n";
  echo "<th>option suffix</th>\n";
  echo "<th>kill command</th>\n";
  echo "<th>&nbsp;</th>\n";
  echo "</th></tr>\n";
  
  /* Ausgabe aller Zeilen */
  foreach ($csvValues as $dim1val)
  {

    echo '<tr>'."\n";
    echo '<td>'.$dim1val[0].'</td>'."\n";
    echo '<td>'.$dim1val[1].'</td>'."\n";
    echo '<td>'.$dim1val[2].'</td>'."\n";
    echo '<td>'.$dim1val[3].'</td>'."\n";
    echo '<td>'.$dim1val[4].'</td>'."\n";
    echo '<td>';
    echo '<form action="./manage_rules.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showEditRule"><input type="submit" value="edit"></form>'."\n";
    echo '<form action="./manage_rules.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showCopyRule"><input type="submit" value="copy"></form>'."\n";
    echo '<form action="./manage_rules.php" method="post"><input type="hidden" name="ID" value="'.$dim1val[0].'">';
    echo '<input type="hidden" name="do" value="showDeleteRule"><input type="submit" value="delete"></form>'."\n";
    echo '</td>'."\n";
    echo "</tr>\n";      
  }
  
  /* Ende der Ausgabe -> Tablelle wieder schliessen */
  echo "</table><br>\n\n";

}

// get action als table to edit
function printActionLineAsTableToEditOrCopy($csvLine,$editOrCopy,$actionRules)
{

    // for new actions, set the default values
    if ($csvLine=='') { $csvLine=array('-1',time(),(time()+300),'*** please choose ***','','no repeat','-',(time()+300),'-'); } 
  
    // start the table
    echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
    echo '<form action="./index.php" method="post">'."\n";
    
    if ($editOrCopy=='edit') { echo '<input type="hidden" name="do" value="saveEditAction">'."\n"; }
    if ($editOrCopy=='copy') { echo '<input type="hidden" name="do" value="saveCopyAction">'."\n"; }
    
    echo '<input type="hidden" name="ID" value="'.$csvLine[0].'">'."\n";;
    
    // set headlines of the table
    echo "<tr>\n";
    echo "<th>start</th>\n";
    echo "<th>end</th>\n";
    echo "<th>action type</th>\n";
    echo "<th>action parameter (only one string)</th>\n";
    echo "<th>repeat action</th>\n";
    echo "<th>repeat every (days,...)</th>\n";
    echo "<th>last repeat</th>\n";
    echo "<th>manual switch (optional)</th>\n";
    echo "<th>&nbsp;</th>\n";
    echo "</tr>\n";

    $repeatValues=array('hourly','daily','weekly','monthly');

    // values of the line
    echo '<tr>'."\n";
    echo '<td align="center">'; inputDateAndTime($csvLine[1],'b'); echo "</td>\n";
    echo '<td align="center">'; inputDateAndTime($csvLine[2],'e'); echo "</td>\n";
    echo '<td align="center">'; chooseActionType($csvLine[3],$actionRules);'</td>'."\n";
    echo '<td align="center"><input type="text" size="40" name="actionValue" value="'.$csvLine[4].'"></td>'."\n";
    echo '<td align="center">'; chooseVaulue($csvLine[5],$repeatValues,'repeatAction');'</td>'."\n";
    echo '<td align="center">'; printSelectField("repeatEvery",$csvLine[6],1,31); '</td>'."\n";
    echo '<td align="center">'; inputDateAndTime($csvLine[7],'l'); echo "</td>\n";
    echo '<td align="center">'; printSelectField("switchPosition",$csvLine[8],1,10); '</td>'."\n";

    if ($editOrCopy=='edit') { echo '<td align="center"><input type="submit" value="EDIT ACTION"></td>'."\n"; }
    if ($editOrCopy=='copy') { echo '<td align="center"><input type="submit" value="CREATE ACTION"></td>'."\n"; }
    
    echo "</tr>\n";
    echo "</form>\n";
    
    echo "</table><br>\n\n";

}

// get rules als table to edit
function printRuleLineAsTableToEditOrCopy($csvLine,$editOrCopy,$actionRules)
{

    // for new actions, only
    if ($csvLine=='') { $csvLine=array('','','','',''); } 
  
    // start the table
    echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
    echo '<form action="./manage_rules.php" method="post">'."\n";
    
    if ($editOrCopy=='edit') { echo '<input type="hidden" name="do" value="saveEditRule">'."\n"; }
    if ($editOrCopy=='copy') { echo '<input type="hidden" name="do" value="saveCopyRule">'."\n"; }
    
    echo '<input type="hidden" name="ID" value="'.$csvLine[0].'">'."\n";;
    
    // set headlines of the table
    echo "<tr>\n";
    echo "<th>action type</th>\n";
    echo "<th>command</th>\n";
    echo "<th>option prefix</th>\n";
    echo "<th>option suffix</th>\n";
    echo "<th>kill command</th>\n";
    echo "<th>&nbsp;</th>\n";
    echo "</tr>\n";
    
    // values of the line
    echo '<tr>'."\n";
    echo '<td><input type="text" name="actionType" value="'.$csvLine[0].'"></td>'."\n";
    echo '<td><input type="text" name="command" value="'.$csvLine[1].'"></td>'."\n";
    echo '<td><input type="text" name="optionPrefix" value="'.$csvLine[2].'"></td>'."\n";
    echo '<td><input type="text" name="optionSuffix" value="'.$csvLine[3].'"></td>'."\n";
    echo '<td><input type="text" name="killCommand" value="'.$csvLine[4].'"></td>'."\n";
    
    if ($editOrCopy=='edit') { echo '<td><input type="submit" value="EDIT RULE"></td>'."\n"; }
    if ($editOrCopy=='copy') { echo '<td><input type="submit" value="CREATE RULE"></td>'."\n"; }
    
    echo "</tr>\n";
    echo "</form>\n";
    
    echo "</table><br>\n\n";

}

// get action als table to delete
function printActionLineAsTableToDelete($csvLine)
{
  
    // create table
    echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
    echo '<form action="./index.php" method="post">'."\n";
    echo '<input type="hidden" name="do" value="deleteLineAction">'."\n";
    echo '<input type="hidden" name="ID" value="'.$csvLine[0].'">'."\n";;
    
    // headlines of the table
    echo "<tr>\n";
    echo "<th>start</th>\n";
    echo "<th>end</th>\n";
    echo "<th>action type</th>\n";
    echo "<th>action parameters</th>\n";
    echo "<th>repeat action</th>\n";
    echo "<th>repeat every (days,...)</th>\n";
    echo "<th>last repeat</th>\n";
    echo "<th>manual switch (optional)</th>\n";
    echo "<th>&nbsp;</th>\n";
    echo "</tr>\n";
    
    // values of the line
    echo '<tr>'."\n";
    echo '<td>'; showDateAndTime($csvLine[1]); echo "</td>\n";
    echo '<td>'; showDateAndTime($csvLine[2]); echo "</td>\n";
    echo '<td>'.$csvLine[3]."</td>\n";
    echo '<td>'.$csvLine[4]."</td>\n";
    if ( $csvLine[5] == '-' ) 
    { 
    	// show no repeat settings, if "repeat action" is unused (value "-")
    	echo "<td>-</td>\n<td>-</td>\n<td>-</td>\n"; 
    }
    else
    {
		echo '<td>'.$csvLine[5].'</td>'."\n";
		echo '<td>'.$csvLine[6].'</td>'."\n";
		echo '<td>'; showDateAndTime($csvLine[7]); echo "</td>\n";
    }
    echo '<td>'.$csvLine[8]."</td>\n";
    echo '<td><input type="submit" value="DELETE NOW"></td>'."\n";
    
    echo "</tr>\n";
    echo "</form>\n";
    
    echo "</table><br>\n\n";

}

// get rule als table to delete
function printRuleLineAsTableToDelete($csvLine)
{
  
    // create table
    echo "\n<table align=\"center\" border=1 width=\"100%\" cellpadding=\"5\">\n";
    echo '<form action="./manage_rules.php" method="post">'."\n";
    echo '<input type="hidden" name="do" value="deleteLineRule">'."\n";
    echo '<input type="hidden" name="ID" value="'.$csvLine[0].'">'."\n";;
    
    // headlines of the table
    echo "<tr>\n";
    echo "<th>action type</th>\n";
    echo "<th>command</th>\n";
    echo "<th>option prefix</th>\n";
    echo "<th>option suffix</th>\n";
    echo "<th>kill command</th>\n";
    echo "<th>&nbsp;</th>\n";
    echo "</tr>\n";
    
    // values of the line
    echo '<tr>'."\n";
    echo '<td>'.$csvLine[0]."</td>\n";
    echo '<td>'.$csvLine[1]."</td>\n";
    echo '<td>'.$csvLine[2]."</td>\n";
    echo '<td>'.$csvLine[3]."</td>\n";
    echo '<td>'.$csvLine[4]."</td>\n";
    echo '<td><input type="submit" value="DELETE NOW"></td>'."\n";
    
    echo "</tr>\n";
    echo "</form>\n";
    
    echo "</table><br>\n\n";

}

?>
