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
                            <?php foreach ($user_records as $record) { ?>
                                <div class="review-card">
                                <div class="full-name">
                                        Full Name: <?php echo !empty($record->full_name) ? $record->full_name : 'N/A'; ?>
                                    </div>
                                    <div class="review-rating">
                                        Rating: <?php echo !empty($record->review_rating) ? $record->review_rating : 'N/A'; ?>
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