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
        wp_enqueue_style( 'font-awesome-for-rating',  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
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
            <input type="number" name="review_rating" id="review_rating" min="1" max="5" required>

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
        // error_log(print_r($user));
        
        $subject = 'Registration Successful';
        $message = 'Dear ' . $user->display_name . ', your registration was successful.';
        $headers = 'From: Your Website <noreply@example.com>';
    
        wp_mail( $to, $subject, $message, $headers );
    }


  
    public function render_display_form()
{
    ob_start();
    global $wpdb;

    if (is_user_logged_in()) {
        // Get the current page number from the query parameter
        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        // Set the number of reviews to display per page
        $reviews_per_page = 5;

       
        
        // Check if the latest filter has changed
        $latest_filter_changes = isset($_GET['latest-filter-changes']) ? intval($_GET['latest-filter-changes']) : 0;
        // Check if the review filter has changed
        $review_filter_changes = isset($_GET['review-filter-changes']) ? intval($_GET['review-filter-changes']) : 0;
        // Check if the pagination has changed
        $pagination_changes = isset($_GET['pagination-changes']) ? intval($_GET['pagination-changes']) : 0;

        // Calculate the offset for the query
        $offset = ($current_page - 1) * $reviews_per_page;

              // Check if the latest filter has changed
              if ($latest_filter_changes === 1) {
                $current_page = 1;
                $offset = 0;
            }
    
            // Check if the review filter has changed
            if ($review_filter_changes === 1) {
                $current_page = 1;
                $offset = 0;
            }
    
            // Check if the pagination has changed
            if ($pagination_changes === 1) {
                // Maintain the current review and latest filter
                // No changes required for $current_page and $offset
            }else {
                // Pagination is not changed, get the current page number from the query parameter
                $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset = ($current_page - 1) * $reviews_per_page;
            }
       
        
        // Build the initial query
        $query = "
            SELECT u.user_email, CONCAT(um.meta_value, ' ', um2.meta_value) AS full_name, um3.meta_value AS review_rating, um4.meta_value AS review_description
            FROM {$wpdb->users} AS u
            LEFT JOIN {$wpdb->usermeta} AS um ON u.ID = um.user_id AND um.meta_key = 'first_name'
            LEFT JOIN {$wpdb->usermeta} AS um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
            LEFT JOIN {$wpdb->usermeta} AS um3 ON u.ID = um3.user_id AND um3.meta_key = 'review_rating'
            LEFT JOIN {$wpdb->usermeta} AS um4 ON u.ID = um4.user_id AND um4.meta_key = 'review'
            GROUP BY u.ID
        ";

        // Apply rating filter
        if (!empty($rating_filter)) {
            $query .= " HAVING um3.meta_value = {$rating_filter}";
        }

        // Apply date filter
        if ($date_filter === 'latest') {
            $query .= " ORDER BY u.user_registered DESC";
        }

  

        // Append pagination information to the query
        $query .= " LIMIT $reviews_per_page OFFSET $offset";

        // Execute the query to get the user records
        $user_records = $wpdb->get_results($query);

        // Execute the query to get the total number of reviews
        $total_reviews = count($user_records);

        // Calculate the total number of pages
        $total_pages = ceil($total_reviews / $reviews_per_page);

        // Display the filter options in the review card
        ?>
        <div class="reviews-grid">
            <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="GET" class="review-filters">
                <div>
                    <label for="rating-filter">Filter by Rating:</label>
                    <select name="rating-filter" id="rating-filter">
                        <option value="">All Ratings</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>
                <div>
                    <label for="date-filter">Filter by Registration Date:</label>
                    <select name="date-filter" id="date-filter">
                        <option value="">All Dates</option>
                        <option value="latest">Latest Registered</option>
                    </select>
                </div>
                <input type="hidden" name="latest-filter-changes" value="0">
                <input type="hidden" name="review-filter-changes" value="0">
                <input type="hidden" name="pagination-changes" value="0">
                <input type="submit" value="Apply Filter">
            </form>
        <?php
        error_log(print_r($current_page));
            // Display pagination links
        echo '<div class="pagination">';
        if ($current_page > 1) {
            // Display the previous page link
            $prev_page = $current_page - 1;
            echo '<a href="' . esc_url(add_query_arg('page', $prev_page)) . '">Previous</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            // Display the page number link
            echo '<a href="' . esc_url(add_query_arg('page', $i)) . '">' . $i . '</a>';
        }

        if ($current_page < $total_pages) {
            // Display the next page link
            $next_page = $current_page + 1;
            echo '<a href="' . esc_url(add_query_arg('page', $next_page)) . '">Next</a>';
        }
        echo '</div>';


        // Check if there are user records
        if (!empty($user_records)) {
            foreach ($user_records as $record) {
                // Include the template file
                $file = ABSPATH . 'wp-content/plugins/user-registration/review-card.php';
                include_once($file);
            }
        } else {
            // Check if rating filter is applied and no reviews found
            if (!empty($rating_filter)) {
                echo 'No reviews found for the selected rating.';
            } else {
                echo 'No reviews found.';
            }
        }

       // Display pagination links
        echo '<div class="pagination">';
        if ($current_page > 1) {
            // Display the previous page link
            $prev_page = $current_page - 1;
            echo '<a href="' . esc_url(add_query_arg(array('page' => $prev_page, 'latest-filter-changes' => 0, 'review-filter-changes' => 0, 'pagination-changes' => 0))) . '">Previous</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            // Display the page number link
            echo '<a href="' . esc_url(add_query_arg(array('page' => $i, 'latest-filter-changes' => 0, 'review-filter-changes' => 0, 'pagination-changes' => 0))) . '">' . $i . '</a>';
        }

        if ($current_page < $total_pages) {
            // Display the next page link
            $next_page = $current_page + 1;
            echo '<a href="' . esc_url(add_query_arg(array('page' => $next_page, 'latest-filter-changes' => 0, 'review-filter-changes' => 0, 'pagination-changes' => 0))) . '">Next</a>';
        }
        echo '</div>';

    } else {
        ?>
        <p>You are not authorized to view this content. Please log in to access the reviews.</p>
    <?php }

    return ob_get_clean();
}
    
    

}
            
// Instantiate the plugin.
new User_Registration_Form_Plugin();
