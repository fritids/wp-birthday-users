<?php
/*
Plugin Name: WP Birthday Users
Plugin URI: http://omar.reygaert.eu/wp/plugins/wp-birthday-users
Plugin that adds birthday posts for the users.
Version: 0.1.5
Domain Path: /lang
Author: Omar Reygaert
Author URI: http://about.me/omar.reygaert
License: GPL2
*/

### Create Text Domain For Translations
add_action('init', 'birthdayusers_textdomain');
function birthdayusers_textdomain() {
  $plugin_dir = basename(dirname(__FILE__)) . '/lang';
  load_plugin_textdomain( 'wp-birthday-users', null, $plugin_dir );
}

### Load in the loop functions
require( 'functions.php' );

### Function: Birthday Users Manager Menu
add_action('admin_menu', 'birthday_users_page');
function birthday_users_page() {
  $optionarray_def = array();
  $optionarray_def = get_option('birthdayusers_options');
  if (function_exists('add_submenu_page')) {
    add_submenu_page("users.php", __('Users Birthdays', 'wp-birthday-users'), __('Users Birthdays', 'wp-birthday-users'), bu_permLevel($optionarray_def ['bu_view']), plugin_basename(__FILE__), 'birthdayusers_init');
  }
  add_options_page(__('Birthdays Options', 'wp-birthday-users'), __('Birthdays Options', 'wp-birthday-users'), 8, plugin_dir_path(__FILE__).'config.php', 'birthdayusers_options');
} 

### Function: Add fields to profil
add_action( 'show_user_profile', 'list_birthdays' );
add_action( 'edit_user_profile', 'list_birthdays' );
add_action( 'personal_options_update', 'save_birthday_users_custom_fields' );
add_action( 'edit_user_profile_update', 'save_birthday_users_custom_fields' );

