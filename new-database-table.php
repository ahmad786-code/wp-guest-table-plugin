<?php
/*
  Plugin Name: Guest Details
  Version: 1.0
  Author: Your Name
  Author URI: Your Website
*/

 

class GuestDetailsPlugin {
    function __construct() {
        global $wpdb;

        $this->charset = $wpdb->get_charset_collate();
        $this->tablename = $wpdb->prefix . "guests";

        register_activation_hook(__FILE__, array($this, 'onActivate'));
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('wp_enqueue_scripts', array($this, 'loadAssets'));
        add_filter('template_include', array($this, 'loadTemplate'), 99);

        

        
    }

    
    function onActivate() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta("CREATE TABLE $this->tablename (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            guest_name varchar(60) NOT NULL DEFAULT '',
            email varchar(100) NOT NULL DEFAULT '',
            table_option varchar(60) NOT NULL DEFAULT '',
            PRIMARY KEY  (id)
        ) $this->charset;");
    }
    function loadAssets() {
        if (is_page('pet-adoption')) {
          wp_enqueue_style('petadoptioncss', plugin_dir_url(__FILE__) . 'guest.css');
        }
      }
    
    function loadTemplate($template) {
        if (is_page('pet-adoption')) {
          return plugin_dir_path(__FILE__) . 'inc/template-guests.php';
        }
        return $template;
      }

    function addAdminMenu() {
        add_menu_page('Guest Details', 'Guest Details', 'manage_options', 'guest-details', array($this, 'renderAdminPage'));
    }
 

    function renderAdminPage() {
        if (isset($_POST['save_details'])) {
            $guest_name = sanitize_text_field($_POST['guest_name']);
            $email = sanitize_email($_POST['email']);
            $table_option = sanitize_text_field($_POST['table_option']);

            global $wpdb;
            $wpdb->insert($this->tablename, array('guest_name' => $guest_name, 'email' => $email, 'table_option' => $table_option));
            echo '<div class="updated"><p>Guest details saved successfully!</p></div>';
        }  

        if (isset($_POST['send_mail'])) {
            $email = sanitize_email($_POST['email']);
            $subject = 'Test Email';
            $message = 'This is a test email message.';

            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send the email
            $mail_result = wp_mail($email, $subject, $message, $headers);

            if ($mail_result) {
                echo '<div class="updated"><p>Test email sent successfully!</p></div>';
            } else {
                echo '<div class="error"><p>Error sending test email.</p></div>';
            }
        
    }

  
        // Retrieve guest details
        $guestDetails = $this->getGuestDetails();
        ?>
        <div class="wrap">
            <h1>Guest Details</h1>
            <form method="post" action="">
                <label for="guest_name">Guest Name:</label>
                <input type="text" name="guest_name" required>
                
                <label for="email">Email:</label>
                <input type="email" name="email" required>
                 
                <label for="table_option">Table Option:</label>
                <input type="text" name="table_option" required>
                  
                   <button type="submit" name="save_details">Save Details</button>
                   <button type="submit" name="send_mail">Send Email</button>

                 
            </form>

            <?php if (!empty($guestDetails)) : ?>
                <h2>Guest List</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Table Option</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guestDetails as $guest) : ?>
                            <tr>
                                <td><?php echo esc_html($guest['guest_name']); ?></td>
                                <td><?php echo esc_html($guest['email']); ?></td>
                                <td><?php echo esc_html($guest['table_option']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No guest details found.</p>
            <?php endif; ?>
        </div>
        <?php
    }
 

    function getGuestDetails() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM $this->tablename", ARRAY_A);
        return $results;
    }

}



$guestDetailsPlugin = new GuestDetailsPlugin();
 