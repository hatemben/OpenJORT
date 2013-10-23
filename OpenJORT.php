<?php
/**
* OpenJORT : Opening up Tunisian's Official Gazette
* @author Hatem <hatem@php.net>
* @version 0.1 beta
* @license MIT License
*/

set_time_limit(0);
ini_set( "memory_limit","600M");
date_default_timezone_set('Africa/Tunis');
setlocale(LC_TIME, "fr_FR");

// path to announcement’s gazettes
$path = "annonces";

// initiate mysql connection
$mysqli = new mysqli("localhost", "root", "root", "openjort",'8889','/Applications/MAMP/tmp/mysql/mysql.sock');


function month_frtoen($month ){

   $months = array(  'fr_FR' => array
        (
            '1' => 'janvier',
            '2' => 'février',
            '3' => 'mars',
            '4' => 'avril',
            '5' => 'mai',
            '6' => 'juin',
            '7' => 'juillet',
            '8' => 'août',
            '9' => 'septembre',
            '10' => 'octobre',
            '11' => 'novembre',
            '12' => 'décembre',
        ),

    'en_EN' => array
        (
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ));


$key = array_search($month,$months['fr_FR']);
return $months['en_EN'][$key];
}

function strtotime_french($date){
   $date = trim($date);
   $dt = explode(' ', $date);
   $dt[0] = str_replace('er', '', $dt[0]);
   $month = month_frtoen($dt[1]);
   $newDate = $dt[0].' '.$month.' '.$dt[2];
   return $newDate ;
}



// Read directory content
$d = dir($path);
while (false !== ($entry = $d->read())) {
   // loop inside txt files
   if ($entry!='.' and $entry!='..' and ereg('txt$', $entry)){
         $GazData = array();
         // Read txt file into array
         $data = file($path.'/'.$entry);
         $newData = array();
         foreach ($data as $key => $line) {

            $line = trim($line);
            
            // Remove lines starting with page numbers
            if (!preg_match("/^Page (?<digit>\d+)$/", $line)
               // Remove extra line in footer
               and !preg_match("/^Journal Officiel de la R/si", $line)
               // Remove extra line in footer with gazette number
               and !preg_match("/^N. (?<digit>\d+)$/", $line)){

               // Alles guet, we will just remove line separators.
               $newData[] = str_replace('______________________________','',$line);
            }

            if (preg_match("/^N. (?<digit>\d+)$/", $line,$gaznumber)) {
               $GazData['number'] = $gaznumber['digit'];
            }

            if ($key<=10 and preg_match_all('/(\d{4})/', $line,$gazyear)){
               if (sizeof($gazyear[0]) > 1 ) {
                  $gazyy = explode($gazyear[0][0], $line);  
        
                  $GazData['pubdate'] = date('Y-m-d', strtotime(strtotime_french($gazyy[1])));
               } elseif ($gazyear[0][0] > 1500) {
                  //$GazData['pubdate'] = date('Y-m-d', strtotime($gazyear[0][0]));
               }
            }

            if ($key<10 and preg_match_all('/^(\d{2})/', $line,$gazed)){
               $GazData['pubyear'] = $gazed[0][0];
            }
         }
         // Gazette content ready for announcement extraction
         $gazette = implode(' ', $newData);

         // extracting basic gazette information
         
         $GazData['title'] = $newData['0'];
         $GazData['pdflink'] = str_replace('.txt', '', $entry);
         $GazData['textlink'] = $entry;


// insert Gazette data into database 
         $query = "INSERT into gazettes values (NULL,'".$GazData['title']."',
            '".$GazData['number']."',
            '".$GazData['pubdate']."',
            '".$GazData['pubyear']."',
            '".$GazData['pdflink']."',
            '".$GazData['textlink']."')";


         $mysqli->query($query);

         $currentGazetteId = $mysqli->insert_id;

         // Explode gazette content into keywords
         $keywords = preg_split('/[\s,]+/', $gazette);
         $i = 0; $append = false;
         foreach ($keywords as $key => $value) {
            // just to skip first page and table of content, we start here
            if (trim($value) == 'SOCIETES'){
               $append = true;
               // Detect an announcement number, cut here !
           } elseif(preg_match("/^[0-9]{4}[A-Z]{1}[0-9]{5}/si", $value)){ 
               $i += 1 ;
            }

            if ($append  and !preg_match("/^[0-9]{4}[A-Z]{1}[0-9]{5}/si", $value)) {

                if (trim($value) != 'SOCIETES'){
                  // add announcement text into the result array.
                  $elements[$i]['text'][] = trim($value);
               }
            } elseif ($append  and preg_match("/^[0-9]{4}[A-Z]{1}[0-9]{5}/si", $value)) {
               // Copy announcement ID separately for later use
               $elements[$i-1]['ID'] = trim($value);
            }

         }
         // The code below is just to make gazette element readable :
         $entity = array();
         foreach ($elements as $key => $value) {
            # code...
            $entity[$key]['ID'] = $value['ID'];
            $entity[$key]['text'] = implode(' ', $value['text']);

         $query = "INSERT into announcements values('".$entity[$key]['ID']."',
            '".$mysqli->real_escape_string($entity[$key]['text'])."','".$currentGazetteId."','')";
         $mysqli->query($query);

         }
         // display first gazette content
       //  print_r($entity);
        // exit;
         echo $entry." done ...\n";
   }
}
$d->close();

$mysqli->close();