### Function: Birthday-Users init
function birthdayusers_init() {
  $optionarray_def = array();
  $optionarray_def = get_option('birthdayusers_options');
  $pluginname = explode("/", plugin_basename(__FILE__));
  $text = "";
  wp_enqueue_style('wp-birthday-users-admin', plugins_url('wp-birthday-users/birthday-users-admin-css.css'), false, '0.1', 'all');
  if (isset($_REQUEST["rebuild"])) {
    foreach(scandir(plugin_dir_path(__FILE__)."icals") as $item){
      if(is_file(plugin_dir_path(__FILE__)."icals/$item")){
        deletefile(plugin_dir_path(__FILE__)."icals/$item");
      } 
    }
  }
  $blogusers = get_users('orderby=ID');
  $youngest = $oldest = NULL;
  $youngest_name = $oldest_name = "";
  $upload = wp_upload_dir();
  foreach ($blogusers as $user) {
    $birthday      = get_user_meta($user->ID, 'birthday_date', true);
    $nickname      = get_user_meta($user->ID, 'nickname', true);
    $birthdayshare = get_user_meta($user->ID, 'birthday_share', true);
    $birthdayage   = get_user_meta($user->ID, 'birthday_age', true);
    $changes       = get_user_meta($user->ID, 'birthday_change', true);
    $first_name    = get_user_meta($user->ID, 'first_name', true);
    $last_name     = get_user_meta($user->ID, 'last_name', true);
    if ($birthday != "") {
      $date = preg_split("/\//", $birthday);
      $birthdate = ($date[2]<10?"0".$date[2]:$date[2])."-".($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]);
      if ($oldest == NULL) {
        $oldest = $birthdate;
        $oldest_name = ($nickname!= ""?$nickname:$user->user_login);
      }
      if (isset($_REQUEST["rebuild"]) && $birthdayshare == 1) {
        write2file(birthday2ical($birthday, $user->ID, $birthdayage, $changes, $optionarray_def['bu_display']), plugin_dir_path(__FILE__)."icals/b2i_".$user->user_login);
      }
      $usersarray[(($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]) >= date('m-d')?"come":"past")][$user->ID] = array(
        'birthday_user'  => ($first_name != ""?($first_name." ".$last_name):$user->user_login),
        'birthday_date'  => (($birthdayage==1 || current_user_can('activate_plugins'))?$birthday:$date[0]."/".$date[2]."/****"),
        'birthday_share' => $birthdayshare,
        'birthday_age'   => $birthdayage,
        'birthday_sort'  => ($date[1]<10?"0".$date[1]:$date[1])."-".($date[0]<10?"0".$date[0]:$date[0]),
        'birthday_newer' => ($date[1]<10?"0".$date[1]:$date[1])
      );
      $averageage += age($birthday);
        
      if ($birthdate < $oldest) {
        $oldest = $birthdate;
        $oldest_name = ($nickname!= ""?$nickname:$user->user_login);
      }
      if ($birthdate > $youngest) {
        $youngest = $birthdate;
        $youngest_name = ($nickname!= ""?$nickname:$user->user_login);
      }
    }
  }
  $usersbirthday = count($usersarray['come'])+count($usersarray['past']);
  if ($usersarray['come'] != NULL) {
    uasort($usersarray['come'], 'date_sort');
    $upcoming = '';
    foreach ($usersarray['come'] as $user_id => $user) {
      if ($user['birthday_newer'] == $usersarray['come'][$key-1]['birthday_newer']) {
        if ($user['birthday_share'] == 1 || current_user_can('activate_plugins')) {
          $upcoming .= "<tr><td class=\"date\">".$user['birthday_date']."</td><td> - </td><td class=\"username\">".$user['birthday_user']."</td>";
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            $upcoming .= "<td>(".age($user['birthday_date']).__('y', 'wp-birthday-users').")</td>";
          }
          $upcoming .= "</tr>\n";
        }
      } else {
        if ($user['birthday_share'] == 1 || current_user_can('activate_plugins')) {
          $upcoming .= "<th>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</th>\n";
          $upcoming .= "<tr><td class=\"date\">".$user['birthday_date']."</td><td> - </td><td class=\"username\">".$user['birthday_user']."</td>";
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            $upcoming .= "<td>(".age($user['birthday_date']).__('y', 'wp-birthday-users').")</td>";
          }
          $upcoming .= "</tr>\n";
        }
      }
    }
  }
  if ($usersarray['past'] != NULL) {
    usort($usersarray['past'], 'date_sort');
    $passed = '';
    foreach ($usersarray['past'] as $key => $user) {
      if ($user['birthday_newer'] == $usersarray['past'][$key-1]['birthday_newer']) {
        if ($user['birthday_share'] == 1 || current_user_can('activate_plugins')) {
          $passed .= "<tr class=\"user\"><td class=\"date\">".$user['birthday_date']."</td><td> - </td><td class=\"username\">".$user['birthday_user']."</td>";
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            $passed .= "<td>(".age($user['birthday_date']).__('y', 'wp-birthday-users').")</td>";
          }
          $passed .= "</tr>\n";
        }
      } else {
        if ($user['birthday_share'] == 1 || current_user_can('activate_plugins')) {
          $passed .= "<th>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</th>\n";
          $passed .= "<tr class=\"user\"><td class=\"date\">".$user['birthday_date']."</td><td> - </td><td class=\"username\">".$user['birthday_user']."</td>";
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            $passed .= "<td>(".age($user['birthday_date']).__('y', 'wp-birthday-users').")</td>";
          }
          $passed .= "</tr>\n";
        }
      }
    }
  }
  if (isset($_REQUEST["rebuild"])) {
    write2file(merge2ical(plugin_dir_path(__FILE__)."icals"), $upload['basedir']."/birthday.ics");
    $text .= __('Birthdays rebuild.', 'wp-birthday-users');
  }
  if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; }
