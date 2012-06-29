<?php

class BirthdayUsersWidget extends WP_Widget {
  function BirthdayUsersWidget() {
    $widget_ops = array ( 
      'classname' => 'wp-birthday',
      'description' => __( 'Display a list of birthdays', 'wp-birthday-users' )
    );
    $this->WP_Widget('BirthdayUsersWidget', __('Birthdays', 'wp-birthday-users'), $widget_ops);
  }
 
  function form($instance) {
    wp_enqueue_style('wp-birthday-users-admin', plugins_url('wp-birthday-users/birthday-users-admin-css.css'), false, '0.1', 'all');
    $instance = wp_parse_args( ( array ) $instance, array ( 
      'show_months'   => '', 
      'show_coming'   => 'on', 
      'show_past'     => '', 
      'number_coming' => 3, 
      'number_past'   => 0, 
      'show_link_bu'  => '',
      'hide_if_none'  => '',)
    ); 
    $uploaddir = wp_upload_dir();
?>
  <table>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'show_months' ); ?>"><?php _e( 'Sort by month', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_months' ); ?>" name="<?php echo $this->get_field_name( 'show_months' ); ?>" value="on"<?php checked('on', esc_attr($instance['show_months']) ); ?> /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'show_coming' ); ?>"><?php _e( 'Show upcoming birthdays', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_coming' ); ?>" name="<?php echo $this->get_field_name( 'show_coming' ); ?>" value="on"<?php checked('on', $instance['show_coming'] ); ?> /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'number_coming' ); ?>"><?php _e( 'How many upcoming birthdays', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="text" class="textinput" id="<?php echo $this->get_field_id( 'number_coming' ); ?>" name="<?php echo $this->get_field_name( 'number_coming' ); ?>" value="<?php echo esc_attr( $instance['number_coming'] ); ?>" /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'show_past' ); ?>"><?php _e( 'Show past birthdays', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_past' ); ?>" name="<?php echo $this->get_field_name( 'show_past' ); ?>" value="on"<?php checked('on', $instance['show_past'] ); ?> /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'number_past' ); ?>"><?php _e( 'How many past birthdays', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="text" class="textinput" id="<?php echo $this->get_field_id( 'number_past' ); ?>" name="<?php echo $this->get_field_name( 'number_past' ); ?>" value="<?php echo esc_attr( $instance['number_past'] ); ?>" /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'show_link_bu' ); ?>"><?php _e( 'Show ical download link', 'wp-birthday-users' ); ?>:</label><a href="<?php echo $uploaddir['baseurl']."/birthday.ics"; ?>" title="<?php _e('Download Birthday calendar', 'wp-birthday-users')?>"></a></td>
      <td class="inputtd"><input type="checkbox" id="<?php echo $this->get_field_id( 'show_link_bu' ); ?>" name="<?php echo $this->get_field_name( 'show_link_bu' ); ?>" value="on"<?php checked('on', $instance['show_link_bu'] ); ?> /></td>
    </tr>
    <tr>
      <td class="inputtitle"><label for="<?php echo $this->get_field_name( 'hide_if_none' ); ?>"><?php _e( 'Hide widget if no birthdays today', 'wp-birthday-users' ); ?>:</label></td>
      <td class="inputtd"><input type="checkbox" id="<?php echo $this->get_field_id( 'hide_if_none' ); ?>" name="<?php echo $this->get_field_name( 'hide_if_none' ); ?>" value="on"<?php checked('on', $instance['hide_if_none'] ); ?> /></td>
    </tr>
  </table>
<?php
  }
 
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['show_months']   = $new_instance['show_months'];
    $instance['show_months']   = $new_instance['show_months'];
    $instance['show_coming']   = $new_instance['show_coming'];
    $instance['show_past']     = $new_instance['show_past'];
    $instance['number_coming'] = $new_instance['number_coming'];
    $instance['number_past']   = $new_instance['number_past'];
    $instance['show_link_bu']  = $new_instance['show_link_bu'];
    $instance['hide_if_none']  = $new_instance['hide_if_none'];
    return $instance;
  }
 
  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    wp_enqueue_style('wp-birthday-users-widget', plugins_url('wp-birthday-users/birthday-users-widget-css.css'), false, '0.1', 'all');
    $usersarray = birthdayslist();
    $tocome = $usersarray['come'];
    $past = $usersarray['past'];
    uasort($tocome, 'date_sort');
    uasort($past, 'date_sort');
    if ($usersarray['info']['today'] > 0 || $instance['hide_if_none'] != "on") {
      echo "\n".$before_widget."\n  ";
      echo $before_title.__('Birthdays', 'wp-birthday-users').$after_title."\n";
      if ($instance['show_link_bu'] === "on") {
        echo "<div class=\"birthdaylink\"><a href=\"".$usersarray['info']['baseurl']."\" title=\"".__('Download Birthday calendar', 'wp-birthday-users')."\"></a></div>";
      }
      $t = 0;
      $number = ($instance['number_coming'] < count($tocome)?$instance['number_coming']:count($tocome));
      foreach ($tocome as $key => $user) {
        if (($instance['show_coming'] === "on" && $number > $t) ||  $user['birthday_sort'] == date('m-d')) {
          if ($user['birthday_sort'] == date('m-d') && ($user['birthday_newer'] != $tocome[getPrevKey($key, $tocome)]['birthday_newer'])) {
            echo "  <div class=\"section\">\n    <h4>".__('Today', 'wp-birthday-users')."</h4>\n";
            $t--;
          } else {
            if (($user['birthday_newer'] != $tocome[getPrevKey($key, $tocome)]['birthday_newer'] || $tocome[getPrevKey($key, $tocome)]['birthday_sort'] != $user['birthday_sort']) && $instance['show_months'] === "on") {
              echo "  <div class=\"section\">\n    <h4>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</h4>\n";
            } else {
              if ($t === 0) {
                echo "  <div class=\"section\">\n    <h4>".__('Upcoming birthdays', 'wp-birthday-users')."</h4>\n";
              }
            }
          }
          echo "    <div".($user['birthday_sort'] == date('m-d')?" class=\"birthday\"":"").">".$user['birthday_user'];
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            echo "<div>".age($user['birthday_date']).__('y', 'wp-birthday-users')."</div>";
          }
          echo "</div>\n";
          if ((($user['birthday_newer'] != $tocome[getNextKey($key, $tocome)]['birthday_newer'] || $tocome[getNextKey($key, $tocome)]['birthday_sort'] != date('m-d')) && $instance['show_months'] === "on") || ($t >= $number-$usersarray['info']['today']-1 || ($tocome[getNextKey($key, $tocome)]['birthday_sort'] != date('m-d') && $t < 0))) {
            echo "  </div>\n";  
          }
        } else {
          if ($t === 0 && $tocome[getPrevKey($key, $tocome)]['birthday_sort'] != date('m-d')) {
            echo "  <div class=\"section\">\n    <h4>".__('No birthdays today', 'wp-birthday-users')."</h4>\n</div>";
          }
        }
        $t++;
      }
      $t = 0;
      foreach ($past as $key => $user) {
        if ($instance['show_past'] === "on" && $instance['number_past'] > $t) {
          if (($user['birthday_newer'] != $past[getPrevKey($key, $past)]['birthday_newer'] || $past[getPrevKey($key, $past)]['birthday_sort'] != $user['birthday_sort']) && $instance['show_months'] === "on") {
            echo "  <div class=\"section\">\n    <h4>".date('M', mktime(0,0,0,$user['birthday_newer'],1))."</h4>\n";
          } else {
            if ($t === 0) {
              echo "  <div class=\"section\">\n    <h4>".__('Passed birthdays', 'wp-birthday-users')."</h4>\n";
            }
          }
          echo "    <div".($user['birthday_sort'] == date('m-d')?" class=\"birthday\"":"").">".$user['birthday_user'];
          if ($user['birthday_age']==1 || current_user_can('activate_plugins')) {
            echo "<div>".age($user['birthday_date']).__('y', 'wp-birthday-users')."</div>";
          }
          echo "</div>\n";
          if (($user['birthday_newer'] != $past[getNextKey($key, $past)]['birthday_newer'] && $instance['show_months'] === "on") || ($t === $instance['number_past']-1)) {
            echo "  </div>\n";  
          }
        }
        $t++;
      }
      echo $after_widget;
    }
  }
}

add_action( 'widgets_init', create_function('', 'return register_widget("BirthdayUsersWidget");') );
?>
