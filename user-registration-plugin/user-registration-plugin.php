<?php
/**
 * Plugin Name: User Registration Form
 * Plugin URI: #
 * Description: Creates a user registration form with email, password, first name, last name, review text area, and review rating fields.
 * Version: 1.1
 * Author: Utsav
 * Text Domain: user-registration-plugin
 * Domain Path: /languages
 * PHP version: 7.4.33
 * WP version: 6.2.2
 **/
class User_Registration_Form_Plugin {
    private $db;
    private $user_ids;

    /**
     * Initialize the plugin.
     */
    public function __construct() {

        global $wpdb;
        $this->db = $wpdb;

        add_shortcode( 'user_registration_form', array( $this, 'render_registration_form' ) );
        add_action( 'init', array( $this, 'process_registration_form' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'user_registration_success', array($this, 'send_registration_email'), 10, 1 );
        add_shortcode( 'user_display_form', array( $this, 'render_display_form' ) );
        
    }

    public function enqueue_styles(){
        wp_enqueue_style( 'user_registration_form_styles', plugin_dir_url( __FILE__ ) . 'css/style.css' );
    }
    /**
     * Render the registration form shortcode.
     *
     * @return string The HTML markup of the registration form.
     */
    public function render_registration_form() {
        ob_start();
        ?>
        <form method="post" action="" class = "form_handler">
            <label for="user_email">Email:</label>
            <input type="email" name="user_email" id="user_email" required>

            <label for="user_password">Password:</label>
            <input type="password" name="user_password" id="user_password" required>

            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" required>

            <label for="review">What do you like about our products?</label>
            <textarea name="review" id="review"></textarea>

            <label for = "review_rating">Rate on the scale of 1 to 5 </label>
            <input type="number" name="review_rating" id="review_rating" min="0" max="5" required>

            <input type="submit" id = "submit_form_handler" value="Register">
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Process the registration form submission.
     */
    public function process_registration_form() {
        if ( isset( $_POST['user_email'] ) 
        && isset( $_POST['user_password'] )
         && isset( $_POST['first_name'] )
          && isset( $_POST['last_name'] )
           && isset( $_POST['review'] )
            && isset( $_POST['review_rating'])
             ){

            $user_email = sanitize_email( $_POST['user_email'] );
            $user_password = sanitize_text_field( $_POST['user_password'] );
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );
            $review = sanitize_textarea_field( $_POST['review'] );
            $review_rating = intval($_POST['review_rating']);

            // Extract username from email
            $username = $this->extract_username_from_email( $user_email );

            // Perform further validation or save the user data to the database.
                
            // Example: Creating a new user., 
            $user_id = wp_create_user( $username, $user_password, $user_email );

            // Save additional user meta data.
            update_user_meta( $user_id, 'first_name', $first_name );
            update_user_meta( $user_id, 'last_name', $last_name );
            update_user_meta( $user_id, 'review', $review );
            update_user_meta( $user_id, 'review_rating', $review_rating );

            // Trigger custom action hook for successful registration
            do_action( 'user_registration_success', $user_id );

            // Redirect or display a success message.
            wp_redirect( home_url( '/success/' ) );
            exit;
        }
    }
    //function to extract username from email
    private function extract_username_from_email( $email ) {
        $username = '';
        if ( is_email( $email ) ) {
            $parts = explode( '@', $email );
            $username = sanitize_user( $parts[0], true );
        }
        return $username;
    }

    public function send_registration_email( $user_id ) {

        ob_start();

        $user = get_user_by( 'ID', $user_id );
    
        // Prepare and send the registration email
        $to = $user->user_email;
        error_log(print_r($user));
        
        $subject = 'Registration Successful';
        $message = 'Dear ' . $user->display_name . ', your registration was successful.';
        $headers = 'From: Your Website <noreply@example.com>';
    
        wp_mail( $to, $subject, $message, $headers );
    }


  
    //render display form to display cards
    public function render_display_form(){
        
        global $wpdb;
        
        //$user_ids =  $wpdb->get_results("SELECT * FROM {$wpdb->users}");
        $query = "
        SELECT u.user_email, CONCAT(um.meta_value, ' ', um2.meta_value) AS full_name, um3.meta_value AS review_rating, um4.meta_value AS review_description
        FROM {$wpdb->users} AS u
        LEFT JOIN {$wpdb->usermeta} AS um ON u.ID = um.user_id AND um.meta_key = 'first_name'
        LEFT JOIN {$wpdb->usermeta} AS um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        LEFT JOIN {$wpdb->usermeta} AS um3 ON u.ID = um3.user_id AND um3.meta_key = 'review_rating'
        LEFT JOIN {$wpdb->usermeta} AS um4 ON u.ID = um4.user_id AND um4.meta_key = 'review'
        GROUP BY u.ID
        ";
        $user_records = $wpdb->get_results($query);
        echo "<pre>";
        error_log(print_r($user_ids));
        echo "</pre>";
     
            // $user_iden = $wpdb->get_results("SELECT * FROM {$wpdb->usermeta} WHERE ID = $user_iden");
            foreach ($user_records as $record) {
                // $user_email = !empty($record->user_email) ? $record->user_email : 'N/A';
                // $full_name = !empty($record->full_name) ? $record->full_name : 'N/A';
                // $review_rating = !empty($record->review_rating) ? $record->review_rating : 'N/A';
                // $review_description = !empty($record->review_description) ? $record->review_description : 'N/A';
            
                // // Perform any operations with the variables for each row
                // // For example, you can print them or store them in an array
                // echo "User Email: " . $user_email . "<br>";
                // echo "Full Name: " . $full_name . "<br>";
                // echo "Review Rating: " . $review_rating . "<br>";
                // echo "Review Description: " . $review_description . "<br>";
                // echo "<br>";
                

               
                    ob_start();
            
                    // Include the template file
                    $file = ABSPATH . 'wp-content/plugins/user-registration-form/review-card.php';
            
                    include_once($file);
            
                    return ob_get_clean();
                

            }
    }
}
            
// Instantiate the plugin.
new User_Registration_Form_Plugin();