?>
  <div class="wrap">
    <div id="icon-wp-birthday-users" class="icon32"><br /></div>
    <h2><?php _e('Birthdays', 'wp-birthday-users'); echo (current_user_can('activate_plugins')?"<span class=\"rebuild\"><a href=\"?".$_SERVER['QUERY_STRING']."&amp;rebuild\">".__('rebuild birthdays', 'wp-birthday-users')."</a> - <a href=\"options-general.php?page=".$pluginname[0]."/config.php\">".__('Settings')."</a></span>":"") ?></h2>
    <ul>
      <li><em><?php printf(__('%1$s</em> of the %2$s registered user filled in there birthday.', 'wp-birthday-users'), $usersbirthday, count($blogusers)); ?></li>
      <li><strong><?php _e('Average age', 'wp-birthday-users'); ?>:</strong> <em><?php echo ($usersbirthday != 0 ?round($averageage/$usersbirthday, 1):"") ?></em></li>
      <li><strong><?php _e('Oldest user', 'wp-birthday-users'); ?>:</strong> <em><?php echo $oldest_name ?></em></li>
      <li><strong><?php _e('Youngest user', 'wp-birthday-users'); ?>:</strong> <em><?php echo $youngest_name ?></em></li>
      <li><strong><?php _e('Birthdays ICAL', 'wp-birthday-users'); ?>:</strong> <em><a href="<?php echo $upload['baseurl']."/birthday.ics" ?>">birthday.ics</a></em></li>
    </ul>
    <div class="metabox-holder">
      <div class="postbox">
        <div class="handlediv" title="Klik om te wisselen"><br /></div><h3><span class="upcoming">&nbsp;</span><?php _e('Upcoming birthdays', 'wp-birthday-users'); ?> - <small>( <?php echo count($usersarray['come'])." / ".$usersbirthday; ?> )</small></h3>
        <div class="content">
          <table>
            <?php echo $upcoming; ?></table>
          </table>
        </div>
      </div>
    </div>
    <div class="metabox-holder">
      <div class="postbox">
        <div class="handlediv" title="Klik om te wisselen"><br /></div><h3><span class="passed">&nbsp;</span><?php _e('Passed birthdays', 'wp-birthday-users'); ?> - <small>( <?php echo count($usersarray['past'])." / ".$usersbirthday; ?> )</small></h3>
        <div class="content">
          <table>
            <?php echo $passed; ?></table>
          </table>
        </div>
      </div>
    </div>
    <script>
      var $j = jQuery.noConflict();
      $j(".handlediv").click(function() {
          $j(this).parent().children(".content").toggle();
      });
    </script>
<?php

}

