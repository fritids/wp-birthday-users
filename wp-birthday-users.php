<?php
/*
Plugin Name: WP Birthday Users
Plugin URI: http://omar.reygaert.eu/wp/plugins/wp-birthday-users
Plugin that adds birthday posts for the users.
Version: 0.1
Author: Omar Reygaert
Author URI: http://about.me/omar.reygaert
License: GPL2
*/

### Create Text Domain For Translations
add_action('init', 'birthdayusers_textdomain');
function birthdayusers_textdomain() {
  $plugin_dir = basename(dirname(__FILE__)) . '/lang';
  load_plugin_textdomain( 'wp-birthdayusers', null, $plugin_dir );
}

### Load in the loop functions
require( 'functions.php' );

### Function: Birthday Users Manager Menu
add_action('admin_menu', 'birthday_users_page');
function birthday_users_page() {
  if (function_exists('add_submenu_page')) {
    add_submenu_page("users.php", __('Users Birthdays', 'wp-birthdayusers'), __('Users Birthdays', 'wp-birthdayusers'), 8, plugin_basename(__FILE__), 'birthdayusers_init');
  }
} 

### Function: Add fields to profil
add_action( 'show_user_profile', 'list_birthdays' );
add_action( 'edit_user_profile', 'list_birthdays' );
add_action( 'personal_options_update', 'save_birthday_users_custom_fields' );
add_action( 'edit_user_profile_update', 'save_birthday_users_custom_fields' );

### Function: Birthday-Users init
function birthdayusers_init() {
  wp_enqueue_style('wp-birthdayusers-admin', plugins_url('wp-birthday-users/birthday-users-admin-css.css'), false, '0.1', 'all');
  $blogusers = get_users('orderby=ID');
  $youngest = $oldest = date('Y-m-d')-45;
  $youngest_name = $oldest_name = "";
  $upload = wp_upload_dir();
  foreach ($blogusers as $user) {
    $birthday = get_user_meta($user->ID, 'birthday_date', true);
    if ($birthday != "") {
      $date = preg_split("/\//", $birthday);
      $birthdate = ($date[2]<10?"0".$date[2]:$date[2])."-".($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]);
      $optionarray_def[(($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]) >= date('m-d')?"come":"past")][$user->ID] = array(
        'birthday_user'  => get_user_meta($user->ID, 'first_name', true)." ".get_user_meta($user->ID, 'last_name', true),
        'birthday_date'  => $birthday,
        'birthday_share' => get_user_meta($user->ID, 'birthday_share', true),
        'birthday_age'   => get_user_meta($user->ID, 'birthday_age', true),
        'birthday_sort'  => ($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]),
        'birthday_newer' => ($date[1]<10?"0".$date[1]:$date[1])
      );
      $averageage += age($birthday);
        
      if ($birthdate < $oldest) {
        $oldest = $birthdate;
        $oldest_name = get_user_meta($user->ID, 'nickname', true);
      }
      if ($birthdate > $youngest) {
        $youngest = $birthdate;
        $youngest_name = get_user_meta($user->ID, 'nickname', true);
      }
    }
  }
  $usersbirthday = count($optionarray_def['come'])+count($optionarray_def['past']);
  uasort($optionarray_def['come'], 'date_sort');
  $upcoming = '';
  foreach ($optionarray_def['come'] as $user_id => $user) {
    if ($user['birthday_newer'] == $optionarray_def['come'][$key-1]['birthday_newer']) {
      $upcoming .= "<p class=\"user\"><div class=\"date\">".$user['birthday_date']."</div> (".age($user['birthday_date']).__('y', 'wp-birthdayusers').") <div class=\"username\"> - ".$user['birthday_user']."</div></p>";
    } else {
      $upcoming .= "<h4>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</h4>";
      $upcoming .= "<p class=\"user\"><div class=\"date\">".$user['birthday_date']."</div> (".age($user['birthday_date']).__('y', 'wp-birthdayusers').") <div class=\"username\"> - ".$user['birthday_user']."</div></p>";
    }
    //echo "<div class=\"user\">".$user['birthday_date']." - ".$user['birthday_user']." (".age($user['birthday_date'])."j)</div>";
  }
  usort($optionarray_def['past'], 'date_sort');
  $passed = '';
  foreach ($optionarray_def['past'] as $key => $user) {
    if ($user['birthday_newer'] == $optionarray_def['past'][$key-1]['birthday_newer']) {
      $passed .= "<p class=\"user\"><div class=\"date\">".$user['birthday_date']."</div> (".age($user['birthday_date']).__('y', 'wp-birthdayusers').") <div class=\"username\"> - ".$user['birthday_user']."</div></p>";
    } else {
      $passed .= "<h4>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</h4>";
      $passed .= "<p class=\"user\"><div class=\"date\">".$user['birthday_date']."</div> (".age($user['birthday_date']).__('y', 'wp-birthdayusers').") <div class=\"username\"> - ".$user['birthday_user']."</div></p>";
    }
    //echo "<div class=\"user\">".$user['birthday_date']." - ".$user['birthday_user']." (".age($user['birthday_date'])."j)</div>";
  }
  if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; }
