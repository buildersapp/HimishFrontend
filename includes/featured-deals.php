<?php if (!empty($featuredDeals)) { ?>
<!-- FEATURED DEALS -->
<div class="featured-deals-DV mb-4">
    <div>
        <div class="set-responsive container set-slider-ad-slick" id="set-feature-ads">
            <?php foreach ($featuredDeals as $fd) { ?>
            <div>
                <div class="position-relative set-overflow-hidden set-cursor-pointer set-main-feature-ad">
                    <img src="assets/img/feature-ad-1.jpg" alt="bg-blur-img" class="set-bg-blur-img">
                    <div class="set-inner-card-story">
                        <div class="set-img-video-story-card">
                            <a href="post-details.php?id=<?= base64_encode($fd['id']) ?>">
                                <img src="<?= MEDIA_BASE_URL . $fd['post_images'][0]['image'] ?>" class="set-story-content-img" alt="Ad Image">
                            </a>
                        </div>
                    </div>
                    <!-- <div class="buy-button set-bg-button-main">
                        <a href="post-details.php?id=<?= base64_encode($fd['id']) ?>" target="_blank"><button>Buy</button></a>
                    </div> -->
                    <div class="product-pricing set-bg-button-main">
                        <div class="pricing">
                            <h3 class="reg-price">Reg Price <span>$<?= $fd['regular_price'] ?></span></h3>
                            <h2 class="discount-price">Only <span>$<?= $fd['price'] ?></span></h2>
                        </div>
                        <div class="buy-button">
                            <a href="post-details.php?id=<?= base64_encode($fd['id']) ?>" target="_blank"><button>Buy</button></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>