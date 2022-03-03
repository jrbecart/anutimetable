<?php
require_once __DIR__.'/../vendor/autoload.php'; // SimpleXLSX php class v0.8.8
use Shuchkin\SimpleXLSX;

$term = $argv[1];

//Setup a PHP array to hold our CSV rows.
$csvData = array();
$id = 1;

try {
    if($term == "winter")
    {
      read(dirname(__FILE__)."/xlsx-winter.xlsx",$id, $csvData);
      chmod(dirname(__FILE__)."/timetableWinter.json",0777);
      $f = fopen(dirname(__FILE__)."/timetableWinter.json", "w+") or die("fopen failed");
      fwrite($f, json_encode($csvData));
      fclose($f);
    }
    else
    {
      read(dirname(__FILE__)."/xlsx-fall.xlsx",$id, $csvData);
      chmod(dirname(__FILE__)."/timetableFall.json",0777);
      $f = fopen(dirname(__FILE__)."/timetableFall.json", "w+") or die("fopen failed");
      fwrite($f, json_encode($csvData));
      fclose($f);
    }
    
    

    chmod(dirname(__FILE__)."/../js/importDate.js",0777);
    $f = fopen(dirname(__FILE__)."/../js/importDate.js", "w+") or die("fopen failed");
    fwrite($f, "var importDate = \"" . date_create('now', timezone_open('America/Toronto'))->format('Y-m-d H:i:s') . "\";");
    fclose($f);

    echo 1;  
} catch ( Exception $e ) {
    // send error message 
    
    echo $e->getMessage();
} 
return;