?>
	<div class="wrap">
		<div id="icon-wp-birthday-users" class="icon32"><br /></div>
		<h2><?php _e('Birthdays', 'wp-birthdayusers'); ?></h2>
    <ul>
      <li><em><?php printf(__('%1$s</em> of the %2$s registered user filled in there birthday.'), $usersbirthday, count($blogusers)); ?></li>
      <li><strong><?php _e('Average age', 'wp-birthdayusers'); ?>:</strong> <em><?php echo round($averageage/$usersbirthday, 1) ?></em></li>
      <li><strong><?php _e('Oldest user', 'wp-birthdayusers'); ?>:</strong> <em><?php echo $oldest_name ?></em></li>
      <li><strong><?php _e('Youngest user', 'wp-birthdayusers'); ?>:</strong> <em><?php echo $youngest_name ?></em></li>
      <li><strong><?php _e('Birthdays ICAL', 'wp-birthdayusers'); ?>:</strong> <em><a href="<?php echo $upload['baseurl']."/birthday.ics" ?>">birthday.ics</a></em></li>
    </ul>
    <div class="metabox-holder">
      <div class="postbox">
        <div class="handlediv" title="Klik om te wisselen"><br /></div><h3><span class="upcoming">&nbsp;</span><?php _e('Upcoming birthdays', 'wp-birthdayusers'); ?></h3>
        <?php echo $upcoming; ?>
      </div>
    </div>
    <div class="metabox-holder">
      <div class="postbox">
        <div class="handlediv" title="Klik om te wisselen"><br /></div><h3><span class="upcoming">&nbsp;</span><?php _e('Passed birthdays', 'wp-birthdayusers'); ?></h3>
        <?php echo $passed; ?>
      </div>
    </div>
<?php

}

### Function: Save edits of user
function save_birthday_users_custom_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) )
    return FALSE;
    
  $changes = get_user_meta($user_id, 'birthday_change', true);
  $changedate = update_user_meta( $user_id, 'birthday_date', $_POST['birthday_date'] );
  $changeshare = update_user_meta( $user_id, 'birthday_share', $_POST['birthday_share'] );
  $changeage = update_user_meta( $user_id, 'birthday_age', $_POST['birthday_age'] );
  
  if ($_POST['birthday_share'] == 1 && $_POST['birthday_date'] != "" && ($changedate || $changeshare || $changeage)) {
    write2file(birthday2ical($_POST['birthday_date'], $user_id, $_POST['birthday_age'], ($changes==""?0:$changes)), plugin_dir_path(__FILE__)."icals/b2i_".get_user_meta($user_id, 'first_name', true)."_".get_user_meta($user_id, 'last_name', true));
    update_user_meta( $user_id, 'birthday_change',  $changes+1);
  }
  if ($_POST['birthday_share'] == 0 && ($changedate || $changeshare || $changeage)) {
    deletefile(plugin_dir_path(__FILE__)."icals/b2i_".get_user_meta($user_id, 'first_name', true)."_".get_user_meta($user_id, 'last_name', true));
  }
  // Save in plugin-folder
  write2file(merge2ical(plugin_dir_path(__FILE__)."icals"), plugin_dir_path(__FILE__)."birthday.ics");
  // Save to uploads-folder: HOSTNAME/wp-content/uploads/birthday.ics
  $upload = wp_upload_dir();
  write2file(merge2ical(plugin_dir_path(__FILE__)."icals"), $upload['basedir']."/birthday.ics");
}

### Function: add jquery, jquery-ui and datepicker
add_action( 'init', 'wp29r01_date_picker' );
function wp29r01_date_picker() {
  wp_enqueue_script( 'jquery' );
  wp_enqueue_script( 'jquery-ui-core' );
  wp_enqueue_script( 'jquery-ui-datepicker');
}

### Function: init for the profil-page
function list_birthdays() {
	global $profileuser;
	$user_id = $profileuser->ID;
  wp_enqueue_style('wp-birthdayusers-admin', plugins_url('wp-birthday-users/css/smoothness/jquery-ui-1.8.20.custom.css'), false, '1.8.20', 'all');
  $optionarray_def = array(
    'birthday_date'  => get_user_meta($user_id, 'birthday_date', true),
    'birthday_share' => get_user_meta($user_id, 'birthday_share', true),
    'birthday_age'   => get_user_meta($user_id, 'birthday_age', true)
  );

?>
    <script type="text/javascript">
      var $j = jQuery.noConflict();
      $j(function() {
        $j("#birthday_date").datepicker({
          dateFormat : 'd/m/yy',
          changeMonth: true,
          changeYear: true,
          maxDate: "-18Y"
        });
      });
    </script>
    <h3><?php _e('Date of birth', 'wp-birthdayusers'); ?></h3>
    <table class="form-table">
      <tr>
        <th>
          <label for="address"><?php _e('Birthday', 'wp-birthdayusers'); ?></label>
        </th>
        <td>
          <input type="text" id="birthday_date" name="birthday_date" size="60" maxlength="100" value="<?php echo $optionarray_def['birthday_date']; ?>" dir="ltr" />
        </td>
      </tr>
      <tr>
        <td><?php _e('Share anniversary info', 'wp-birthdayusers'); ?></td>
        <td><input name="birthday_share" type="checkbox" id="birthday_share" value="1" <?php checked('1', $optionarray_def['birthday_share']); ?> /></td>
      </tr>
      <tr>
        <td><?php _e('Share age info', 'wp-birthdayusers'); ?></td>
        <td><input name="birthday_age" type="checkbox" id="birthday_age" value="1" <?php checked('1', $optionarray_def['birthday_age']); ?> /> <span><em><?php printf(__('Show your age: %d y, and this only if you share your anniversary info.', 'wp-birthdayusers') , age($optionarray_def['birthday_date'])); ?></em></span></td>
      </tr>
    </table>
<?php 

}

?>