### Function: Save edits of user
function save_birthday_users_custom_fields( $user_id ) {
  if ( !current_user_can( 'edit_user', $user_id ) )
    return FALSE;

  $optionarray_def = array();
  $optionarray_def = get_option('birthdayusers_options');
  $changes = get_user_meta($user_id, 'birthday_change', true);
  $changedate = update_user_meta( $user_id, 'birthday_date', $_POST['birthday_date'] );
  $changeshare = update_user_meta( $user_id, 'birthday_share', $_POST['birthday_share'] );
  $changeage = update_user_meta( $user_id, 'birthday_age', $_POST['birthday_age'] );
  $user_info = get_userdata($user_id);
  
  if ($_POST['birthday_share'] == 1 && $_POST['birthday_date'] != "" && ($changedate || $changeshare || $changeage)) {
    write2file(birthday2ical($_POST['birthday_date'], $user_id, $_POST['birthday_age'], ($changes==""?0:$changes), $optionarray_def['bu_display']), plugin_dir_path(__FILE__)."icals/b2i_".$user_info->user_login);
    update_user_meta( $user_id, 'birthday_change',  $changes+1);
  }
  if ($_POST['birthday_share'] == 0 && ($changedate || $changeshare || $changeage) && file_exists(plugin_dir_path(__FILE__)."icals/b2i_".$user_info->user_login)) {
    deletefile(plugin_dir_path(__FILE__)."icals/b2i_".$user_info->user_login);
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
  wp_enqueue_style('wp-birthday-users-admin', plugins_url('wp-birthday-users/css/smoothness/jquery-ui-1.8.20.custom.css'), false, '1.8.20', 'all');
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
    <h3><?php _e('Date of birth', 'wp-birthday-users'); ?></h3>
    <table class="form-table">
      <tr>
        <th>
          <label for="address"><?php _e('Birthday', 'wp-birthday-users'); ?></label>
        </th>
        <td>
          <input type="text" id="birthday_date" name="birthday_date" size="60" maxlength="100" value="<?php echo $optionarray_def['birthday_date']; ?>" dir="ltr" />
        </td>
      </tr>
      <tr>
        <td><?php _e('Share anniversary info', 'wp-birthday-users'); ?></td>
        <td><input name="birthday_share" type="checkbox" id="birthday_share" value="1" <?php checked('1', $optionarray_def['birthday_share']); ?> /></td>
      </tr>
      <tr>
        <td><?php _e('Share age info', 'wp-birthday-users'); ?></td>
        <td><input name="birthday_age" type="checkbox" id="birthday_age" value="1" <?php checked('1', $optionarray_def['birthday_age']); ?> /> <span><em><?php printf(__('Show your age: %d y, and this only if you share your anniversary info.', 'wp-birthday-users') , age($optionarray_def['birthday_date'])); ?></em></span></td>
      </tr>
    </table>
<?php 

}

### Function: Birthday-Users options
function birthdayusers_options() {
  wp_enqueue_style('wp-birthday-users-admin', plugins_url('wp-birthday-users/birthday-users-admin-css.css'), false, '0.1', 'all');
  $text = '';
  $optionarray_def = array();
  $optionarray_def = get_option('birthdayusers_options');
  // Setup Default Options Array
  if ($optionarray_def == "") {
    $optionarray_def = array(
      'bu_display' => 'first_name,last_name',
      'bu_view'    => 'administrator',
      'bu_desc'    => ''
    );
  }
  // add_option('switch_options', $backup_options, 'wp-switch Options');

  if (isset($_POST['submit']) ) {    
    // Options Array Update
    $optionarray_def = array (
      'bu_display' => $_POST['bu_display'],
      'bu_view'    => $_POST['bu_view'],
      'bu_desc'    => $_POST['bu_desc']
    );
    $update_db_options = update_option('birthdayusers_options', $optionarray_def);
    if($update_db_options) {
      $text = '<font color="green">'.__('Options Updated', 'wp-birthday-users').'</font>';
    }
    if(empty($text)) {
      $text = '<font color="red">'.__('No Options Updated/Changed', 'wp-birthday-users').'</font>';
    }
  }
  if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; }
?>
<!-- Birthday Users Options -->
<form method="post" action="?<?php echo $_SERVER['QUERY_STRING'] ?>">
  <?php wp_nonce_field('wp-birthday-users_options'); ?>
  <div class="wrap">
    <div id="icon-wp-birthday-users" class="icon32"><br /></div>
    <h2><?php _e('Birthday Users Options', 'wp-birthday-users'); ?></h2>
    <h3><?php _e('Default Settings', 'wp-birthday-users'); ?></h3> 
    <table class="form-table">
      <tr>
        <td valign="top" width="200px"><strong><?php _e('Display name in ICAL:', 'wp-birthday-users'); ?></strong></td>
        <td>
          <select name="bu_display" id="bu_display">
            <option value="nickname"<?php selected( $optionarray_def['bu_display'], "nickname" ); ?>><?php _e('Nickname'); ?></option>
            <option value="user_login"<?php selected( $optionarray_def['bu_display'], "user_login" ); ?>><?php _e('Username'); ?></option>
            <option value="first_name"<?php selected( $optionarray_def['bu_display'], "first_name" ); ?>><?php _e('First Name'); ?></option>
            <option value="last_name"<?php selected( $optionarray_def['bu_display'], "last_name" ); ?>><?php _e('Last Name'); ?></option>
            <option value="first_name,last_name"<?php selected( $optionarray_def['bu_display'], "first_name,last_name" ); ?>><?php echo __('First Name')." ".__('Last Name'); ?></option>
            <option value="last_name,first_name"<?php selected( $optionarray_def['bu_display'], "last_name,first_name" ); ?>><?php echo __('Last Name')." ".__('First Name'); ?></option>
          </select>
          <p><?php _e('Choose which name will be used for the description in the ical-file', 'wp-birthday-users'); ?></p>
        </td>
      </tr>
      <tr>
        <td valign="top" width="200px"><strong><?php _e('Select the group that can see the birthday page overview:', 'wp-birthday-users'); ?></strong></td>
        <td>
          <select name="bu_view" id="bu_view"><?php wp_dropdown_roles( $optionarray_def['bu_view'] ); ?></select>
          <p><?php _e('This will show the link: ', 'wp-birthday-users'); ?><a href="users.php"><?php _e('Users'); ?></a> -> <a href="users.php?page=<?php echo plugin_basename(__FILE__) ?>"><?php _e('Users Birthdays', 'wp-birthday-users'); ?></a><br />
          <?php _e('Ages will only be visible if the user choosed to share it or if you are administrator', 'wp-birthday-users'); ?></p>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </div>
  </form>
<?php

}

?>
