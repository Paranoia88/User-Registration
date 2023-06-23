                  <style>
                            .reviews-grid {
                                display: grid;
                                grid-template-columns: repeat(3, 1fr);
                                grid-gap: 20px;
                                margin: 20px;
                            }

                            .review-card {
                                border: 1px solid #ccc;
                                padding: 20px;
                            }

                            .review-rating {
                                font-weight: bold;
                                font-size: 18px;
                                margin-bottom: 10px;
                            }

                            .review-description {
                                margin-bottom: 10px;
                            }

                            .review-email {
                                font-style: italic;
                            }
                        </style>

                        <div class="reviews-grid">
                            <!-- form to apply filters to the rendered data -->
                        <form action="<?php echo esc_url(home_url('/')); ?>" method="GET" class="review-filters">
                                    <label for="rating-filter">Filter by Rating:</label>
                                    <select name="rating-filter" id="rating-filter">
                                        <option value="">All Ratings</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>

                                    <label for="date-filter">Filter by Registration Date:</label>
                                    <select name="date-filter" id="date-filter">
                                        <option value="">All Dates</option>
                                        <option value="latest">Latest Registered</option>
                                    </select>

                                    <input type="submit" value="Apply Filter">
                                </form>
                            <?php foreach ($user_records as $record) { ?>
                                <div class="review-card">
                                <div class="full-name">
                                        Full Name: <?php echo !empty($record->full_name) ? $record->full_name : 'N/A'; ?>
                                    </div>
                                    <div class="review-rating">
                                        Rating: <?php if(!empty($record->review_rating)){ 
                                        if($record->review_rating == 1){
                                            echo '<span class="fa fa-star checked"></span>';
                                        }elseif($record->review_rating == 2){
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                        }elseif($record->review_rating == 3){
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                        }elseif($record->review_rating == 4){
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                        }elseif($record->review_rating == 5){
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                            echo '<span class="fa fa-star checked"></span>';
                                        }
                                        }else{
                                            echo 'N/A'; 
                                        }
                                        ?>
                                    </div>
                                    <div class="review-description">
                                        <?php echo !empty($record->review_description) ? $record->review_description : 'N/A'; ?>
                                    </div>
                                    <div class="review-email">
                                        <?php echo !empty($record->user_email) ? $record->user_email : 'N/A'; ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
