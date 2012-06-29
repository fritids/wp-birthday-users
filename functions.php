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

### Function: get previous key in array
function getPrevKey($key, $hash = array()){
  $keys = array_keys($hash);
  $found_index = array_search($key, $keys);
  if ($found_index === false || $found_index === 0)
    return false;
  return $keys[$found_index-1];
}

### Function: get next key in array
function getNextKey($key, $hash = array()) {
  $keys = array_keys($hash);
  $found_index = array_search($key, $keys);
  if ($found_index === false)
    return false;
  return $keys[$found_index+1];
}

### Function: Get Permision number of a group
function bu_permLevel($permlevel='administrator') {
  $number = 8;
  if (isset($permlevel)){
    $role = get_role( $permlevel );
    for ($i=0; $i < 9; $i++) {
      if (array_key_exists("level_$i", $role->capabilities)) {
        $number = $i;
      }
    }
  }
  return $number;
}

### Function: set a value based on saved option
function getUserMetaValue($display='first_name,last_name', $user_id) {
  $display = ($display == NULL?'first_name,last_name':$display);
  $user = get_user_by('id', $user_id);
  //$user = get_user_to_edit($user_id);
  $parts = explode(",", $display);
  if (count($parts) < 2) {
    $value = $user->$parts[0];
  } else {
    $value = $user->$parts[0]." ".$user->$parts[1];
  }
  if ($value == "" || $value == " ") {
    $value = $user->data->display_name;
  }
  return $value;
}

function birthdayslist($rebuild=false) {
  $optionarray_def = array();
  $optionarray_def = get_option('birthdayusers_options');
  if (isset($rebuild) && $rebuild) {
    foreach(scandir(plugin_dir_path(__FILE__)."icals") as $item){
      if(is_file(plugin_dir_path(__FILE__)."icals/$item")){
        deletefile(plugin_dir_path(__FILE__)."icals/$item");
      } 
    }
  }
  $blogusers = get_users('orderby=ID');
  $usersarray['info']['youngest'] = $usersarray['info']['oldest'] = "";
  $youngest = $oldest = NULL;
  $upload = wp_upload_dir();
  $usersarray['info']['basedir'] = $upload['basedir']."/birthday.ics";
  $usersarray['info']['baseurl'] = $upload['baseurl']."/birthday.ics";
  $usersarray['info']['today'] = 0;
  foreach ($blogusers as $user) {
    $birthday      = get_user_meta($user->ID, 'birthday_date', true);
    $nickname      = get_user_meta($user->ID, 'nickname', true);
    $birthdayshare = get_user_meta($user->ID, 'birthday_share', true);
    $birthdayage   = get_user_meta($user->ID, 'birthday_age', true);
    $changes       = get_user_meta($user->ID, 'birthday_change', true);
    $first_name    = get_user_meta($user->ID, 'first_name', true);
    $last_name     = get_user_meta($user->ID, 'last_name', true);
    if ($birthday != "") {
      if ($birthdayshare == 1 || current_user_can('activate_plugins')) {
        $date = preg_split("/\//", $birthday);
        $birthdate = ($date[2]<10?"0".$date[2]:$date[2])."-".($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]);
        if ($oldest == NULL) {
          $oldest = $birthdate;
          $usersarray['info']['oldest'] = ($nickname!= ""?$nickname:$user->user_login);
        }
        if (isset($rebuild) && $rebuild && $birthdayshare == 1) {
          write2file(birthday2ical($birthday, $user->ID, $birthdayage, $changes, $optionarray_def['bu_display']), plugin_dir_path(__FILE__)."icals/b2i_".$user->user_login);
        }
        $usersarray[(($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]) >= date('m-d')?"come":"past")][$user->ID] = array(
          'birthday_user'  => getUserMetaValue($optionarray_def['bu_display'], $user->ID),
          'birthday_date'  => (($birthdayage==1 || current_user_can('activate_plugins'))?$birthday:"<span class=\"protected\">".$date[0]."/".$date[1]."/</span>"),
          'birthday_share' => $birthdayshare,
          'birthday_age'   => $birthdayage,
          'birthday_sort'  => ($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]),
          'birthday_newer' => ($date[1]<10?"0".$date[1]:$date[1])
        );
        if ((($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0])) === date('m-d')) {
          $usersarray['info']['today']++;
        }
        $usersarray['info']['average_age'] += age($birthday);
        if ($birthdate < $oldest) {
          $oldest = $birthdate;
          $usersarray['info']['oldest'] = ($nickname!= ""?$nickname:$user->user_login);
        }
        if ($birthdate > $youngest) {
          $youngest = $birthdate;
          $usersarray['info']['youngest'] = ($nickname!= ""?$nickname:$user->user_login);
        }
      }
    }
  }
  $usersarray['info']['total_users'] = count($blogusers);
  if (isset($rebuild) && $rebuild) {
    write2file(merge2ical(plugin_dir_path(__FILE__)."icals"), $upload['basedir']."/birthday.ics");
    $usersarray['info']['text'] .= __('Birthdays rebuild.', 'wp-birthday-users');
  }
  return $usersarray;
}

### Function: create a ical-content for one user
function birthday2ical($date, $user_id, $birthday_age, $sequence, $display) {
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
ORGANIZER;CN=".getUserMetaValue($display, $user_id).":MAILTO:".$user_info->user_email."
UID:uuid:".md5($user_id)."
CLASS:PUBLIC
CREATED:20120528T154008Z
DESCRIPTION:".__('Birthday of ', 'wp-birthday-users').getUserMetaValue($display, $user_id).($birthday_age==1?__(' born on ', 'wp-birthday-users').$date[2]:"")."
LAST-MODIFIED:".date('Ymd\THis\Z')."
X-MICROSOFT-CDO-BUSYSTATUS:FREE
X-FUNAMBOL-ALLDAY:1
LOCATION:Gent
SEQUENCE:".$sequence."
STATUS:CONFIRMED
SUMMARY:".__('Birthday ', 'wp-birthday-users').get_user_meta($user_id, 'nickname', true)."
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
