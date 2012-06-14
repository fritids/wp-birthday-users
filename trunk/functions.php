<?php

### Function: get the age of a date seen as today
function age($date){
 $date  = preg_split("/\//", $date);
 $y = gmstrftime("%Y");
 $m = gmstrftime("%m");
 $d = gmstrftime("%d");
 $age = $y - $date[2];
 if($m <= $date[1]) {
  if($m == $date[1]) {
    if($d < $date[0]) $age = $age - 1;
  }
  else $age = $age - 1;
 }
 return($age);
}

### Function: sort an array with fields 'birthday_sort'
function date_sort($a, $b){
  return strnatcmp($a['birthday_sort'], $b['birthday_sort']);  
}

### Function: write to a file
function write2file($content, $file) {
  $fp = fopen($file, "w+");
  fwrite($fp, $content);
  fclose($fp);
}

### Function: delete a file
function deletefile($file) {
  unlink($file);
}

### Function: create a ical-content for one user
function birthday2ical($date, $user_id, $birthday_age, $sequence) {
//DTSTART;TZID=".get_option('timezone_string').":".$date[2].($date[1]<10?"0".$date[1]:$date[1]).($date[0]<10?"0".$date[0]:$date[0])."T090000Z
//DTEND;TZID=".get_option('timezone_string').":".$date[2].($date[1]<10?"0".$date[1]:$date[1]).($date[0]<10?"0".$date[0]:$date[0])."T090000Z
//CLASS:PRIVATE
  $date  = preg_split("/\//", $date);
  $user_info = get_userdata($user_id);
  $content = "BEGIN:VEVENT
CATEGORIES:Birthday
DTSTART;VALUE=DATE:".($date[2]>="1970"?$date[2]:"1970").($date[1]<10?"0".$date[1]:$date[1]).($date[0]<10?"0".$date[0]:$date[0])."
DTEND;VALUE=DATE:".($date[2]>="1970"?$date[2]:"1970").($date[1]<10?"0".$date[1]:$date[1]).($date[0]<10?"0".$date[0]:$date[0])."
RRULE:FREQ=YEARLY;INTERVAL=1;BYMONTHDAY=".($date[0]<10?"0".$date[0]:$date[0]).";BYMONTH=".($date[1]<10?"0".$date[1]:$date[1])."
DTSTAMP:".date('Ymd\THis\Z')."
ORGANIZER;CN=".get_user_meta($user_id, 'first_name', true)." ".get_user_meta($user_id, 'last_name', true).":MAILTO:".$user_info->user_email."
UID:uuid:".md5($user_id)."
CLASS:PUBLIC
CREATED:20120528T154008Z
DESCRIPTION:Verjaardag van ".get_user_meta($user_id, 'first_name', true)." ".get_user_meta($user_id, 'last_name', true).($birthday_age==1?" geboren op ".$date[2]:"")."
LAST-MODIFIED:".date('Ymd\THis\Z')."
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-FUNAMBOL-ALLDAY:1
LOCATION:Gent
SEQUENCE:".$sequence."
STATUS:CONFIRMED
SUMMARY:Verjaardag ".get_user_meta($user_id, 'nickname', true)."
TRANSP:OPAQUE
END:VEVENT";
 return $content;
}

### Function: create the ical file with all users
function merge2ical ($folder) {
  $content = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//WP-BIRTHDAY-USERS//WPBU Calendar 0.1//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:WP BIRTHDAY USERS 
X-WR-TIMEZONE:'.get_option('timezone_string').'
X-OWNER;CN="Birthday":mailto:'.get_option('admin_email').'
BEGIN:VTIMEZONE
TZID:'.get_option('timezone_string').'
X-LIC-LOCATION:'.get_option('timezone_string').'
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYMONTH=12;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYMONTH=12;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
';
  foreach(scandir($folder) as $item){
    if(is_file("$folder/$item")){
      $content .= file_get_contents("$folder/$item").'
';
    } 
  }

  $content .= "END:VCALENDAR";
  return $content;
}

?>