function read($inputFileName, &$id, &$csvData)
{
  if ($xlsx = SimpleXLSX::parse($inputFileName)) {
	  $fieldsRess = $xlsx->rows();
	
    $header_values = $rows = [];
    foreach ( $fieldsRess as $k => $r ) 
    {
      if ( $k === 0 ) {
        continue;
      }
      if ( $k === 1 ) {
        $header_values = $r;
        continue;
      }
      $nr=  array_slice($r, 0, count($header_values));
        $rows[] = array_combine( $header_values, $nr );      
    }
    // check if array keys exists
    $keys = ['Subject','Catalog','Section','Descr','Component','Pat Nbr','Sun','Mon','Tues','Wed','Thurs','Fri','Sat','Mtg Start','Mtg End','Projection','Building','Room','Capacity','Assignment','Total Enrolment','Seats Left','Room Use (%)','Total Enrolment Cap.','Open Enrolment Slots','Classroom Exception (0025)','Last','First Name','Email'];
    foreach ($keys as $key)
    {
      if (!array_key_exists($key, $rows[0])) {
          echo "Column \'$key\' does not exist!";
          exit(0);
      }
    }

    $lineCount = 0;
    $list_activity = [];
    

    foreach ($rows as $fields) {
        
        $fieldsRes = [];
        // mapping from previous import function 
        /*
        0'Subject'
        1'Catalog'
        2'Section'
        3'Descr'
        4'Component'
        5'Pat Nbr'
        6'Sun'
        7'Mon'	
        8'Tues'	
        9'Wed'
        10'Thurs'
        11'Fri'
        12'Sat'
        13'Mtg Start'
        14'Mtg End'
        15'Projection'
        16'Building'
        17'Room'
        18'Capacity'
        19'Assignment'
        20'Total Enrolment'
        21'Seats Left'
        22'Room Use (%)'	
        23'Total Enrolment Cap.'
        24'Open Enrolment Slots'
        25'Classroom Exception (0025)'
        26'Last'
        27'First Name'
        28'Email'
        */
                
        $Parent_Activity_Name = $Activity_Name  = $fields['Subject'] . 
          " " . $fields['Catalog'] . 
          " " . $fields['Section'] .
          " " . $fields['Component'] . 
          $fields['Pat Nbr'];  
        
        if ( (empty($list_activity) || empty($list_activity[$Activity_Name])) && !empty($Activity_Name))
        {
          $list_activity[$Activity_Name] = 1;
          
          // Module_Name
          $Module_name = $fields['Subject'] . " " . $fields['Catalog'];  
          $fieldsRes[0] = $Module_name;
          
          $Module_Description = $Module_name = $fields['Descr'];  
          
          $fieldsRes[1] = $Module_Description;
          $fieldsRes[2] = $Activity_Name;
          $fieldsRes[3] = $Parent_Activity_Name;
          
          $Activity_Type = $Activity_Description = $fields['Component'];  
          
          $fieldsRes[4] = $Activity_Description;
          $fieldsRes[5] = $Activity_Type;
          
          if($fields['Sun'] == 'Y')
          {
            $Day = 6;
          }
          elseif($fields['Mon'] == 'Y')
          {
            $Day = 0;
          }
          elseif($fields['Tues'] == 'Y')
          {
            $Day = 1;
          }
          elseif($fields['Wed'] == 'Y')
          {
            $Day = 2;
          }
          elseif($fields['Thurs'] == 'Y')
          {
            $Day = 3;
          }
          elseif($fields['Fri'] == 'Y')
          {
            $Day = 4;
          }
          elseif($fields['Sat'] == 'Y')
          {
            $Day = 5;
          }
          else
          {
            //$Day = '';
            continue;
          }
          $fieldsRes[6] = $Day;
          
          $StartTime = date("H:i",strtotime($fields['Mtg Start']));   
          $fieldsRes[7] = $StartTime;
                  
          $EndTime = date("H:i",strtotime($fields['Mtg End']));   
          $fieldsRes[8] = $EndTime;
          
          $fieldsRes[9] = "01/01/2019";
          $fieldsRes[10] = "1-43";        
          $fieldsRes[11] = 111111111111111111111111111111111111111111111111111111;
          
          $Location = $Activity_Description = $fields['Building'] . " " . $fields['Room'];  
          $fieldsRes[12] = $Location;
          $fieldsRes[13] = $Activity_Description;
          $fieldsRes[14] = $fields['Projection'];
          
          $Prof_Info = ($fields['Last'] ? $fields['Last'] . " " . $fields['First Name'] . " (" . $fields['Email'] . ")" : "");
          $fieldsRes[15] = $Prof_Info;
          
          // process data
          if (strlen($fieldsRes[7]) < 5)
            $fieldsRes[7] = "0$fieldsRes[7]";
          
          $st = (int) mb_substr($fieldsRes[7], 0, 2);
          $st2 = (float)mb_substr($fieldsRes[7], 0, 2);
          
          $min = (int) mb_substr($fieldsRes[7], -2);
          if ($min > 0)
            $st2 = (float)($st + ($min/60));
            
          $et = (int) mb_substr($fieldsRes[8], 0, 2);
          $et2 = (float) mb_substr($fieldsRes[8], 0, 2);
          $min = (int) mb_substr($fieldsRes[8], -2);
          if ($min > 0)
            $et2 = $et + ($min/60);
          $n = (float)($et2 - $st2);
          
          
          $resultRow = new \stdClass();
          $resultRow->id = $id;
          $resultRow->info = mb_substr($fieldsRes[2], 9); 
          # parent activity name
          $resultRow->name = trim(mb_substr($fieldsRes[3], 0, 9));
          
          $resultRow->start = $st2;
          $resultRow->dur = $n;
          # location
          $resultRow->location = $fieldsRes[12];
          # day
          $resultRow->day = $fieldsRes[6];
          
          $resultRow->weeks = "30-35,38-43";
          if ($fieldsRes[15])
            $resultRow->note = "$fieldsRes[1] &#013;\nCapacity: $fieldsRes[14]&#013;\nProf: $fieldsRes[15]&#013;&#013;\n\n" ;
          else
            $resultRow->note = "$fieldsRes[1] &#013;\nCapacity: $fieldsRes[14]&#013;&#013;\n\n" ;
          
          $csvData[] = $resultRow;
          
          $id++;
        }
    }
    
  } else {
      echo 'xlsx error: '.$xlsx->error();
  }

}