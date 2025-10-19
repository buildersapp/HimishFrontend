<?php

/**
 * Renders a post card based on the type.
 *
 * @param array $post The post data.
 * @param int $type The type of post card to render.
 */
if (!function_exists('renderPostCard')) {
    function renderPostCard($post, $type, $isDetail = 0, $isGuestMode = 0, $show_listing_images = 0,$isDetailPage =0,$totalCount =0) {
        if ($type == 0) {
            echo postCard($post,$isGuestMode,$isDetailPage);
        } elseif ($type == 1) {
            echo newListingCard($post, $isDetail, $show_listing_images,$totalCount);
        } elseif ($type == 2) {
            if(!empty($post['info'])){
                echo dealCard($post, $isDetail);
            }else{
                echo dealShareCard($post, $isDetail);
            }
        } elseif ($type == 3) {
            echo AdsCard($post, $isGuestMode);
        } elseif ($type == 4) {
            echo onSaleCard($post, 0);
        }
    }
}

/**
 * Generates a post card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('postCard')) {
    function postCard($post, $guestMode,$isDetailPage) {
        if (!empty($post)) { //dump($post); ?>
        <!-- FOR BUSINESS/POST CARDS -->   
         <?php if($isDetailPage ==0) { ?>
        <div class="col-lg-4 col-md-6 col-12 mt-2">
            <div class="post-card vpc-post-card" id="lcp-<?php echo @$post['id']; ?>" data-post-id="<?php echo @$post['id']; ?>" data-i-view="<?php echo @$post['i_view']; ?>">
                <?php if($post['boost_expire'] > 0){ ?>
                    <p class="txt-primary-small mx-2">Boosted Post</p>
                <?php } ?>
                
                <div class="position-relative set-comment-sidebar-custom">
                    <?php if(!empty($post['category'])){ ?>
                        <div class="ribbon"><?php echo $post['category']; ?></div>
                    <?php } ?>
                    <div class="img-cont">
                        <div class="post-img blur-bg-img" style="background-image:url(<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>) !important;">
                        </div>
                        <a href="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" data-fancybox="gallery">
                            <img src="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" class="img-fluid mx-auto" alt="">
                            
                        </a>
                    </div>
                </div>
                
                <div class="post-author">
                    
                    <div class="author-info w-100">
                        <h3>
                            <?php if (empty($guestMode)) { ?>
                            <a href="company-details.php?id=<?= base64_encode($post['company_id']) ?>"><span><?= mb_substr($post['company_name'], 0, 20) . (strlen($post['company_name']) > 20 ? '...' : '') ?></span></a>
                            <span><img src="assets/images/verify-badge.png" alt=""></span>
                            <?php }else{ ?>
                                <a href="javascript:void(0);" onclick="guestLoginModal()"><span><?php echo $post['company_name']; ?></span></a>
                                <span><img src="assets/images/verify-badge.png" alt=""></span>
                            <?php } ?>
                        </h3>
                        <div class="author-loc">
                            <span class="d-flex">
                                <?php
                                $post_locations = $post['post_locations'] ?? [];
                                $main_location = $post_locations[0] ?? null;
                                $other_locations = array_slice($post_locations, 1);
                                ?>

                                <?php if (!empty($main_location)): ?>
                                    <div class="post-location">
                                        <img src="assets/images/location-05.png" alt="Location Icon">
                                        <span class="loc-name">
                                            <?php if ($main_location['country_code'] === 'WW'): ?>
                                                <strong>Worldwide</strong>
                                            <?php elseif (!empty($main_location['latitude']) && !empty($main_location['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?= $main_location['latitude'] ?>,<?= $main_location['longitude'] ?>" target="_blank">
                                                    <?= !empty($main_location['city']) || !empty($main_location['state']) ? trim($main_location['city'] . ', ' . $main_location['state'], ', ') : trim($post['city'] . ', ' . $post['state'], ', ') ?>
                                                </a>
                                            <?php else: ?>
                                                <?= !empty($main_location['city']) || !empty($main_location['state']) ? trim($main_location['city'] . ', ' . $main_location['state'], ', ') : '' ?>
                                            <?php endif; ?>
                                        </span>

                                        <?php if ($main_location['country_code'] !== 'WW' && count($other_locations) > 0): ?>
                                            <span class="other-loc" data-bs-toggle="dropdown" aria-expanded="true">
                                                +<?= count($other_locations) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($main_location['country_code'] !== 'WW'): ?>
                                            <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                                <?php foreach ($post_locations as $index => $loc): ?>
                                                    <li class="px-3">
                                                        <a href="https://www.google.com/maps?q=<?= $loc['latitude'] ?>,<?= $loc['longitude'] ?>" target="_blank">
                                                            <strong><i class="fa fa-globe"></i>&nbsp;<?= $index === 0 ? 'Primary' : (!empty($loc['name']) ? $loc['name'] : 'Location ' . ($index)) ?></strong><br>
                                                            <?= trim($loc['city'] . ', ' . $loc['state'], ', ') ?>
                                                        </a>
                                                    </li>
                                                    <?php if ($index < count($post_locations) - 1): ?>
                                                        <hr class="com-location"/>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="post-time post-time ms-2 mt-1"><?= time_ago($post['created_at']) ?></span>
                            </span>
                        </div>
                        
                        <p class="post-text">
                            <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>">
                                <?php 
                                $title = $post['title'] ?: '';
                                echo (strlen($title) > 35) ? substr($title, 0, 35) . '...' : $title;
                                ?>
                            </a>
                        </p>
                    </div>
                </div>
                
                <div class="post-footer">
                    
                    <div class="post-tags hide-scrollbar set-tags-post-business-custom">
                        <?php if (!empty($post['service'])): ?>
                            <?php 
                                $keywords = explode(',', $post['service']);
                                $count = 0;
                                foreach ($keywords as $keyword): 
                                    if ($count >= 3) break; // show only first 3
                            ?>
                                <span onclick="addSearchParam('<?php echo trim($keyword); ?>')">
                                    <?php echo trim($keyword); ?>
                                </span>
                            <?php 
                                $count++;
                                endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                    <div class="post-interactions">
                        <div class="post-actions-left">
                            <div class="interaction-item like-post-fn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                                <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                            </div>
                            <div class="interaction-item post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                <img src="assets/img/cmnt.png" alt="Comment"><span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span>
                            </div>
                            <div class="interaction-item share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Post" data-type="get_posts"><img src="assets/img/share.png" alt="Share"><span><?php echo (@$post['total_share'] == 0) ? '' : @$post['total_share']; ?></span></div>
                        </div>
                        <div class="post-actions-right d-flex">
                            <?php if($post['is_claimed'] == "1" && $post['owner_id'] == $_SESSION['hm_wb_auth_data']['id']){ ?>
                                <button type="button" class="saveBtn-Bp boostPostModalFn" data-post-id="<?php echo base64_encode($post['id']); ?>" data-boost-expire="<?= $post['boost_expire']; ?>">
                                    <span class="saveBtnTxt">Boost Post</span>
                                </button>
                            <?php } ?>
                            <button class="btn-bookmarkFill fav-post-fn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                data-post-type="0">
                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" width="18" alt="Fav">
                            </button>
                        </div>
                    </div>
                    
                </div>

                
                <div class="post-actions d-flex align-items-start justify-content-between">
                    
                    <a href="#" class="action-item">
                        <img src="assets/img/eye.png" alt="Spotted">
                        <span class="action-item-count post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></span>
                    </a>
                    
                    <a href="javascript:void(0)" class="action-item action-rcmd-container" id="rc-p<?= $post['id'] ?>" onclick="getCompanyRecommends({ post_container: 'lcp-<?php echo ($post['id']); ?>' || '', company_id: '<?= addslashes($post['company_id']) ?>' || 0, company_name: '<?= addslashes($post['company']) ?>', total_recommends: '<?= addslashes($post['total_recommend']) ?>' || 0, container: '#pc-recommends-container', i_recommend: '<?= $post['i_recommend'] ?>' })">
                        <img src="assets/img/<?php echo $post['i_recommend'] ? 'like-fill' : 'like' ?>.png" alt="Recommend-<?php echo ($post['id']); ?>">
                        <span class="action-item-count d-<?php echo ($post['id']); ?> action-rcmd-count"><?php echo ($post['total_recommend'] == 0) ? '' : $post['total_recommend']; ?></span>
                    </a>

                    
                    <div class="dropdown action-item">
                        <button class="btn btn-link p-0 dropdown-toggle" type="button" id="postActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="assets/img/dots.png" alt="More Options">
                        </button>
                        
                        <ul class="dropdown-menu" aria-labelledby="postActionsDropdown1">
                            <?php if($post['i_follow'] == 3){ ?>
                                <li><a class="dropdown-item follow-company-fn" id="cf<?= $post['company_id'] ?>" data-company-id="<?= $post['company_id'] ?>" data-type="1" href="javascript:void(0)">Connect</a></li>
                            <?php }else if($post['i_follow'] == 0){ ?>
                                <li><a class="dropdown-item follow-company-fn" id="cf<?= $post['company_id'] ?>" data-company-id="<?= $post['company_id'] ?>" data-type="0" href="javascript:void(0)">Connection Request Sent</a></li>
                            <?php } ?>

                            <?php if(!$post['i_request_sent']){ ?>
                                <li>
                                    <a href="javascript:void(0)" class="dropdown-item" id="claimp<?= $post['id'] ?>" data-bs-toggle="offcanvas" data-bs-target="#createClaimOffcanvas" aria-controls="createClaimOffcanvas" onclick="$('#post_id').val(<?= $post['id'] ?>); $('#company_id').val(<?= $post['company_id'] ?>);">Claim This Post</a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportPostOffcanvas" aria-controls="reportPostOffcanvas" onclick="$('#report_post_id').val(<?= $post['id'] ?>);">Report Post
                                </a>
                            </li>
                            <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                                || 
                                (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                                <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                            <?php } ?>
                            <?php if(!isset($_SESSION['hm_wb_auth_data'])){ ?>
                                <li><p  class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportClaimPostOffcanvas" aria-controls="reportClaimPostOffcanvas" onclick="$('#report_claim_post_id').val(<?= $post['id'] ?>); $('#report_claim_post_type').val(0);">Claim & Remove Post</p></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php }else { ?>

        <!-- FOR DETAIL BUSINESS/POST CARD -->
        <div class="col-12">
            <div class="post-card vpc-post-card set-details-post-card-margin" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                <?php if($post['boost_expire'] > 0){ ?>
                    <p class="txt-primary-small mx-2">Boosted Post</p>
                <?php } ?>
                
                <div class="position-relative set-comment-sidebar-custom">
                    <?php if(!empty($post['category'])){ ?>
                        <div class="ribbon"><?php echo $post['category']; ?></div>
                    <?php } ?>
                    <div class="img-cont">
                        
                        <div class="post-img blur-bg-img" style="background-image:url(<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>) !important;">
                        </div>
                        
                        <a href="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" data-fancybox="gallery">
                            <img src="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" class="img-fluid mx-auto" alt="">
                            
                        </a>
                    </div>
                </div>
                
                <div class="post-author">
                    
                    <div class="author-info w-100">
                        <h3>
                            <?php if (empty($guestMode)) { ?>
                            <a href="company-details.php?id=<?= base64_encode($post['company_id']) ?>"><span><?= mb_substr($post['company_name'], 0, 20) . (strlen($post['company_name']) > 20 ? '...' : '') ?></span></a>
                            <span><img src="assets/images/verify-badge.png" alt=""></span>
                            <?php }else{ ?>
                                <a href="javascript:void(0);" onclick="guestLoginModal()"><span><?php echo $post['company_name']; ?></span></a>
                                <span><img src="assets/images/verify-badge.png" alt=""></span>
                            <?php } ?>
                        </h3>
                        <div class="author-loc">
                            <span class="d-flex">
                                <?php
                                $post_locations = $post['post_locations'] ?? [];
                                $main_location = $post_locations[0] ?? null;
                                $other_locations = array_slice($post_locations, 1);
                                ?>

                                <?php if (!empty($main_location)): ?>
                                    <div class="post-location">
                                        <img src="assets/images/location-05.png" alt="Location Icon">
                                        <span class="loc-name">
                                            <?php if ($main_location['country_code'] === 'WW'): ?>
                                                <strong>Worldwide</strong>
                                            <?php elseif (!empty($main_location['latitude']) && !empty($main_location['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?= $main_location['latitude'] ?>,<?= $main_location['longitude'] ?>" target="_blank">
                                                    <?= !empty($main_location['city']) || !empty($main_location['state']) ? trim($main_location['city'] . ', ' . $main_location['state'], ', ') : trim($post['city'] . ', ' . $post['state'], ', ') ?>
                                                </a>
                                            <?php else: ?>
                                                <?= !empty($main_location['city']) || !empty($main_location['state']) ? trim($main_location['city'] . ', ' . $main_location['state'], ', ') : '' ?>
                                            <?php endif; ?>
                                        </span>

                                        <?php if ($main_location['country_code'] !== 'WW' && count($other_locations) > 0): ?>
                                            <span class="other-loc" data-bs-toggle="dropdown" aria-expanded="true">
                                                +<?= count($other_locations) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($main_location['country_code'] !== 'WW'): ?>
                                            <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                                <?php foreach ($post_locations as $index => $loc): ?>
                                                    <li class="px-3">
                                                        <a href="https://www.google.com/maps?q=<?= $loc['latitude'] ?>,<?= $loc['longitude'] ?>" target="_blank">
                                                            <strong><i class="fa fa-globe"></i>&nbsp;<?= $index === 0 ? 'Primary' : (!empty($loc['name']) ? $loc['name'] : 'Location ' . ($index)) ?></strong><br>
                                                            <?= trim($loc['city'] . ', ' . $loc['state'], ', ') ?>
                                                        </a>
                                                    </li>
                                                    <?php if ($index < count($post_locations) - 1): ?>
                                                        <hr class="com-location"/>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <span class="post-time post-time ms-2 mt-1"><?= time_ago($post['created_at']) ?></span>
                            </span>
                        </div>
                        
                        <p class="post-text">
                            <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>">
                                <?php 
                                $title = $post['title'] ?: '';
                                echo (strlen($title) > 40) ? substr($title, 0, 40) . '...' : $title;
                                ?>
                            </a>
                        </p>
                    </div>
                </div>
                
                <div class="post-footer">
                    
                    <div class="post-tags hide-scrollbar">
                        <?php if (!empty($post['service'])): ?>
                            <?php 
                                $keywords = explode(',', $post['service']);
                                $count = 0;
                                foreach ($keywords as $keyword): 
                                    if ($count >= 3) break; 
                            ?>
                                <span onclick="addSearchParam('<?php echo trim($keyword); ?>')">
                                    <?php echo trim($keyword); ?>
                                </span>
                            <?php 
                                $count++;
                                endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                    <div class="post-interactions">
                        <div class="post-actions-left">
                            <div class="interaction-item like-post-fn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                                <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                            </div>
                            <div class="interaction-item post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                <img src="assets/img/cmnt.png" alt="Comment"><span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span>
                            </div>
                            <div class="interaction-item share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Post" data-type="get_posts"><img src="assets/img/share.png" alt="Share"><span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span></div>
                        </div>
                        <div class="post-actions-right d-flex">
                            <?php if($post['is_claimed'] == "1" && $post['owner_id'] == $_SESSION['hm_wb_auth_data']['id']){ ?>
                                <button type="button" class="saveBtn-Bp boostPostModalFn" data-post-id="<?php echo base64_encode($post['id']); ?>" data-boost-expire="<?= $post['boost_expire']; ?>">
                                    <span class="saveBtnTxt">Boost Post</span>
                                </button>
                            <?php } ?>
                            <button class="btn-bookmarkFill fav-post-fn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                data-post-type="0">
                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" width="18" alt="Fav">
                            </button>
                        </div>
                    </div>
                    
                </div>

                
                <div class="post-actions d-flex align-items-start justify-content-between">
                    
                    <a href="#" class="action-item">
                        <img src="assets/img/eye.png" alt="Spotted">
                        <span class="action-item-count post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></span>
                    </a>
                    
                    <a href="javascript:void(0)" class="action-item action-rcmd-container" id="rc-p<?= $post['id'] ?>" onclick="getCompanyRecommends({ post_container: 'lcp-<?php echo ($post['id']); ?>' || '', company_id: '<?= addslashes($post['company_id']) ?>' || 0, company_name: '<?= addslashes($post['company']) ?>', total_recommends: '<?= addslashes($post['total_recommend']) ?>' || 0, container: '#pc-recommends-container', i_recommend: '<?= $post['i_recommend'] ?>' })">
                        <img src="assets/img/<?php echo $post['i_recommend'] ? 'like-fill' : 'like' ?>.png" alt="Recommend-<?php echo ($post['id']); ?>">
                        <span class="action-item-count d-<?php echo ($post['id']); ?> action-rcmd-count"><?php echo ($post['total_recommend'] == 0) ? '' : $post['total_recommend']; ?></span>
                    </a>

                    
                    <div class="dropdown action-item">
                        <button class="btn btn-link p-0 dropdown-toggle" type="button" id="postActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="assets/img/dots.png" alt="More Options">
                        </button>
                        
                        <ul class="dropdown-menu" aria-labelledby="postActionsDropdown1">
                            <?php if($post['i_follow'] == 3){ ?>
                                <li><a class="dropdown-item follow-company-fn" id="cf<?= $post['company_id'] ?>" data-company-id="<?= $post['company_id'] ?>" data-type="1" href="javascript:void(0)">Connect</a></li>
                            <?php }else if($post['i_follow'] == 0){ ?>
                                <li><a class="dropdown-item follow-company-fn" id="cf<?= $post['company_id'] ?>" data-company-id="<?= $post['company_id'] ?>" data-type="0" href="javascript:void(0)">Connection Request Sent</a></li>
                            <?php } ?>

                            <?php if(!$post['i_request_sent']){ ?>
                                <li>
                                    <a href="javascript:void(0)" class="dropdown-item" id="claimp<?= $post['id'] ?>" data-bs-toggle="offcanvas" data-bs-target="#createClaimOffcanvas" aria-controls="createClaimOffcanvas" onclick="$('#post_id').val(<?= $post['id'] ?>); $('#company_id').val(<?= $post['company_id'] ?>);">Claim This Post</a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportPostOffcanvas" aria-controls="reportPostOffcanvas" onclick="$('#report_post_id').val(<?= $post['id'] ?>);">Report Post
                                </a>
                            </li>
                            <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                                || 
                                (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                                <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                            <?php } ?>
                            <?php if(!isset($_SESSION['hm_wb_auth_data'])){ ?>
                                <li><p  class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportClaimPostOffcanvas" aria-controls="reportClaimPostOffcanvas" onclick="$('#report_claim_post_id').val(<?= $post['id'] ?>); $('#report_claim_post_type').val(0);">Claim & Remove Post</p></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } }
    }
}

/**
 * Generates a featured card layout.
 *
 * @param array $featuredDeals The deal data.
 */
if (!function_exists('getFeaturedDealsHtmlFn')) {
    function getFeaturedDealsHtmlFn($featuredDeals) {
        if (!empty($featuredDeals)) { ?>
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
                                    <h3 class="reg-price">Reg Price <span><?= formatPriceIntl($fd['regular_price']) ?></span></h3>
                                    <h2 class="discount-price">Only <span><?= formatPriceIntl($fd['price']) ?></span></h2>
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
        <?php } 
    }
}


/**
 * Generates a ads card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('AdsCard')) {
    function AdsCard($post, $isGuestMode) {
        if (!empty($post)) { ?>
        <div class="col-lg-4 col-md-6 col-12 mt-2">
            <div class="post-card set-ads-card-mobile vpc-ad-card" id="lcp-<?php echo ($post['id']); ?>" data-scroll-ads="<?php echo ($post['product_id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>" data-ad-id="<?php echo ($post['id']); ?>">
                <!-- Post Image -->
                <?php if(count($post['sponser_ads_images'])){ 
                    $isVideo = 0;
                    if (!empty($post['sponser_ads_images'])) {
                        $media = $post['sponser_ads_images'][0]['media'];
                        // echo'<pre>';print_r($media);die('test');
                        $extension = pathinfo($media, PATHINFO_EXTENSION);
                        $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
                        if (in_array(strtolower($extension), $videoExtensions)) {
                            $isVideo = 1;
                        }
                    }?>
                    
                <div class="ad-img img-cont">
                    <?php if($isVideo == 1){ ?>
                        <video class="w-100" controls autoplay muted loop playsinline>
                            <source src="<?php echo MEDIA_BASE_URL.$post['sponser_ads_images'][0]['media']; ?>">
                        </video>
                    <?php }else{ ?>
                        <div class="post-img blur-bg-img" style="background-image:url(<?php echo MEDIA_BASE_URL.$post['sponser_ads_images'][0]['media']; ?>) !important;"></div>
                        <a href="<?php echo MEDIA_BASE_URL.$post['sponser_ads_images'][0]['media']; ?>" data-fancybox="gallery">
                            <img src="<?php echo MEDIA_BASE_URL.$post['sponser_ads_images'][0]['media']; ?>" class="img-fluid mx-auto" alt="">
                        </a>
                    <?php } ?>
                </div>
                <?php } ?>
                <!-- Author Section -->
                <div class="post-author">
                    <!-- <div class="author-img">
                        <img src="<?php echo !empty($post['companyProfile']['logo']) ? MEDIA_BASE_URL.$post['companyProfile']['logo'] : generateBase64Image($post['company_name']); ?>" class="mail-img" alt="">
                    </div> -->
                    <div class="author-info">
                        <h3>
                            <a href="company-details.php?id=<?= base64_encode($post['companyProfile']['id']) ?>"><span><?php echo $post['companyProfile']['name']; ?></span></a>
                            <span><img src="assets/images/verify-badge.png" alt=""></span>
                        </h3>
                        <div class="author-loc">
                            <img src="assets/images/location-05.png" alt="">
                            <span class="f-12-g">
                                <?php echo (!empty($post['location'])) ? $post['location'] : ''; ?>
                                <span class="post-time"><?= time_ago($post['created_at']) ?></span>
                            </span>
                        </div>
                        <p class="post-text"><a href="ad-details.php?id=<?php echo base64_encode($post['id']); ?>"><?php echo $post['title'] ?: ''; ?></a></p>
                    </div>
                </div>
                <!-- Post Footer -->
                <div class="post-footer">
                    <div class="ads-tags px-0">
                        <?php if (!empty($post['service'])): ?>
                            <?php foreach (explode(',', $post['service']) as $keyword): ?>
                                <span onclick="addSearchParam('<?php echo trim($keyword); ?>')"><?php echo $keyword; ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="ad-interactions p-0">
                        <div class="post-actions-left">
                            <div class="interaction-item-ad like-ads-fn" 
                                data-ads-id="<?php echo $post['id']; ?>" 
                                data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                                <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                            </div>
                            <div class="interaction-item-ad ads-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>"><img src="assets/img/cmnt.png" alt="Comment"><span> <?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span></div>
                            <div class="interaction-item-ad share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Ads" data-type="get_ads"><img src="assets/img/share.png" alt="Share"></div>
                            
                        </div>
                        
                        <div class="post-actions-right d-flex gap-4 gap-lg-5">
                            <div class="">

                                <?php
                                    $styleType = [];
                                    if(!empty($post['style_type'])){
                                        $styleType = json_decode($post['style_type'],true);
                                    }
                                ?>

                                <?php if ($post['button_type'] === 'quote') { ?>
                                    <a href="<?= $post['link'] ?>" target="_blank" class="ads-radio-bottom-btn-email radio-btn-clr-three text-white">
                                        <span>Get Quote</span>
                                    </a>
                                <?php }else if ($post['button_type'] === 'sms' && !$isGuestMode){ ?>
                                    <a href="single-chat.php?id=<?= base64_encode($post['company_id']) ?>&type=<?= base64_encode(3) ?>" class="ads-radio-bottom-btn-email radio-btn-clr-one text-white">
                                        <span>Message</span>
                                    </a>
                                <?php }else if ($post['button_type'] === 'info'){ ?>
                                    <a href="<?= $post['link'] ?>" target="_blank" class="ads-radio-bottom-btn-email radio-btn-clr-one text-white">
                                        <span>More Info</span>
                                    </a>
                                <?php }else if ($post['button_type'] === 'whatsapp'){ ?>
                                    <?php
                                    $phone = isset($styleType['to']) ? preg_replace('/\D/', '', $styleType['to']) : '';
                                    $message = isset($styleType['message']) ? urlencode($styleType['message']) : '';
                                    $whatsAppUrl = $phone ? "https://wa.me/{$phone}?text={$message}" : "#";
                                    ?>
                                    <a href="<?= $whatsAppUrl ?>" target="_blank" class="ads-radio-bottom-btn-email radio-btn-clr-two text-white">
                                        <img src="assets/img/logos_whatsapp-icon.svg" alt="bookmark">
                                        <span>Chat</span>
                                    </a>
                                <?php }else if ($post['button_type'] === 'email'){ ?>
                                    <a href="mailto:<?= count($styleType) ? $styleType['to'] : '' ?>?subject=<?= urlencode($styleType['subject']) ?>&body=<?= urlencode($styleType['message']) ?>" class="ads-radio-bottom-btn-email radio-btn-clr-one text-white">
                                        <img src="assets/img/envalops.svg" alt="bookmark">
                                        <span>Email</span>
                                    </a>
                                <?php } ?>
                            </div>
                            <button class="btn-bookmarkFill fav-ads-fn" 
                                data-ads-id="<?php echo $post['id']; ?>" 
                                data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                data-post-type="0">
                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" width="18" alt="Fav">
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Header Action Buttons -->
                <div class="post-actions d-flex align-items-start justify-content-between set-ad-action-bar">
                    <!-- View Action -->
                    <a href="#" class="action-item">
                        <img src="assets/img/eye.png" alt="Spotted">
                        <span class="action-item-count ads-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></span>
                    </a>
                    <!-- Recommend Action -->
                    <a href="javascript:void(0)" class="action-item action-rcmd-container" id="rc-p<?= $post['id'] ?>" onclick="getCompanyRecommends({ post_container: 'lcp-<?php echo ($post['id']); ?>' || '', company_id: '<?= addslashes($post['companyProfile']['id']) ?>' || 0, company_name: '<?= addslashes($post['companyProfile']['name']) ?>', total_recommends: '<?= addslashes($post['total_recommend']) ?>' || 0, container: '#pc-recommends-container', i_recommend: '<?= $post['i_recommend'] ?>' })">
                        <img src="assets/img/<?php echo $post['i_recommend'] ? 'like-fill' : 'like' ?>.png" alt="Recommend">
                        <span class="action-item-count action-rcmd-count"><?php echo ($post['total_recommend'] == 0) ? '' : $post['total_recommend']; ?></span>
                    </a>
                    <!-- More Options Dropdown -->
                    <div class="dropdown action-item">
                        <button class="btn btn-link p-0 dropdown-toggle" type="button" id="postActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="assets/img/dots.png" alt="More Options">
                        </button>
                        
                        <ul class="dropdown-menu" aria-labelledby="postActionsDropdown1">
                            <?php
                            $currentTime = time();
                            $expireTime = (int)$post['expire_date'];

                            if ($expireTime < $currentTime) {
                            ?>
                                <li><a href="create-ad.php?ad_id=<?= base64_encode($post['id']) ?>" class="dropdown-item text-info">Extend Ad Time</a></li>
                            <?php } ?>
                            <li><a href="company-details.php?id=<?= base64_encode($post['companyProfile']['id']) ?>" class="dropdown-item">View Company</a></li>
                            <?php 
                            if (
                                (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                                || 
                                (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])
                            ) { ?>
                                <li><a href="create-ad.php?ad_id=<?= base64_encode($post['id']) ?>&edit=1" class="dropdown-item">Edit</a></li>
                                <li><p class="dropdown-item" onclick="deleteAds('<?php echo base64_encode($post['id']); ?>')">Delete</p></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <!-- <hr /> -->
            </div>
        </div>
        <?php }
    }
}

/**
 * Generates a "Looking For" card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('lookingForCard')) {
    function lookingForCard($post, $isDetail) {
        if (!empty($post)) { ?>
        
            <div class="col-lg-6 mt-3">
                <div class="set-card-categories">
                    <div class="set-category-card-img position-relative">
                      <div class="set-top-overlay-badges">
                        <span class="set-blue-card-badge">Cars Used</span>
                        <span class="for-sale-badge">For Sale</span>
                      </div>
                      <!-- INTERACTION -->
                      <div class="set-main-interaction set-overlay-for-interaction set-overlay-for-interaction h-100">
                        <div class="d-flex align-items-center justify-content-evenly flex-column h-100">
                          <div class="set-comment-card">
                            <img src="assets/img/view-icon.png" alt="view" height="17px" />
                            <p class="m-0 f-7-b">34</p>
                          </div>
                          <div class="set-share-card">
                            <img src="assets/img/share.png" alt="share" height="17px" />
                            <p class="m-0 f-7-b">03</p>
                          </div>
                          <div class="set-heart-card">
                            <img
                              src="assets/img/heart-outline.png"
                              class="set-heart-icon"
                              data-filled="assets/img/heart-solid.png"
                              data-outline="assets/img/heart-outline.png"
                              alt="Heart" height="17px"
                            />
                            <p class="m-0 f-7-b">02</p>
                          </div>
                          <div class="set-comment-card">
                            <img src="assets/img/cmnt.png" alt="comment" height="17px" />
                            <p class="m-0 f-7-b">01</p>
                          </div>
                          
                          <div class="set-share-card">
                            <img src="assets/img/tag-icon.png" alt="tag" height="17px" />
                          </div>
                        </div>
                      </div>
                      <!-- PRODUCT IMG -->
                      <a href="product-detail-car.html" class="d-block set-product-new-listing">
                        <div class="carousel-inner">
                            <?php foreach ($post['post_images'] as $index => $image): ?>
                                
                                <div class="img-cont-listing carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                    
                                    <img src="<?php echo MEDIA_BASE_URL . $image['image']; ?>" class="blurred-bg" alt="...">
                                    
                                   
                                    <img src="<?php echo MEDIA_BASE_URL . $image['image']; ?>" class="img-fluid listing_img-listingc" alt="..." data-fancybox="gallery-listing-<?php echo $post['id']; ?>" data-caption="<?= $post['info'] ?>">
                                </div>

                            <?php endforeach; ?>
                        </div>
                      </a>
                    </div>
                    <div class="set-category-card-content">
                      <!-- FOR USER -->
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-1">
                          <a href="#" class="set-box-24-24">
                            <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="img-fluid rounded-circle" alt="">
                          </a>
                          <div class="">
                            <a href="#" class="f-12-bl fw-600 text-decoration-none">R. Montalvo</a>
                            <span class="f-10-grey-67 d-flex gap-2 align-items-center"><a href="#" class="text-decoration-none f-12-grey-67"><img src="assets/img/location-07.png" alt="location" class="pe-1">Lakewood, NJ (71 mi) </a></span>
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                          <a href="#" class="set-post-stamp d-inline-flex align-items-center gap-1 position-static"><img src="assets/img/message.png" alt="message"></a>

                          <div class="dropdown set-btn-down-none">
                            <button class="btn dropdown-toggle set-btn-ftr-dropdown " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <img src="assets/img/Claim.png" height="22px" alt="time">
                            </button>
                            <ul class="dropdown-menu set-position-inner">
                              <li><a class="dropdown-item" href="#">Connect</a></li>
                              <li><a class="dropdown-item" href="#">Claim This Post</a></li>
                              <li><a class="dropdown-item" href="#">Report Post</a></li>
                            </ul>
                          </div>
                        </div>
                      </div>
                      <!-- TITLE PRICE -->
                      <div class="d-flex justify-content-between mt-1">
                        <a href="product-detail-car.html" class="d-block text-decoration-none f-14-gb fw-600 pe-5">Nissan Rogue SV AWD</a>
                      </div>
                      <div class="">
                        <p class="f-12-g mb-0">Used - 2023 Kawasaki Mule PRO-FXR For Sale...<a href="#" class="f-12-bl text-decoration-none">Read More</a></p>
                      </div>
                      <!-- TAGS -->
                      <div class="set-tags-event-card set-card-overflow-tag-scroll">
                        <a href="#" class="set-tag-event text-decoration-none">Used Car</a>
                        <a href="#" class="set-tag-event text-decoration-none checked-tag">SUV</a>
                        <a href="#" class="set-tag-event text-decoration-none">Low Mileage</a>
                        <a href="#" class="set-tag-event text-decoration-none">Nissan</a>
                      </div>
                      <!-- OTHER DETAILS -->
                      <div class="d-flex align-items-center justify-content-between">
                        <div class="px-2 my-2 text-center set-line-height-16-org">
                          <p class="m-0 f-10-grey-67">Year</p>
                          <span class="f-12-bl">2012</span>
                        </div>
                        <div class="px-2 my-2 text-center set-line-height-16-org">
                          <p class="m-0 f-10-grey-67">Color</p>
                          <span class="f-12-bl">Gray</span>
                        </div>
                        <div class="px-2 my-2 text-center set-line-height-16-org">
                          <p class="m-0 f-10-grey-67">Mileage</p>
                          <span class="f-12-bl">7,500</span>
                        </div>
                        <div class="px-2 my-2 text-center set-line-height-16-org">
                          <p class="m-0 f-10-grey-67">Condition</p>
                          <span class="f-12-bl">Mint</span>
                        </div>
                      </div>
                      
                      <div class="d-flex align-items-center justify-content-between">
                        <span class="f-12-bl text-success fw-normal">Asking Price</span>
                        <h5 class="f-12-bl fw-bold m-0">$7850.55</h5>
                      </div>
                    </div>
                </div>
            </div>
        <!-- START OLD CODE LISTING -->
        <!-- <div class="sale-list sdfsafd vpc-listing-card mt-3" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
            <div class="sale-content">
                <div class="sale-author">
                    <div class="sale-author-img">
                        <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="img-fluid rounded-circle" alt="">
                    </div>
                    <div class="sale-author-info usrf<?= @$_SESSION['hm_wb_auth_data']['id'] ?>">


                        <?php

                            $showDropdown = false;

                            if (isset($_SESSION['hm_wb_auth_data']) && ($_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'] || $_SESSION['hm_wb_auth_data']['id'] != $post['user']['id'])) {
                                $showDropdown = true;
                            }

                            if (!isset($_SESSION['hm_wb_auth_data'])) {
                                $showDropdown = true;
                            }
                        ?>

                        <h3>
                            <span><a href="user-details.php?id=<?= base64_encode($post['user']['id']) ?>" target="_blank"><?= $post['user']['name'] ?></a></span>
                            <?php if ($showDropdown): ?>
                            <div class="dropdown action-item cmnt-action">
                                <button class="btn btn-link p-0 dropdown-toggle" type="button"
                                    id="listActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="assets/img/dots.png" alt="More Options">
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="listActionsDropdown1">
                                    <?php if(isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id']){ ?>
                                        <li><a class="py-0 px-0" href="create-listing.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                        <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                                    <?php }else{ ?>
                                        <?php if(!$post['i_request_sent']){ ?>
                                        <li>
                                            <a href="javascript:void(0)" class="dropdown-item" id="claimp<?= $post['id'] ?>" data-bs-toggle="offcanvas" data-bs-target="#createClaimOffcanvas" aria-controls="createClaimOffcanvas" onclick="$('#post_id').val(<?= $post['id'] ?>); $('#company_id').val(0);">Claim This Listing</a>
                                        </li>
                                        <?php }else{ ?>
                                        <li>
                                            <a href="javascript:void(0)" class="dropdown-item">Claim Request Sent</a>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if(!isset($_SESSION['hm_wb_auth_data'])){ ?>
                                        <li><p  class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportClaimPostOffcanvas" aria-controls="reportClaimPostOffcanvas" onclick="$('#report_claim_post_id').val(<?= $post['id'] ?>); $('#report_claim_post_type').val(1);">Claim & Remove Post</p></li>
                                    <?php } ?>
                                </ul>

                                <?php if(isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id']){ ?>
                                    <span><a href="single-chat.php?id=<?= base64_encode($post['id']) ?>&type=<?= base64_encode(2) ?>"><img src="assets/img/chat1.png" alt=""></a></span>
                                <?php } ?>
                            </div>
                            <?php endif; ?>
                        </h3>

                        <div class="author-loc">
                            <img src="assets/img/sale-loc.png" alt="">
                            <span><?php echo (!empty($post['city']) && !empty($post['state'])) ? $post['city'] . ', ' . $post['state'] : ($post['city'] ?: $post['state']); ?></span>
                             - 
                            <span><?= time_ago($post['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="sale-details">
                    <h2><a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>"><?= $post['title'] ?> </a></h2>
                    <p class="mt-1">
                        <?php if($isDetail){ ?>
                            <?= $post['info'] ?>
                        <?php }else{ ?>
                            <?= mb_substr($post['info'], 0, 100) . (strlen($post['info']) > 100 ? '...' : '') ?>
                            <?php if (strlen($post['info']) > 100) {?>
                                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>">Read more</a>
                            <?php } ?> 
                            
                        <?php } ?>
                    </p>
                </div>

                <div class="sale-tags">
                    <?php if (!empty($post['service'])): ?>
                        <?php foreach (explode(',', $post['service']) as $keyword): ?>
                            <span><?php echo $keyword; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="sale-interactions set-show-desktop">
                    <div class="single-sale-interaction post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>"><img src="assets/img/commentfill.png" alt=""><span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span></div>
                    <div class="single-sale-interaction like-post-fn" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                        <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                        <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                    </div>
                    <div class="single-sale-interaction"><img src="assets/img/viewfill.png" alt=""><span class="post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></span></div>
                    <div class="single-sale-interaction share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Listing" data-type="get_looking_for"><img src="assets/img/send.png" alt=""><span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span></div>
                </div>
            </div>

            <div class="sale-img-carousel">
                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>">
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                        <?php if (count($post['post_images']) > 1): ?>
                            <?php foreach ($post['post_images'] as $index => $image): ?>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index == 0 ? 'active' : ''; ?>"></button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($post['post_images'] as $index => $image): ?>
                                
                                <div class="img-cont-listing carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                    
                                    <img src="<?php echo MEDIA_BASE_URL . $image['image']; ?>" class="blurred-bg" alt="...">
                                    
                                   
                                    <img src="<?php echo MEDIA_BASE_URL . $image['image']; ?>" class="img-fluid listing_img-listingc" alt="..." data-fancybox="gallery-listing-<?php echo $post['id']; ?>" data-caption="<?= $post['info'] ?>">
                                </div>

                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="javascript:void(0);" class="sale-bookmark fav-post-fn" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                        data-post-type="1">
                        <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmark-up.png'; ?>" width="24" alt="Fav">
                    </a>
                    
                    <span class="set-sale-tag-up">For Sale</span>
                </a>
            </div>

        
            <div class="sale-interactions set-show-on-mobile">
                <div class="single-sale-interaction post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>"><img src="assets/img/commentfill.png" alt=""><span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span></div>
                    <div class="single-sale-interaction like-post-fn" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                        <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                        <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                    </div>
                <div class="single-sale-interaction"><img src="assets/img/viewfill.png" alt=""><span class="post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></span></div>
                <div class="single-sale-interaction share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Listing" data-type="get_looking_for"><img src="assets/img/send.png" alt=""><span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span></div>
            </div>
        </div> -->
        <!-- END OLD CODE LISTING -->
        <?php }
    }
}

/**
 * Generates a "New Listing Card" card layo ut.
 *
 * @param array $post The post data.
 */
if (!function_exists('onSaleCard')) {
    function onSaleCard($post, $isDetail) {

        $placeholderImage = 'assets/images/blank.png';
        if (!empty($post['real_estate_listings'])) {
            $placeholderImage = 'assets/images/apartment-ph.png';
        } else if (!empty($post['car_listings'])) {
            $placeholderImage = 'assets/images/vehicles-ph.png';
        } else if (!empty($post['events'])) {
            $placeholderImage = 'assets/images/events-ph.png';
        } else if (!empty($post['general_items'])) {
            $placeholderImage = 'assets/images/general-items-ph.png';
        } else if (!empty($post['service_listings'])) {
            $placeholderImage = 'assets/images/services-ph.png';
        } else if (!empty($post['job_listings'])) {
            $placeholderImage = 'assets/images/jobs-ph.png';
        } else if (!empty($post['found_lost_listings'])) {
            $placeholderImage = 'assets/images/lost-found-ph.png';
        } else if (!empty($post['dress_listings'])) {
            $placeholderImage = 'assets/images/dress-ph.png';
        }

        if (!empty($post) && @$post['ad_type'] != "Product") { ?>
            <?php
                $image = !empty($post['post_images'][0]['image']) ? MEDIA_BASE_URL . $post['post_images'][0]['image'] : $placeholderImage;

                $imageUrl = $image;
            ?>
            <div class="col-lg-4 mt-3 rst-<?= $post['ad_type'].'-'.$post['category'] ?> vpc-listing-card" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                <div class="set-card-categories set-card-onsale-main">
                    <div class="set-category-card-img position-relative">
                        
                        <!-- INTERACTION -->
                        <div class="set-main-interaction set-overlay-for-interaction set-overlay-for-interaction set-interaction-on-sale">
                            <div class="d-flex align-items-center justify-content-evenly flex-column h-100">

                                <!-- Spotted -->
                                <div class="set-comment-card curpointer">
                                    <img src="assets/images/view-icon.png" alt="view" height="17px" />
                                    <p class="m-0 f-7-b post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></p>
                                </div>

                                <!-- Share -->
                                <div class="set-share-card share-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="On Sale" data-type="get_on_sale">
                                    <img src="assets/images/share.png" alt="share" height="17px" />
                                    <p class="m-0 f-7-b"><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></p>
                                </div>

                                <!-- Like / UnLike -->
                                <div class="set-heart-card like-post-fn curpointer" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                    <img
                                        src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'heart-outline.png'; ?>"
                                        class="set-heart-icon"
                                        data-filled="assets/images/heart-solid.png"
                                        data-outline="assets/images/heart-outline.png"
                                        alt="Heart" height="17px"
                                    />
                                    <p class="m-0 f-7-b"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></p>
                                </div>

                                <!-- Comments -->
                                <div class="set-comment-card post-comments-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                    <img src="assets/images/comment.png" alt="comment" height="17px" />
                                    <p class="m-0 f-7-b"><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></p>
                                </div>
                                
                                <!-- Bookmark -->
                                <div class="set-share-card fav-listing-fn curpointer" data-post-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['ad_type']; ?>" data-listing-id="0" data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>">
                                    <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'tag-icon.png'; ?>" alt="tag" height="17px" />
                                </div>
                            </div>
                        </div>

                        <!-- Image -->
                        <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1" class="d-block set-product-new-listing">
                            <div class="carousel-inner">
                                <div class="img-cont-listing carousel-item active">
                                    <img src="<?php echo $imageUrl; ?>" class="blurred-bg" alt="..." data-fancybox="gallery">
                                    <img src="<?php echo $imageUrl; ?>" alt="<?= uniqid().'-'.$post['id'].time() ?>" class="img-fluid listing_img-listingc" data-fancybox="gallery">
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="set-category-card-content px-3 pb-3">
                        <div class="d-flex gap-3 justify-content-between">
                            <div class="set-width-title-location">
                                <!-- ANCHOR TITLE -->
                                <div class="">
                                    <a href="#" class="f-14-gb fw-bold set-title-onsale-card"><?= $post['title'] ?></a>
                                </div>
                                <!-- LOCATION -->
                                <div class="d-flex">
                                    <div class="d-flex align-items-start gap-2">
                                        <img src="assets/images/location-20-20.png" alt="img">
                                        <span class="f-12-g fw-medium"><?= $post['address'] ?> &nbsp;•<small> <?= $post['post_locations'][0]['distance'] ?? 0 ?> mi away</small></span>
                                    </div>
                                </div>
                            </div>
                            <!-- VALID -->
                            <div class="d-flex mt-1 set-min-w-72">
                                <!-- <?= date('F d, Y H:i A', $post['expire_date']) ?> -->
                                <div>
                                    <span class="f-12-grey-67 fw-medium">till <strong><?= date('M d', $post['expire_date']) ?></strong></span>
                                </div>
                            </div>
                        </div>
                        

                        
                        <!-- SPECIAL ITEMS -->
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- <div>
                                <img src="assets/images/tag.png" alt="img">
                                <span class="f-12-g fw-medium"><?= count($post['on_sale_listings']) ?> Sale Items</span>
                            </div> -->
                            <div>
                                <span class="set-badge-lightgrey set-badge-on-sale"><?= $post['category'] ?></span>
                            </div>
                        </div>

                        <!-- TABLE BLOCK -->
                        <?php
                        $onSaleListings = $post['on_sale_listings'] ?? [];

                        if (!empty($onSaleListings)):
                            // Get only the latest (last) row
                            $latestItem = end($onSaleListings);

                            // Determine which columns to show
                            $showCategory = !empty($latestItem['category']);
                            $showBrand = !empty($latestItem['brand']);
                            $showDescription = !empty($latestItem['product_name']);
                            $showSize = !empty($latestItem['size']);
                            $showColor = !empty($latestItem['color']);
                            $showPrice = !empty($latestItem['price']) || !empty($latestItem['sale_price']);
                        ?>
                        <!-- <div class="set-table-onSale-block mt-1 text-center set-table-onsale-sale-items">
                            <table width="100%">
                                <thead>
                                    <tr>
                                        <?php if ($showCategory): ?><th>Category</th><?php endif; ?>
                                        <?php if ($showBrand): ?><th>Brand</th><?php endif; ?>
                                        <?php if ($showDescription): ?><th>Description</th><?php endif; ?>
                                        <?php if ($showSize): ?><th>Size</th><?php endif; ?>
                                        <?php if ($showColor): ?><th>Color</th><?php endif; ?>
                                        <?php if ($showPrice): ?><th>Price</th><?php endif; ?>
                                        <?php if (count($onSaleListings) > 1): ?><th></th><?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php if ($showCategory): ?>
                                            <td><?= htmlspecialchars($latestItem['category']) ?></td>
                                        <?php endif; ?>

                                        <?php if ($showBrand): ?>
                                            <td><?= htmlspecialchars($latestItem['brand']) ?></td>
                                        <?php endif; ?>

                                        <?php if ($showDescription): ?>
                                            <td>
                                                <?= (strlen($latestItem['product_name']) > 80 
                                                        ? htmlspecialchars(substr($latestItem['product_name'], 0, 80)) . '...' 
                                                        : htmlspecialchars($latestItem['product_name'])) ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if ($showSize): ?>
                                            <td><?= htmlspecialchars($latestItem['size']) ?></td>
                                        <?php endif; ?>

                                        <?php if ($showColor): ?>
                                            <td><?= htmlspecialchars($latestItem['color']) ?></td>
                                        <?php endif; ?>

                                        <?php if ($showPrice): ?>
                                            <td class="set-text-green-onsale-tab">
                                                <?php 
                                                    if (!empty($latestItem['price'])) {
                                                        echo htmlspecialchars(formatPriceIntl($latestItem['price']));
                                                    } elseif (!empty($latestItem['sale_price'])) {
                                                        echo htmlspecialchars(formatPriceIntl($latestItem['sale_price']));
                                                    } else {
                                                        echo '-';
                                                    }
                                                ?>
                                            </td>
                                        <?php endif; ?>

                                        <?php if (count($onSaleListings) > 1): ?>
                                            <td class="text-center">
                                                <div class="set-share-card fav-listing-fn curpointer" 
                                                    data-post-id="<?= $post['id'] ?>" 
                                                    data-type="<?= $post['ad_type'] ?>" 
                                                    data-listing-id="<?= $latestItem['id'] ?>" 
                                                    data-favorited="<?= $latestItem['is_fav'] ? '1' : '0'; ?>">
                                                    <img src="assets/img/<?= $latestItem['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>

                            <?php if(!$isDetail) : ?>
                                <div class="text-center mt-1">
                                    <button class="set-view-more-btn" data-bs-toggle="modal" data-bs-target="#view-more-btn-<?= $post['id'] ?>">View More</button>
                                </div>
                            <?php endif; ?>
                        </div> -->
                        <?php endif; ?>


                        <!-- Modal -->
                        <div class="modal fade" id="view-more-btn-<?= $post['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $post['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content set-modal-onSale-main">
                                    <div class="modal-header py-3">
                                        <h2 class="modal-title fs-6 fw-bold" id="exampleModalLabel<?= $post['post_id'] ?>">
                                            <?= strtoupper($post['title'] ?? 'PRODUCT LIST') ?>
                                        </h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="set-table-view-onsale">
                                            <?php
                                            $onSaleListingsModal = $post['on_sale_listings'] ?? [];

                                            if (!empty($onSaleListingsModal)):
                                                // Determine which columns to show
                                                $showCategory = $showBrand = $showDescription = $showSize = $showColor = $showPrice = $showUnits = false;

                                                foreach ($onSaleListingsModal as $item) {
                                                    if (!empty($item['category'])) $showCategory = true;
                                                    if (!empty($item['brand'])) $showBrand = true;
                                                    if (!empty($item['product_name'])) $showDescription = true;
                                                    if (!empty($item['size'])) $showSize = true;
                                                    if (!empty($item['color'])) $showColor = true;
                                                    if (!empty($item['u_o_m'])) $showUnits = true;
                                                    if (!empty($item['price']) || !empty($item['sale_price'])) {
                                                        $showPrice = true;
                                                    }
                                                }
                                            ?>
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showCategory): ?><th>Category</th><?php endif; ?>
                                                        <?php if ($showBrand): ?><th>Brand</th><?php endif; ?>
                                                        <?php if ($showDescription): ?><th>Description</th><?php endif; ?>
                                                        <?php if ($showUnits): ?><th>Unit</th><?php endif; ?>
                                                        <?php if ($showSize): ?><th>Size</th><?php endif; ?>
                                                        <?php if ($showColor): ?><th>Color</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th>Price</th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($onSaleListingsModal as $item): ?>
                                                        <tr>
                                                            <?php if ($showCategory): ?>
                                                                <td><?= !empty($item['category']) ? htmlspecialchars($item['category']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showBrand): ?>
                                                                <td><?= !empty($item['brand']) ? htmlspecialchars($item['brand']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showDescription): ?>
                                                                <td>
                                                                    <?= !empty($item['product_name']) 
                                                                        ? htmlspecialchars($item['product_name'])
                                                                        : '-' ?>
                                                                </td>
                                                            <?php endif; ?>

                                                            <?php if ($showUnits): ?>
                                                                <td><?= !empty($item['u_o_m']) ? htmlspecialchars($item['u_o_m']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSize): ?>
                                                                <td><?= !empty($item['size']) ? htmlspecialchars($item['size']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showColor): ?>
                                                                <td><?= !empty($item['color']) ? htmlspecialchars($item['color']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showPrice): ?>
                                                                <td>
                                                                    <?php 
                                                                        if (!empty($item['price'])) {
                                                                            echo htmlspecialchars(formatPriceIntl($item['price']));
                                                                        } elseif (!empty($item['sale_price'])) {
                                                                            echo htmlspecialchars($item['sale_price']);
                                                                        } else {
                                                                            echo '-';
                                                                        }
                                                                    ?>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?php else: ?>
                                                <p class="text-center">No listings available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
    }
}

/**
 * Generates a "New Listing Card" card layo ut.
 *
 * @param array $post The post data.
 */
if (!function_exists('newListingCard')) {
    function newListingCard($post, $isDetail, $show_listing_images,$totalCount) {
        $placeholderImage = 'assets/images/blank.png';
        if (!empty($post['real_estate_listings'])) {
            $placeholderImage = 'assets/images/apartment-ph.png';
        } else if (!empty($post['car_listings'])) {
            $placeholderImage = 'assets/images/vehicles-ph.png';
        } else if (!empty($post['events'])) {
            $placeholderImage = 'assets/images/events-ph.png';
        } else if (!empty($post['general_items'])) {
            $placeholderImage = 'assets/images/general-items-ph.png';
        } else if (!empty($post['service_listings'])) {
            $placeholderImage = 'assets/images/services-ph.png';
        } else if (!empty($post['job_listings'])) {
            $placeholderImage = 'assets/images/jobs-ph.png';
        } else if (!empty($post['found_lost_listings'])) {
            $placeholderImage = 'assets/images/lost-found-ph.png';
        } else if (!empty($post['dress_listings'])) {
            $placeholderImage = 'assets/images/dress-ph.png';
        }

        if (!empty($post) && @$post['ad_type'] != "422423") { ?>
            <?php
                $image = !empty($post['post_images'][0]['image']) ? MEDIA_BASE_URL . $post['post_images'][0]['image'] : $placeholderImage;

                $imageUrl = ($show_listing_images == 0) ? $image : $placeholderImage;

                $price = $post['price'];
                $priceLabel = '';
                if (!empty($post['car_listings'][0]['price'])) {
                    $priceLabel = 'Asking Price';
                } elseif (!empty($post['real_estate_listings'][0]['price'])) {
                    $priceLabel = 'Asking Price';
                } elseif (!empty($post['general_items'][0]['price'])) {
                    $priceLabel = 'Asking Price';
                } elseif (!empty($post['service_listings'][0]['price'])) {
                    $priceLabel = 'Service Charge';
                } elseif (!empty($post['events'][0]['price'])) {
                    $priceLabel = 'Ticket Price';
                } elseif (!empty($post['job_listings'][0]['salary'])) {
                    $priceLabel = 'Salary';
                }
            ?>
            <?php if($isDetail){ ?>
                <div class="row rst-<?= $post['ad_type'].'-'.$post['category'] ?> vpc-listing-card" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                    <div class="col-lg-6">
                        <div class="thumbnail_slider set-custom-slider-newlisting">
                            <!-- Primary Slider Start-->
                            <div id="primary_slider">
                                <div class="splide__track position-relative">
                                    <div class="set-top-overlay-badges set-up-badges-detail">
                                        <?php if (!empty($post['real_estate_listings'])) { ?>
                                            <?php if (!empty($post['category'])): ?>
                                                <span class="set-blue-card-badge set-color-near-ornage"><?= $post['category'] ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($post['ad_purpose'])): ?>
                                                <span class="for-sale-badge color-green-sale"><?= $post['ad_purpose'] ?></span>
                                            <?php endif; ?>
                                        <?php } else if (!empty($post['car_listings'])) { ?>
                                            <?php if (!empty($post['category'])): ?>
                                                <span class="set-blue-card-badge"><?= $post['category'] ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($post['ad_purpose'])): ?>
                                                <span class="for-sale-badge"><?= $post['ad_purpose'] ?></span>
                                            <?php endif; ?>
                                        <?php } else if (!empty($post['events'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-brown"><?= $post['category'] ?></span>
                                                <?php endif; ?>

                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge set-badge-yellow"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['general_items'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-purple"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                                    
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge set-color-near-ornage"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['service_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-baby"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge set-badge-service"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['job_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-purple"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge badge-color-lightgreen"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['found_lost_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['ad_type'])): ?>
                                                    <span class="set-blue-card-badge set-badge-black"><?= $post['ad_type'] ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge set-badge-red-org"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['dress_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-pink"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="for-sale-badge color-green-sale"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <!-- INTERACTION -->
                                    <div class="set-main-interaction set-overlay-for-interaction set-overlay-for-interaction h-100 set-up-interaction-detail">
                                        <div class="d-flex align-items-center justify-content-evenly flex-column h-100">
                                            <!-- Spotted -->
                                            <div class="set-comment-card curpointer">
                                                <img src="assets/images/view-icon.png" alt="view" height="17px" />
                                                <p class="m-0 f-7-b post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></p>
                                            </div>

                                            <!-- Share -->
                                            <div class="set-share-card share-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Listing" data-type="get_looking_for">
                                                <img src="assets/images/share.png" alt="share" height="17px" />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></p>
                                            </div>

                                            <!-- Like / UnLike -->
                                            <div class="set-heart-card like-post-fn curpointer" 
                                            data-post-id="<?php echo $post['id']; ?>" 
                                            data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                                <img
                                                    src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'heart-outline.png'; ?>"
                                                    class="set-heart-icon"
                                                    data-filled="assets/images/heart-solid.png"
                                                    data-outline="assets/images/heart-outline.png"
                                                    alt="Heart" height="17px"
                                                />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></p>
                                            </div>

                                            <!-- Comments -->
                                            <div class="set-comment-card post-comments-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                                <img src="assets/images/comment.png" alt="comment" height="17px" />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></p>
                                            </div>
                                            
                                            <!-- Bookmark -->
                                            <div class="set-share-card fav-listing-fn curpointer" data-post-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['ad_type']; ?>" data-listing-id="0" data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>">
                                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'tag-icon.png'; ?>" alt="tag" height="17px" />
                                            </div>
                                        </div>
                                    </div>
                                    <ul class="splide__list">
                                        <img src="<?php echo $imageUrl; ?>" class="blurred-bg" alt="..." data-fancybox="gallery">
                                        <img src="<?php echo $imageUrl; ?>" alt="product" data-fancybox="gallery" class="set-img-org-slide-listing" />
                                    </ul>
                                </div>
                            </div>
                            <!-- Primary Slider End-->
                            <!-- Thumbnal Slider Start-->
                            <div id="thumbnail_slider">
                                <div class="splide__track">
                                    <ul class="splide__list">
                                        <!-- <li class="splide__slide">
                                        <img src="assets/images/vehicle.png" alt="product">
                                        </li> -->
                                    </ul>
                                </div>
                            </div>
                            <!-- Thumbnal Slider End-->
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="set-detailed-content">
                            <div class="">
                                <!-- CONTENT -->
                                <div class="set-business-card-content set-detail-content-postersz position-relative">
                                    <!-- FOR USER -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="#" class="set-box-24-24"><img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" alt="user" /></a>
                                            <div class="">
                                                <a href="#" class="f-14-bl fw-600 text-decoration-none"><?= $post['user']['name'] ?></a>
                                                <span class="f-10-grey-67 d-flex gap-2 align-items-center">
                                                    <a href="#" class="text-decoration-none f-12-grey-67"><img src="assets/images/location-07.png" alt="location" class="pe-1" /><?php echo (!empty($post['city']) && !empty($post['state'])) ? $post['city'] . ', ' . $post['state'] : ($post['city'] ?: $post['state']); ?></a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if(!empty($price)){ ?>
                                            <div class="d-flex align-items-center gap-2 justify-content-between">
                                                <span class="f-12-bl text-success fw-normal bhy"><?= $priceLabel ?></span>
                                                <h5 class="f-16-bl fw-600 m-0"><?= formatPriceIntl($price) ?></h5>
                                            </div>
                                            <?php } ?>
                                            <?php
                                            // CAR LISTING
                                            $car = $post['car_listings'][0] ?? [];
                                            if (!empty($car['price'])):
                                            ?>
                                            <div class="d-flex align-items-center gap-2 justify-content-between">
                                                <span class="f-12-bl text-success fw-normal">Asking Price</span>
                                                <h5 class="f-16-bl fw-600 m-0"><?= formatPriceIntl($post['price']) ?? '-' ?></h5>
                                            </div>
                                             <?php endif; ?>
                                            <?php if(isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] !== $post['user']['id']){ ?>
                                            <a href="single-chat.php?id=<?= base64_encode($post['id']) ?>&type=<?= base64_encode(2) ?>" class="set-post-stamp d-inline-flex align-items-center gap-1 position-static"><img src="assets/images/message.png" alt="message" /></a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- TITLE -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <a href="#" class="d-block text-decoration-none f-24-gb fw-600 pe-5"><?= $post['title'] ?></a>
                                        </div>
                                    </div>
                                    
                                    <?php if(!empty($post['info'])){ ?>
                                        <h4 class="f-18-gb mt-8 mb-0">Description</h4>
                                        <p class="f-16-grey-67 mb-0">
                                            <?= $post['info'] ?>
                                        </p>
                                    <?php } ?>

                                    <!-- Attributes -->
                                    <?php
                                    $carListings = $post['car_listings'] ?? [];
                                    if (!empty($carListings)):

                                        $carListingsToShow = array_slice($carListings, 0, 3);

                                        // Check if any value exists per field
                                        $showYear = $showMake = $showModel = $showMileage = $showCondition = $showPrice = false;
                                        foreach ($carListingsToShow as $item) {
                                            if (!empty($item['year'])) $showYear = true;
                                            if (!empty($item['make'])) $showMake = true;
                                            if (!empty($item['model'])) $showModel = true;
                                            if (!empty($item['mileage'])) $showMileage = true;
                                            if (!empty($item['condition'])) $showCondition = true;
                                            if (!empty($item['price'])) $showPrice = true;
                                        }

                                        // If no visible columns, skip
                                        if ($showYear || $showMake || $showModel || $showMileage || $showCondition || $showPrice):
                                    ?>

                                    <div class="set-table-onSale-block bg-white mt-1">
                                        <table width="100%">
                                            <thead>
                                                <tr>
                                                    <?php if ($showYear): ?><th class="text-center">Year</th><?php endif; ?>
                                                    <?php if ($showMake): ?><th class="text-center">Make</th><?php endif; ?>
                                                    <?php if ($showModel): ?><th class="text-center">Model</th><?php endif; ?>
                                                    <?php if ($showMileage): ?><th class="text-center">Mileage</th><?php endif; ?>
                                                    <?php if ($showCondition): ?><th class="text-center">Condition</th><?php endif; ?>
                                                    <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                    <?php if (count($carListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($carListingsToShow as $dt): ?>
                                                    <tr>
                                                        <?php if ($showYear): ?>
                                                            <td class="text-center"><?= !empty($dt['year']) ? htmlspecialchars($dt['year']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showMake): ?>
                                                            <td class="text-center"><?= !empty($dt['make']) ? htmlspecialchars($dt['make']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showModel): ?>
                                                            <td class="text-center"><?= !empty($dt['model']) ? htmlspecialchars($dt['model']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showMileage): ?>
                                                            <td class="text-center"><?= !empty($dt['mileage']) ? htmlspecialchars($dt['mileage']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showCondition): ?>
                                                            <td class="text-center"><?= !empty($dt['condition']) ? htmlspecialchars($dt['condition']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showPrice): ?>
                                                            <td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if (count($carListings) > 1): ?>
                                                            <td class="text-center">
                                                                <div class="set-share-card fav-listing-fn curpointer"
                                                                    data-post-id="<?= $post['id']; ?>"
                                                                    data-type="<?= $post['ad_type']; ?>"
                                                                    data-listing-id="<?= $dt['id'] ?>"
                                                                    data-favorited="<?= $dt['is_fav'] ? '1' : '0'; ?>">
                                                                    <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                </div>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <?php if (!$isDetail) : ?>
                                            <div class="text-center mt-1">
                                                <a href="post-details.php?id=<?= base64_encode($post['id']); ?>&type=1">
                                                    <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php
                                        endif; // any visible columns
                                    endif; // not empty
                                    ?>


                                    <?php
                                    // JOB LISTING
                                    $jobListings = $post['job_listings'] ?? [];

                                    if (!empty($jobListings)):
                                        $jobListingsToShow = array_slice($jobListings, 0, 3);

                                        // Determine which columns have at least one non-empty value
                                        $showType = $showEmploymentType = $showSalary = $showSkills = false;

                                        foreach ($jobListingsToShow as $dt) {
                                            if (!empty($dt['type'])) $showType = true;
                                            if (!empty($dt['employment_type'])) $showEmploymentType = true;
                                            if (!empty($dt['salary'])) $showSalary = true;
                                            if (!empty($dt['skill'])) $showSkills = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showEmploymentType): ?><th class="text-center">Employment Type</th><?php endif; ?>
                                                        <?php if ($showSalary): ?><th class="text-center">Salary</th><?php endif; ?>
                                                        <?php if ($showSkills): ?><th class="text-center">Skills</th><?php endif; ?>
                                                        <?php if (count($jobListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($jobListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showType): ?>
                                                                <td><?= !empty($dt['type']) ? htmlspecialchars($dt['type']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showEmploymentType): ?>
                                                                <td class="text-center"><?= !empty($dt['employment_type']) ? htmlspecialchars($dt['employment_type']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSalary): ?>
                                                                <td class="text-center"><?= !empty($dt['salary']) ? htmlspecialchars($dt['salary']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSkills): ?>
                                                                <td class="text-center"><?= !empty($dt['skill']) ? htmlspecialchars($dt['skill']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if (count($jobListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php 
                                    // REAL ESTATE LISTING
                                    $realEstateListings = $post['real_estate_listings'] ?? [];

                                    if (!empty($realEstateListings)):
                                        $realEstateListingsToShow = array_slice($realEstateListings, 0, 3);

                                        // Detect which columns to show based on presence of data
                                        $showType = $showListing = $showSqft = $showBeds = $showBaths = $showLevel = $showPrice = false;

                                        foreach ($realEstateListingsToShow as $dt) {
                                            if (!empty($dt['property_type'])) $showType = true;
                                            if (!empty($dt['listing_type'])) $showListing = true;
                                            if (!empty($dt['square_footage'])) $showSqft = true;
                                            if (!empty($dt['bedrooms'])) $showBeds = true;
                                            if (!empty($dt['bathrooms'])) $showBaths = true;
                                            if (!empty($dt['floor_level'])) $showLevel = true;
                                            if (!empty($dt['price'])) $showPrice = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showListing): ?><th class="text-center">Listing</th><?php endif; ?>
                                                        <?php if ($showSqft): ?><th class="text-center">Sq. ft</th><?php endif; ?>
                                                        <?php if ($showBeds): ?><th class="text-center">Beds</th><?php endif; ?>
                                                        <?php if ($showBaths): ?><th class="text-center">Bath</th><?php endif; ?>
                                                        <?php if ($showLevel): ?><th class="text-center">Level</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <?php if (count($realEstateListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($realEstateListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showType): ?>
                                                                <td><?= !empty($dt['property_type']) ? htmlspecialchars($dt['property_type']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showListing): ?>
                                                                <td class="text-center"><?= !empty($dt['listing_type']) ? htmlspecialchars($dt['listing_type']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSqft): ?>
                                                                <td class="text-center"><?= !empty($dt['square_footage']) ? htmlspecialchars($dt['square_footage']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showBeds): ?>
                                                                <td class="text-center"><?= !empty($dt['bedrooms']) ? htmlspecialchars($dt['bedrooms']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showBaths): ?>
                                                                <td class="text-center"><?= !empty($dt['bathrooms']) ? htmlspecialchars($dt['bathrooms']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showLevel): ?>
                                                                <td class="text-center"><?= !empty($dt['floor_level']) ? htmlspecialchars($dt['floor_level']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showPrice): ?>
                                                                <td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if (count($realEstateListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                               <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?= base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // GENERAL ITEMS LISTING
                                    $generalItems = $post['general_items'] ?? [];

                                    if (!empty($generalItems)):
                                        $generalItemsToShow = array_slice($generalItems, 0, 3);

                                        // Determine which columns have any non-empty data
                                        $showCategory = $showName = $showColor = $showSize = $showCondition = $showPrice = false;

                                        foreach ($generalItemsToShow as $dt) {
                                            if (!empty($dt['category'])) $showCategory = true;
                                            if (!empty($dt['name'])) $showName = true;
                                            if (!empty($dt['item_color'])) $showColor = true;
                                            if (!empty($dt['item_size'])) $showSize = true;
                                            if (!empty($dt['item_age'])) $showCondition = true; // 'item_age' used temporarily for Condition
                                            if (!empty($dt['price'])) $showPrice = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showCategory): ?><th>Category</th><?php endif; ?>
                                                        <?php if ($showName): ?><th class="text-center">Name</th><?php endif; ?>
                                                        <?php if ($showColor): ?><th class="text-center">Color</th><?php endif; ?>
                                                        <?php if ($showSize): ?><th class="text-center">Size</th><?php endif; ?>
                                                        <?php if ($showCondition): ?><th class="text-center">Condition</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <?php if (count($generalItems) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($generalItemsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showCategory): ?>
                                                                <td><?= !empty($dt['category']) ? htmlspecialchars($dt['category']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showName): ?>
                                                                <td class="text-center"><?= !empty($dt['name']) ? htmlspecialchars($dt['name']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showColor): ?>
                                                                <td class="text-center"><?= !empty($dt['item_color']) ? htmlspecialchars($dt['item_color']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSize): ?>
                                                                <td class="text-center"><?= !empty($dt['item_size']) ? htmlspecialchars($dt['item_size']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showCondition): ?>
                                                                <td class="text-center"><?= !empty($dt['item_age']) ? htmlspecialchars($dt['item_age']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showPrice): ?>
                                                                <td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if (count($generalItems) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // SERVICE LISTING
                                    $serviceListings = $post['service_listings'] ?? [];

                                    if (!empty($serviceListings)):
                                        $serviceListingsToShow = array_slice($serviceListings, 0, 3);

                                        // Detect columns to show
                                        $showPurpose = $showService = false;

                                        foreach ($serviceListingsToShow as $dt) {
                                            if (!empty($post['ad_purpose'])) $showPurpose = true;
                                            if (!empty($dt['service'])) $showService = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showPurpose): ?><th>Purpose</th><?php endif; ?>
                                                        <?php if ($showService): ?><th class="text-center">Service</th><?php endif; ?>
                                                        <?php if (count($serviceListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($serviceListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showPurpose): ?>
                                                                <td><?= !empty($post['ad_purpose']) ? htmlspecialchars($post['ad_purpose']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showService): ?>
                                                                <td class="text-center"><?= !empty($dt['service']) ? htmlspecialchars($dt['service']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if (count($serviceListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // EVENTS LISTING
                                    $eventListings = $post['events'] ?? [];

                                    if (!empty($eventListings)):
                                        $eventListingsToShow = array_slice($eventListings, 0, 3);

                                        // Determine which columns to show based on values
                                        $showPurpose = $showAddress = $showDateTime = false;

                                        foreach ($eventListingsToShow as $dt) {
                                            if (!empty($dt['purpose'])) $showPurpose = true;
                                            if (!empty($dt['physical_address'])) $showAddress = true;
                                            if (!empty($dt['date']) || !empty($dt['time'])) $showDateTime = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showPurpose): ?><th>Purpose</th><?php endif; ?>
                                                        <?php if ($showAddress): ?><th class="text-center">Address</th><?php endif; ?>
                                                        <?php if ($showDateTime): ?><th class="text-center">DateTime</th><?php endif; ?>
                                                        <?php if (count($eventListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($eventListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showPurpose): ?>
                                                                <td><?= !empty($dt['purpose']) ? htmlspecialchars($dt['purpose']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showAddress): ?>
                                                                <td class="text-center"><?= !empty($dt['physical_address']) ? htmlspecialchars($dt['physical_address']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showDateTime): ?>
                                                                <td class="text-center">
                                                                    <?php
                                                                        $datetime = trim(($dt['date'] ?? '') . ' ' . ($dt['time'] ?? ''));
                                                                        echo !empty(trim($datetime)) ? htmlspecialchars($datetime) : '-';
                                                                    ?>
                                                                </td>
                                                            <?php endif; ?>
                                                            <?php if (count($eventListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // LOST & FOUND LISTING
                                    $lostListings = $post['found_lost_listings'] ?? [];
                                    if (!empty($lostListings)):
                                        $lostListingsToShow = array_slice($lostListings, 0, 3);
                                    ?>
                                    <div class="set-table-onSale-block bg-white mt-1">
                                        <table width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Location</th>
                                                    <?php if(count($lostListings) > 1): ?>
                                                    <th class="text-center"></th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($lostListingsToShow as $dt): ?>
                                                    <tr>
                                                        <td class="text-center"><?= !empty($dt['description']) ? htmlspecialchars($dt['description']) : '-' ?></td>
                                                        <td class="text-center"><?= !empty($dt['location']) ? htmlspecialchars($dt['location']) : '-' ?></td>
                                                        <?php if(count($lostListings) > 1): ?>
                                                        <td class="text-center">
                                                            <div class="set-share-card fav-listing-fn curpointer" data-post-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['ad_type']; ?>" data-listing-id="<?= $dt['id'] ?>" data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                            </div>
                                                        </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <?php if(!$isDetail) : ?>
                                            <div class="text-center mt-1">
                                                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1"><button class="set-view-more-btn set-btn-block-view-more">View More</button></a>
                                            </div>
                                            <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php
                                    // DRESS LISTING
                                    $dressListings = $post['dress_listings'] ?? [];

                                    if (!empty($dressListings)):
                                        $dressListingsToShow = array_slice($dressListings, 0, 3);

                                        // Check which columns should be shown
                                        $showType = $showBrand = $showColor = $showFabric = $showSize = $showAge = $showFor = $showPrice = false;

                                        foreach ($dressListingsToShow as $dt) {
                                            if (!empty($dt['listing_type'])) $showType = true;
                                            if (!empty($dt['designer_brand'])) $showBrand = true;
                                            if (!empty($dt['color'])) $showColor = true;
                                            if (!empty($dt['fabric_type'])) $showFabric = true;
                                            if (!empty($dt['size'])) $showSize = true;
                                            if (!empty($dt['age'])) $showAge = true;
                                            if (!empty($dt['suggested_for'])) $showFor = true;
                                            if (!empty($dt['price'])) $showPrice = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block bg-white mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showBrand): ?><th class="text-center">Brand</th><?php endif; ?>
                                                        <?php if ($showColor): ?><th class="text-center">Color</th><?php endif; ?>
                                                        <?php if ($showFabric): ?><th class="text-center">Fabric</th><?php endif; ?>
                                                        <?php if ($showSize): ?><th class="text-center">Size</th><?php endif; ?>
                                                        <?php if ($showAge): ?><th class="text-center">Age</th><?php endif; ?>
                                                        <?php if ($showFor): ?><th class="text-center">For</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <?php if (count($dressListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dressListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showType): ?><td><?= !empty($dt['listing_type']) ? htmlspecialchars($dt['listing_type']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showBrand): ?><td class="text-center"><?= !empty($dt['designer_brand']) ? htmlspecialchars($dt['designer_brand']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showColor): ?><td class="text-center"><?= !empty($dt['color']) ? htmlspecialchars($dt['color']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showFabric): ?><td class="text-center"><?= !empty($dt['fabric_type']) ? htmlspecialchars($dt['fabric_type']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showSize): ?><td class="text-center"><?= !empty($dt['size']) ? htmlspecialchars($dt['size']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showAge): ?><td class="text-center"><?= !empty($dt['age']) ? htmlspecialchars($dt['age']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showFor): ?><td class="text-center"><?= !empty($dt['suggested_for']) ? htmlspecialchars($dt['suggested_for']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showPrice): ?><td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td><?php endif; ?>

                                                            <?php if (count($dressListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Attributes END -->

                                    <!-- BADGES -->
                                    <?php if (!empty($post['service'])): ?>
                                    <div class="set-card-badges">
                                        <?php foreach (explode(',', $post['service']) as $keyword): ?>
                                        <a href="#" class="d-inline-block set-fs-14 mt-16"><?php echo $keyword; ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <!-- ADD CALL  -->
                                <div class="mt-5">
                                    <?php if(@$post['hasShowedInterest'] == 0){ ?>
                                     <a href="#" class="set-btn-dark-blue show-listing-interest-fn" data-post-id="<?php echo $post['id']; ?>">Show Interest</a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }else{ ?>
                <div class="col-lg-6 mt-12 rst-<?= $post['ad_type'].'-'.$post['category'] ?> vpc-listing-card" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                    <div class="set-card-categories position-relative">
                        <!-- Top Badges -->
                        <div class="nlBadges d-flex align-items-center set-overlay-badge-category-card">
                                        <?php if (!empty($post['real_estate_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-teal"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['car_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-color-near-ornage">Popular Cars</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['events'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-darkblue">Event</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['general_items'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-purple"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['service_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-color-sky">Service</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['job_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-jobs-purple">Jobs</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['found_lost_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['ad_type'])): ?>
                                                    <span class="set-blue-card-badge set-badge-black"><?= $post['ad_type'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['dress_listings'])) { ?>
                                            <div class="mb-1">
                                                <?php if (!empty($post['ad_purpose'])): ?>
                                                    <span class="set-blue-card-badge color-green-sale"><?= $post['ad_purpose'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>   
                        </div>
                        <div class="set-category-card-img position-relative">
                            <div class="d-flex flex-column">
                                <!-- Image -->
                                <div class=" position-relative">
                                    <!-- INTERACTION -->
                                    <div class="set-main-interaction set-overlay-for-interaction set-overlay-for-interaction h-100">
                                        <div class="d-flex align-items-center justify-content-evenly flex-column h-100">

                                            <!-- Spotted -->
                                            <div class="set-comment-card curpointer">
                                                <img src="assets/images/view-icon.png" alt="view" height="17px" />
                                                <p class="m-0 f-7-b post-spotted-count"><?php echo ($post['total_spotted'] == 0) ? '' : $post['total_spotted']; ?></p>
                                            </div>

                                            <!-- Share -->
                                            <div class="set-share-card share-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Listing" data-type="get_looking_for">
                                                <img src="assets/images/share.png" alt="share" height="17px" />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></p>
                                            </div>

                                            <!-- Like / UnLike -->
                                            <div class="set-heart-card like-post-fn curpointer" 
                                            data-post-id="<?php echo $post['id']; ?>" 
                                            data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                                <img
                                                    src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'heart-outline.png'; ?>"
                                                    class="set-heart-icon"
                                                    data-filled="assets/images/heart-solid.png"
                                                    data-outline="assets/images/heart-outline.png"
                                                    alt="Heart" height="17px"
                                                />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></p>
                                            </div>

                                            <!-- Comments -->
                                            <div class="set-comment-card post-comments-fn curpointer" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                                <img src="assets/images/comment.png" alt="comment" height="17px" />
                                                <p class="m-0 f-7-b"><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></p>
                                            </div>
                                            
                                            <!-- Bookmark -->
                                            <div class="set-share-card fav-listing-fn curpointer" data-post-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['ad_type']; ?>" data-listing-id="0" data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>">
                                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'tag-icon.png'; ?>" alt="tag" height="17px" />
                                            </div>
                                        </div>
                                    </div>
                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1" class="d-block set-product-new-listing">
                                        <div class="carousel-inner">
                                            <div class="img-cont-listing carousel-item active">
                                                <img src="<?php echo $imageUrl; ?>" class="blurred-bg" alt="..." data-fancybox="gallery">
                                                <img src="<?php echo $imageUrl; ?>" alt="<?= uniqid().'-'.$post['id'].time() ?>" class="img-fluid listing_img-listingc" data-fancybox="gallery">
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="set-category-card-content position-relative set-card-new-listing-direction-main">
                            <!-- Dropdown Options -->
                            <div class="d-flex align-items-center gap-2 set-absolute-dropdown-listing">

                                    <?php

                                        $showDropdown = false;

                                        if (isset($_SESSION['hm_wb_auth_data']) && ($_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'] || $_SESSION['hm_wb_auth_data']['id'] != $post['user']['id'])) {
                                            $showDropdown = true;
                                        }

                                        if (!isset($_SESSION['hm_wb_auth_data'])) {
                                            $showDropdown = true;
                                        }
                                    ?>

                                    
                                    
                                    <?php if ($showDropdown): ?>
                                    <div class="dropdown set-btn-down-none">
                                        <button class="btn dropdown-toggle set-btn-ftr-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img src="assets/images/claim.png" height="18px" alt="time">
                                        </button>
                                        <ul class="dropdown-menu set-position-inner">
                                            <?php if(isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id']){ ?>
                                                <li><a class="py-0 px-0" href="create-listing.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                                <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                                            <?php }else{ ?>
                                                <?php if(!$post['i_request_sent']){ ?>
                                                <li>
                                                    <a href="javascript:void(0)" class="dropdown-item" id="claimp<?= $post['id'] ?>" data-bs-toggle="offcanvas" data-bs-target="#createClaimOffcanvas" aria-controls="createClaimOffcanvas" onclick="$('#post_id').val(<?= $post['id'] ?>); $('#company_id').val(0);">Claim This Listing</a>
                                                </li>
                                                <?php }else{ ?>
                                                <li>
                                                    <a href="javascript:void(0)" class="dropdown-item">Claim Request Sent</a>
                                                </li>
                                                <?php } ?>
                                            <?php } ?>

                                            <?php if(!isset($_SESSION['hm_wb_auth_data'])){ ?>
                                                <li><p  class="dropdown-item" data-bs-toggle="offcanvas" data-bs-target="#reportClaimPostOffcanvas" aria-controls="reportClaimPostOffcanvas" onclick="$('#report_claim_post_id').val(<?= $post['id'] ?>); $('#report_claim_post_type').val(1);">Claim & Remove Post</p></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                            </div>
                            <div class="">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <!-- Top Badges -->
                                    <div class="nlBadges d-flex align-items-center">
                                        <?php if (!empty($post['real_estate_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-color-near-ornage"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['car_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['events'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-brown"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['general_items'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-purple"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['service_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-baby"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['job_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge badge-color-purple"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['found_lost_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['ad_type'])): ?>
                                                    <span class="set-blue-card-badge set-badge-black"><?= $post['ad_type'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } else if (!empty($post['dress_listings'])) { ?>
                                            <div class="">
                                                <?php if (!empty($post['category'])): ?>
                                                    <span class="set-blue-card-badge set-badge-pink"><?= $post['category'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php } ?>   
                                    </div>
                                    <span class="set-post-stamp d-inline-flex align-items-center gap-1 position-static pe-3 pt-2"><img src="assets/images/clock-01.png" height="14px" alt="time"> <?= time_ago($post['created_at']) ?></span>
                                </div>
                                <!-- FOR USER -->
                                <!-- <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="#" class="set-box-24-24"><img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" alt="user"></a>
                                        <div class="">
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="#" class="f-14-bl fw-bold text-decoration-none"><?= $post['user']['name'] ?></a>
                                                <?php if(isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] !== $post['user']['id']){ ?>
                                                <a href="single-chat.php?id=<?= base64_encode($post['id']) ?>&type=<?= base64_encode(2) ?>" class="set-post-stamp d-inline-flex align-items-center gap-1 position-static">
                                                    <img src="assets/images/message.png" alt="message">
                                                </a>
                                                <?php } ?>
                                            </div>
                                            <span class="f-10-grey-67 d-flex gap-2 align-items-center"><a href="#" class="text-decoration-none f-12-grey-67"><img src="assets/images/location-07.png" alt="location" class="pe-1"><?php echo (!empty($post['city']) && !empty($post['state'])) ? $post['city'] . ', ' . $post['state'] : ($post['city'] ?: $post['state']); ?></a><span><svg width="4" height="4" viewBox="0 0 4 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="2" cy="2" r="2" fill="#676767"/>
                                                </svg>
                                                <?= time_ago($post['created_at']) ?></span></span>
                                        </div>
                                    </div>
                                </div> -->

                                <div class="d-flex align-items-center mt-1 justify-content-between">
                                    <!-- TITLE -->
                                    <div>
                                        <div class="d-flex justify-content-between">
                                            <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1" class="d-block text-decoration-none f-14-gb set-fw-600">
                                                <?php 
                                                    $title = $post['title'] ?: '';
                                                    echo (strlen($title) > 25) ? substr($title, 0, 25) . '...' : $title;
                                                ?>
                                            </a>
                                        </div>
                                        <!-- LOCATION -->
                                        <span class="f-10-grey-67 d-flex gap-2 align-items-center"><a href="#" class="text-decoration-none f-12-grey-67"><img src="assets/images/location-07.png" alt="location" class="pe-1"><?php echo (!empty($post['city']) && !empty($post['state'])) ? $post['city'] . ', ' . $post['state'] : ($post['city'] ?: $post['state']); ?></a><span></span>
                                        </span>
                                    </div>
                                    <?php if(!empty($post['price'])){ ?>
                                    <div class="text-end">
                                        <span class="f-12-bl text-success set-lh-13-askprice fw-normal">Asking Price</span>
                                        <h5 class="f-14-bl set-fw-600 m-0 "><?= formatPriceIntl($post['price']) ?></h5>
                                    </div>
                                    <?php } ?>
                                </div>
                                
                                <!-- INFO -->
                                <!-- <div class="">
                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                        <p class="f-12-g mb-0">
                                            <?= mb_substr($post['info'], 0, 100) . (strlen($post['info']) > 100 ? '...' : '') ?>
                                            <?php if (strlen($post['info']) > 100) {?>
                                                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1" class="f-12-bl text-decoration-none">Read more</a>
                                            <?php } ?>
                                        </p>
                                    </a>
                                </div> -->
                            </div>    
                            <div class="">  
                                <!-- TABLES -->
                                <div>
                                    <?php
                                    // CAR LISTING
                                        $carListings = $post['car_listings'] ?? [];
                                        if (!empty($carListings)):

                                            // Get only the latest listing (last item in array)
                                            $latestListing = end($carListings);

                                            // Check if any value exists per field
                                            $showYear = $showMake = $showModel = $showMileage = $showCondition = $showPrice = false;
                                            if (!empty($latestListing['year'])) $showYear = true;
                                            if (!empty($latestListing['make'])) $showMake = true;
                                            if (!empty($latestListing['model'])) $showModel = true;
                                            if (!empty($latestListing['mileage'])) $showMileage = true;
                                            if (!empty($latestListing['condition'])) $showCondition = true;
                                            if (!empty($latestListing['price'])) $showPrice = true;

                                            // If no visible columns, skip
                                            if ($showYear || $showMake || $showModel || $showMileage || $showCondition || $showPrice):
                                        ?>

                                        <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showYear): ?><th class="text-center">Year</th><?php endif; ?>
                                                        <?php if ($showMake): ?><th class="text-center">Make</th><?php endif; ?>
                                                        <?php if ($showModel): ?><th class="text-center">Model</th><?php endif; ?>
                                                        <?php if ($showMileage): ?><th class="text-center">Mileage</th><?php endif; ?>
                                                        <?php if ($showCondition): ?><th class="text-center">Condition</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <?php if ($showYear): ?>
                                                            <td class="text-center"><?= !empty($latestListing['year']) ? htmlspecialchars($latestListing['year']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showMake): ?>
                                                            <td class="text-center"><?= !empty($latestListing['make']) ? htmlspecialchars($latestListing['make']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showModel): ?>
                                                            <td class="text-center"><?= !empty($latestListing['model']) ? htmlspecialchars($latestListing['model']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showMileage): ?>
                                                            <td class="text-center"><?= !empty($latestListing['mileage']) ? htmlspecialchars($latestListing['mileage']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showCondition): ?>
                                                            <td class="text-center"><?= !empty($latestListing['condition']) ? htmlspecialchars($latestListing['condition']) : '-' ?></td>
                                                        <?php endif; ?>
                                                        <?php if ($showPrice): ?>
                                                            <td class="text-center"><?= !empty($latestListing['price']) ? htmlspecialchars(formatPriceIntl($latestListing['price'])) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <td class="text-center">
                                                            <div class="set-share-card fav-listing-fn curpointer"
                                                                data-post-id="<?= $post['id']; ?>"
                                                                data-type="<?= $post['ad_type']; ?>"
                                                                data-listing-id="<?= $latestListing['id'] ?>"
                                                                data-favorited="<?= $latestListing['is_fav'] ? '1' : '0'; ?>">
                                                                <img src="assets/img/<?= $latestListing['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <?php
                                            endif; // any visible columns
                                        endif; // not empty
                                        ?>
                                    <?php
                                    // JOB LISTING
                                    $jobListings = $post['job_listings'] ?? [];

                                    if (!empty($jobListings)):

                                        // Get only the latest job listing (last item in the array)
                                        $latestJob = end($jobListings);

                                        // Determine which columns have at least one non-empty value
                                        $showType = $showEmploymentType = $showSalary = $showSkills = false;

                                        if (!empty($latestJob['type'])) $showType = true;
                                        if (!empty($latestJob['employment_type'])) $showEmploymentType = true;
                                        if (!empty($latestJob['salary'])) $showSalary = true;
                                        if (!empty($latestJob['skill'])) $showSkills = true;
                                    ?>
                                        <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showEmploymentType): ?><th class="text-center">Employment Type</th><?php endif; ?>
                                                        <?php if ($showSalary): ?><th class="text-center">Salary</th><?php endif; ?>
                                                        <?php if ($showSkills): ?><th class="text-center">Skills</th><?php endif; ?>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <?php if ($showType): ?>
                                                            <td><?= !empty($latestJob['type']) ? htmlspecialchars($latestJob['type']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showEmploymentType): ?>
                                                            <td class="text-center"><?= !empty($latestJob['employment_type']) ? htmlspecialchars($latestJob['employment_type']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showSalary): ?>
                                                            <td class="text-center"><?= !empty($latestJob['salary']) ? htmlspecialchars($latestJob['salary']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showSkills): ?>
                                                            <td class="text-center"><?= !empty($latestJob['skill']) ? htmlspecialchars($latestJob['skill']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <td class="text-center">
                                                            <div class="set-share-card fav-listing-fn curpointer"
                                                                data-post-id="<?= $post['id']; ?>"
                                                                data-type="<?= $post['ad_type']; ?>"
                                                                data-listing-id="<?= $latestJob['id'] ?>"
                                                                data-favorited="<?= $latestJob['is_fav'] ? '1' : '0'; ?>">
                                                                <img src="assets/img/<?= $latestJob['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <!-- <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?= base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div> -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>



                                    <?php 
                                    // REAL ESTATE LISTING
                                    $realEstateListings = $post['real_estate_listings'] ?? [];

                                   
                                    if (!empty($realEstateListings)):

                                        // Get only the latest listing (last item)
                                        $latestListing = end($realEstateListings);

                                        // Detect which columns to show based on presence of data
                                        $showType = $showListing = $showSqft = $showBeds = $showBaths = $showLevel = $showPrice = false;

                                        if (!empty($latestListing['property_type'])) $showType = true;
                                        if (!empty($latestListing['listing_type'])) $showListing = true;
                                        if (!empty($latestListing['square_footage'])) $showSqft = true;
                                        if (!empty($latestListing['bedrooms'])) $showBeds = true;
                                        if (!empty($latestListing['bathrooms'])) $showBaths = true;
                                        if (!empty($latestListing['floor_level'])) $showLevel = true;
                                        if (!empty($latestListing['price'])) $showPrice = true;
                                    ?>
                                        <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showListing): ?><th class="text-center">Listing</th><?php endif; ?>
                                                        <?php if ($showSqft): ?><th class="text-center">Sq.&nbsp;ft</th><?php endif; ?>
                                                        <?php if ($showBeds): ?><th class="text-center">Beds</th><?php endif; ?>
                                                        <?php if ($showBaths): ?><th class="text-center">Bath</th><?php endif; ?>
                                                        <?php if ($showLevel): ?><th class="text-center">Level</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <th class="text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <?php if ($showType): ?>
                                                            <td><?= !empty($latestListing['property_type']) ? htmlspecialchars($latestListing['property_type']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showListing): ?>
                                                            <td class="text-center"><?= !empty($latestListing['listing_type']) ? htmlspecialchars($latestListing['listing_type']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showSqft): ?>
                                                            <td class="text-center"><?= !empty($latestListing['square_footage']) ? htmlspecialchars($latestListing['square_footage']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showBeds): ?>
                                                            <td class="text-center"><?= !empty($latestListing['bedrooms']) ? htmlspecialchars($latestListing['bedrooms']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showBaths): ?>
                                                            <td class="text-center"><?= !empty($latestListing['bathrooms']) ? htmlspecialchars($latestListing['bathrooms']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showLevel): ?>
                                                            <td class="text-center"><?= !empty($latestListing['floor_level']) ? htmlspecialchars($latestListing['floor_level']) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <?php if ($showPrice): ?>
                                                            <td class="text-center"><?= !empty($latestListing['price']) ? htmlspecialchars(formatPriceIntl($latestListing['price'])) : '-' ?></td>
                                                        <?php endif; ?>

                                                        <td class="text-center">
                                                            <div class="set-share-card fav-listing-fn curpointer"
                                                                data-post-id="<?= $post['id']; ?>"
                                                                data-type="<?= $post['ad_type']; ?>"
                                                                data-listing-id="<?= $latestListing['id'] ?>"
                                                                data-favorited="<?= $latestListing['is_fav'] ? '1' : '0'; ?>">
                                                                <img src="assets/img/<?= $latestListing['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <!-- <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?= base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div> -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>



                                    <?php
                                    // GENERAL ITEMS LISTING
                                    $generalItems = $post['general_items'] ?? [];

                                    if (!empty($generalItems)):
                                        $generalItemsToShow = array_slice($generalItems, 0, 3);

                                        // Determine which columns have any non-empty data
                                        $showCategory = $showName = $showColor = $showSize = $showCondition = $showPrice = false;

                                        foreach ($generalItemsToShow as $dt) {
                                            if (!empty($dt['category'])) $showCategory = true;
                                            if (!empty($dt['name'])) $showName = true;
                                            if (!empty($dt['item_color'])) $showColor = true;
                                            if (!empty($dt['item_size'])) $showSize = true;
                                            if (!empty($dt['item_age'])) $showCondition = true; // 'item_age' used temporarily for Condition
                                            if (!empty($dt['price'])) $showPrice = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showCategory): ?><th>Category</th><?php endif; ?>
                                                        <?php if ($showName): ?><th class="text-center">Name</th><?php endif; ?>
                                                        <?php if ($showColor): ?><th class="text-center">Color</th><?php endif; ?>
                                                        <?php if ($showSize): ?><th class="text-center">Size</th><?php endif; ?>
                                                        <?php if ($showCondition): ?><th class="text-center">Condition</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <?php if (count($generalItems) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($generalItemsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showCategory): ?>
                                                                <td><?= !empty($dt['category']) ? htmlspecialchars($dt['category']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showName): ?>
                                                                <td class="text-center"><?= !empty($dt['name']) ? htmlspecialchars($dt['name']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showColor): ?>
                                                                <td class="text-center"><?= !empty($dt['item_color']) ? htmlspecialchars($dt['item_color']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showSize): ?>
                                                                <td class="text-center"><?= !empty($dt['item_size']) ? htmlspecialchars($dt['item_size']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showCondition): ?>
                                                                <td class="text-center"><?= !empty($dt['item_age']) ? htmlspecialchars($dt['item_age']) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if ($showPrice): ?>
                                                                <td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td>
                                                            <?php endif; ?>

                                                            <?php if (count($generalItems) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // SERVICE LISTING
                                    $serviceListings = $post['service_listings'] ?? [];

                                    if (!empty($serviceListings)):
                                        $serviceListingsToShow = array_slice($serviceListings, 0, 3);

                                        // Detect columns to show
                                        $showPurpose = $showService = false;

                                        foreach ($serviceListingsToShow as $dt) {
                                            if (!empty($post['ad_purpose'])) $showPurpose = true;
                                            if (!empty($dt['service'])) $showService = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block set-scroll-and-pad-none mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showPurpose): ?><th class="w-50 text-center">Purpose</th><?php endif; ?>
                                                        <?php if ($showService): ?><th class="w-50 text-center">Service</th><?php endif; ?>
                                                        <?php if (count($serviceListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($serviceListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showPurpose): ?>
                                                                <td class="w-50 text-center"><?= !empty($post['ad_purpose']) ? htmlspecialchars($post['ad_purpose']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showService): ?>
                                                                <td class="w-50 text-center"><?= !empty($dt['service']) ? htmlspecialchars($dt['service']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if (count($serviceListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <!-- <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div> -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php
                                    // EVENTS LISTING
                                    $eventListings = $post['events'] ?? [];

                                    if (!empty($eventListings)):
                                        $eventListingsToShow = array_slice($eventListings, 0, 3);

                                        // Determine which columns to show based on values
                                        $showPurpose = $showAddress = $showDateTime = false;

                                        foreach ($eventListingsToShow as $dt) {
                                            if (!empty($dt['purpose'])) $showPurpose = true;
                                            if (!empty($dt['physical_address'])) $showAddress = true;
                                            if (!empty($dt['date']) || !empty($dt['time'])) $showDateTime = true;
                                        }
                                    ?>
                                        <!-- <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showPurpose): ?><th>Purpose</th><?php endif; ?>
                                                        <?php if ($showAddress): ?><th class="text-center">Address</th><?php endif; ?>
                                                        <?php if ($showDateTime): ?><th class="text-center">DateTime</th><?php endif; ?>
                                                        <?php if (count($eventListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($eventListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showPurpose): ?>
                                                                <td><?= !empty($dt['purpose']) ? htmlspecialchars($dt['purpose']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showAddress): ?>
                                                                <td class="text-center"><?= !empty($dt['physical_address']) ? htmlspecialchars($dt['physical_address']) : '-' ?></td>
                                                            <?php endif; ?>
                                                            <?php if ($showDateTime): ?>
                                                                <td class="text-center">
                                                                    <?php
                                                                        $datetime = trim(($dt['date'] ?? '') . ' ' . ($dt['time'] ?? ''));
                                                                        echo !empty(trim($datetime)) ? htmlspecialchars($datetime) : '-';
                                                                    ?>
                                                                </td>
                                                            <?php endif; ?>
                                                            <?php if (count($eventListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div> -->
                                    <?php endif; ?>


                                    <?php
                                    // LOST & FOUND LISTING
                                    $lostListings = $post['found_lost_listings'] ?? [];
                                    if (!empty($lostListings)):
                                        $lostListingsToShow = array_slice($lostListings, 0, 3);
                                    ?>
                                    <div class="set-table-onSale-block mt-1">
                                        <table width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Location</th>
                                                    <?php if(count($lostListings) > 1): ?>
                                                    <th class="text-center"></th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($lostListingsToShow as $dt): ?>
                                                    <tr>
                                                        <td class="text-center"><?= !empty($dt['description']) ? htmlspecialchars($dt['description']) : '-' ?></td>
                                                        <td class="text-center"><?= !empty($dt['location']) ? htmlspecialchars($dt['location']) : '-' ?></td>
                                                        <?php if(count($lostListings) > 1): ?>
                                                        <td class="text-center">
                                                            <div class="set-share-card fav-listing-fn curpointer" data-post-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['ad_type']; ?>" data-listing-id="<?= $dt['id'] ?>" data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                            </div>
                                                        </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <?php if(!$isDetail) : ?>
                                            <div class="text-center mt-1">
                                                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1"><button class="set-view-more-btn set-btn-block-view-more">View More</button></a>
                                            </div>
                                            <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php
                                    // DRESS LISTING
                                    $dressListings = $post['dress_listings'] ?? [];

                                    if (!empty($dressListings)):
                                        $dressListingsToShow = array_slice($dressListings, 0, 3);

                                        // Check which columns should be shown
                                        $showType = $showBrand = $showColor = $showFabric = $showSize = $showAge = $showFor = $showPrice = false;

                                        foreach ($dressListingsToShow as $dt) {
                                            if (!empty($dt['listing_type'])) $showType = true;
                                            if (!empty($dt['designer_brand'])) $showBrand = true;
                                            if (!empty($dt['color'])) $showColor = true;
                                            if (!empty($dt['fabric_type'])) $showFabric = true;
                                            if (!empty($dt['size'])) $showSize = true;
                                            if (!empty($dt['age'])) $showAge = true;
                                            if (!empty($dt['suggested_for'])) $showFor = true;
                                            if (!empty($dt['price'])) $showPrice = true;
                                        }
                                    ?>
                                        <div class="set-table-onSale-block mt-1">
                                            <table width="100%">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showType): ?><th>Type</th><?php endif; ?>
                                                        <?php if ($showBrand): ?><th class="text-center">Brand</th><?php endif; ?>
                                                        <?php if ($showColor): ?><th class="text-center">Color</th><?php endif; ?>
                                                        <?php if ($showFabric): ?><th class="text-center">Fabric</th><?php endif; ?>
                                                        <?php if ($showSize): ?><th class="text-center">Size</th><?php endif; ?>
                                                        <?php if ($showAge): ?><th class="text-center">Age</th><?php endif; ?>
                                                        <?php if ($showFor): ?><th class="text-center">For</th><?php endif; ?>
                                                        <?php if ($showPrice): ?><th class="text-center">Price</th><?php endif; ?>
                                                        <?php if (count($dressListings) > 1): ?><th class="text-center"></th><?php endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dressListingsToShow as $dt): ?>
                                                        <tr>
                                                            <?php if ($showType): ?><td><?= !empty($dt['listing_type']) ? htmlspecialchars($dt['listing_type']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showBrand): ?><td class="text-center"><?= !empty($dt['designer_brand']) ? htmlspecialchars($dt['designer_brand']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showColor): ?><td class="text-center"><?= !empty($dt['color']) ? htmlspecialchars($dt['color']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showFabric): ?><td class="text-center"><?= !empty($dt['fabric_type']) ? htmlspecialchars($dt['fabric_type']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showSize): ?><td class="text-center"><?= !empty($dt['size']) ? htmlspecialchars($dt['size']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showAge): ?><td class="text-center"><?= !empty($dt['age']) ? htmlspecialchars($dt['age']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showFor): ?><td class="text-center"><?= !empty($dt['suggested_for']) ? htmlspecialchars($dt['suggested_for']) : '-' ?></td><?php endif; ?>
                                                            <?php if ($showPrice): ?><td class="text-center"><?= !empty($dt['price']) ? htmlspecialchars(formatPriceIntl($dt['price'])) : '-' ?></td><?php endif; ?>

                                                            <?php if (count($dressListings) > 1): ?>
                                                                <td class="text-center">
                                                                    <div class="set-share-card fav-listing-fn curpointer"
                                                                        data-post-id="<?php echo $post['id']; ?>"
                                                                        data-type="<?php echo $post['ad_type']; ?>"
                                                                        data-listing-id="<?= $dt['id'] ?>"
                                                                        data-favorited="<?php echo $dt['is_fav'] ? '1' : '0'; ?>">
                                                                        <img src="assets/img/<?= $dt['is_fav'] ? 'bookmarkfill.png' : 'tag-icon.png' ?>" alt="tag" height="17px" />
                                                                    </div>
                                                                </td>
                                                            <?php endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>

                                            <?php if (!$isDetail): ?>
                                                <!-- <div class="text-center mt-1">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1">
                                                        <button class="set-view-more-btn set-btn-block-view-more">View More</button>
                                                    </a>
                                                </div> -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>


                                    <?php if (!$carListings && !$jobListings && !$realEstateListings && !$generalItems && !$serviceListings && !$eventListings && !$lostListings && !$dressListings): ?>
                                        <div class="set-table-onSale-block mt-2">
                                            <div class="text-center">
                                                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>&type=1"><button class="set-view-more-btn set-btn-block-view-more mt-0">View Details</button></a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>         
                                <!-- TAGS -->
                                <!-- <div class="set-tags-event-card set-card-overflow-tag-scroll set-tags-event-card mt-2">
                                    <?php if (!empty($post['service'])): ?>
                                        <?php foreach (explode(',', $post['service']) as $keyword): ?>
                                            <a href="javascript:void(0)" class="set-tag-event text-decoration-none"><?php echo $keyword; ?> </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div> -->
                                <div class="set-tags-event-card set-card-overflow-tag-scroll set-tags-event-card mt-2">
                                    <?php if (!empty($post['service'])): ?>
                                        <?php 
                                            $keywords = explode(',', $post['service']);
                                            $count = 0;
                                            foreach ($keywords as $keyword): 
                                                if ($count >= 3) break; // limit to 6 tags
                                        ?>
                                            <a href="javascript:void(0)" class="set-tag-event text-decoration-none">
                                                <?php echo trim($keyword); ?>
                                            </a>
                                        <?php 
                                            $count++;
                                            endforeach; 
                                        ?>
                                    <?php endif; ?>
                                </div>

                                
                            </div>
                        </div>
                    </div>
                </div>
                <!-- IF THERE IS ONE CARD IN LISTING -->
                 <?php if($totalCount < 2) {?>
                <!-- <div class="col-lg-6 mt-12">
                    <div class="set-card-categories set-custom-add-listing-card-main position-relative">
                        <a href="#" class="d-block">
                            <div class="set-round-box-add mx-auto">
                                <img src="assets/images/add.png" alt="img">
                            </div>
                            <h4 class="mt-3 f-20-g fw-semibold">Add New <?= $post['ad_type'];?></h4>
                            <p class="f-14-grey fw-medium">Create and share your listing</p>
                        </a>
                    </div>
                </div> -->
            <?php } } ?>
        <?php }
    }
}

/**
 * Generates a Deal card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('dealCard')) {
    function dealCard($post, $isDetail) {
        if (!empty($post)) { ?>
        <?php if(!$isDetail){ ?>
            <div class="col-12 col-md-6 col-lg-3 mt-3">
            <div class="product-container vpc-deal-card deal id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                <div class="product-image">
                    <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>" class="set-img-deal-card-org">
                        <img src="<?= count($post['post_images']) ? MEDIA_BASE_URL.@$post['post_images'][0]['image'] : 'assets/img/favicon.png'; ?>" width="125" height="120" alt="">
                    </a>
                </div>
                <div class="product-details">
                    <div class="product-description">
                        <p class="username d-flex align-items-center justify-content-between"><a href="user-details.php?id=<?php echo base64_encode($post['user']['id']); ?>">@<?= $post['user']['handle_name'] ?></a>&emsp;<span class="time-posted"><?= time_ago($post['created_at']) ?></span></p>
                        <h2 class="product-title f-16-gb fw-semibold"><a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>"><?php echo $post['title'] ?: ''; ?></a></h2>
                        <p class="product-summary">
                            <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>" class="f-12-g">
                            <?= mb_substr($post['info'], 0, 35) . (strlen($post['info']) > 35 ? '...' : '') ?>
                            </a>
                        </p>
                    </div>
                    <div class="product-pricing w-100 set-border-top-1 mt-1">
                        <div class="pricing w-100">
                            <h3 class="f-12-g d-flex align-items-center justify-content-between">Regular Price <del><?= formatPriceIntl($post['regular_price']) ?></del></h3>
                            <h2 class="f-14-bl d-flex align-items-center justify-content-between">Only <span class="fw-semibold"><?= formatPriceIntl($post['price']) ?></span></h2>
                        </div>
                    </div>
                    <div class="product-pricing mt-1">
                        <div class="d-flex gap-2 align-items-center">
                            <div class="product-share share-fn set-product-share-himish" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Deal" data-type="get_deals">
                                <img src="assets/images/share-new.png" alt="">
                                <span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span>
                            </div>
                            <div class="product-bookmark fav-post-fn set-product-bookmark-himish" data-post-id="<?php echo $post['id']; ?>" data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" data-post-type="2">
                                <img src="assets/images/<?php echo $post['i_fav'] ? 'tag-icon-fill.png' : 'tag-icon.png'; ?>" class="img-fluid" alt="">
                            </div>
                        </div>
                        <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                            || (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                            <div class="mt-3 dropdown action-item cmnt-action set-three-dots-org">
                                <button class="btn btn-link p-0 dropdown-toggle" type="button"
                                    id="listActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="assets/img/dots.png" alt="More Options" height="18px">
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="listActionsDropdown1">
                                    <li><a class="py-0 px-0" href="create-deal.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                    <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                                </ul>
                            </div>
                        <?php } ?>
                        <?php if(!empty($post['url'])){ ?>
                        <div class="buy-button">
                            <a href="<?= $post['url'] ?>" target="_blank"><button>Buy Now</button></a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            </div>
        <?php }else{ ?>
            <div class="view-details-container vpc-deal-card" id="lcp-<?php echo ($post['id']); ?>" data-post-id="<?php echo ($post['id']); ?>" data-i-view="<?php echo ($post['i_view']); ?>">
                <div class="product-container set-dealcard-detail-custom">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="product-img-wraper set-bg-left-detail-deal">
                                <div class="product-loc-wraper">
                                    <?php if(!empty($post['address'])){ ?>
                                    <div class="product-loc">
                                        <img src="<?= count($post['user']) ? MEDIA_BASE_URL.@$post['user']['image'] : 'assets/img/favicon.png'; ?>" class="rounded-pz" style="width: 20px; height: 20px"  alt="">
                                        <img src="assets/img/sale-loc.png" alt="">
                                        <span style="font-style: italic;"><?= $post['city'] ?>, <?= $post['state'] ?></span>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="product-image">
                                    <img src="<?= count($post['post_images']) ? MEDIA_BASE_URL.@$post['post_images'][0]['image'] : 'assets/img/favicon.png'; ?>" width="300" alt="" class="img-fluid" data-fancybox="gallery">
                                    <div class="product-hover-interactions set-min-h-270">
                                        <a href="javascript:void(0)" class="product-hover-share share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Deal" data-type="get_deals">
                                            <img src="assets/img/share.png" alt="">
                                            <span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span>
                                        </a>
                                        <a href="javascript:void(0)" class="product-hover-share fav-post-fn" 
                                        data-post-id="<?php echo $post['id']; ?>" 
                                        data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                        data-post-type="2">
                                            <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" class="img-fluid" alt="">
                                        </a>
                                        <a href="javascript:void(0)" class="product-hover-share post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                                            <img src="assets/img/cmnt.png" alt="">
                                            <span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span>
                                        </a>
                                        <a href="javascript:void(0)" class="product-hover-share like-post-fn" 
                                        data-post-id="<?php echo $post['id']; ?>" 
                                        data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                            <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                                            <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                                        </a>
                                        <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                                    || (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                                            <div class="dropdown action-item cmnt-action">
                                                <button class="btn btn-link p-0 dropdown-toggle" type="button"
                                                    id="listActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <img src="assets/img/dots.png" alt="More Options">
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="listActionsDropdown1">
                                                    <li><a class="py-0 px-0" href="create-deal.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                                    <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                                                </ul>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="view-product-details set-bg-right-detail-deal text-start">
                                <h4><a href="user-details.php?id=<?php echo base64_encode($post['user']['id']); ?>">@<?= $post['user']['handle_name'] ?> <span><img src="assets/images/clock-01.png" alt="time"><?= time_ago($post['created_at']) ?></span></a></h4>
                                <h2 class="product-title f-16-gb mt-12 fw-semibold"><a><?php echo $post['title'] ?: ''; ?></a></h2>
                                <p><?= $post['info'] ?></p>
                                <div class="view-product-details-feature">
                                    <!-- <ul></ul> -->
                                    <div class="product-pricing set-custom-pricing-deal-right">
                                        <div class="pricing text-start">
                                            <h3 class="reg-price">Reg Price <span><?= formatPriceIntl($post['regular_price']) ?></span></h3>
                                            <h2 class="discount-price">Only <span><?= formatPriceIntl($post['price']) ?></span></h2>
                                        </div>
                                        <?php if(!empty($post['url'])){ ?>
                                        <div class="buy-button">
                                            <a href="<?= $post['url'] ?>" target="_blank"><button>Buy</button></a>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        <?php } }
    }
}

/**
 * Generates a Deal Share card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('dealShareCard')) {
    function dealShareCard($post, $isDetail) {
        if (!empty($post)) {
            $isVideo = 0;
            if (!empty($post['post_images'])) {
                $media = $post['post_images'][0]['image'];
                $extension = pathinfo($media, PATHINFO_EXTENSION);
                $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv'];
                if (in_array(strtolower($extension), $videoExtensions)) {
                    $isVideo = 1;
                }
            }
        ?>
        <?php if(!$isDetail){ ?>
            <div class="product-hover-container dealS">
                <a href="post-details.php?id=<?php echo base64_encode($post['id']); ?>">
                <div class="product-hover-image">
                    <?php if($isVideo == 1){ ?>
                        <video class="deal-video" controls autoplay muted loop playsinline>
                            <source src="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" type="video/<?= $extension ?>">
                            Your browser does not support the video tag.
                        </video>
                    <?php }else{ ?>
                        <img src="<?= count($post['post_images']) ? MEDIA_BASE_URL.@$post['post_images'][0]['image'] : 'assets/img/favicon.png'; ?>" width="168" height="245" alt="" onerror="this.onerror=null;this.src=\'admin/assets/img/fav-icon.png\'" class="deal-image-tile">
                    <?php } ?>
                    <div class="hover-product-title">
                        <?= $post['title'] ?>
                    </div>
                </div>
                </a>
                <div class="product-hover-interactions">
                    <div class="product-hover-share share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Deal" data-type="get_deals">
                        <img src="assets/img/share.png" alt="">
                        <span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span>
                    </div>
                    <div class="product-hover-share fav-post-fn" 
                    data-post-id="<?php echo $post['id']; ?>" 
                    data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                    data-post-type="2">
                        <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" class="img-fluid" alt="">
                    </div>
                    <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                            || (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                        <div class="mt-3 dropdown action-item cmnt-action set-three-dots-org">
                            <button class="btn btn-link p-0 dropdown-toggle" type="button"
                                id="listActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="assets/img/dots.png" alt="More Options" height="18px">
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="listActionsDropdown1">
                                <li><a class="py-0 px-0" href="create-deal-share.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="product-hover-share post-comments-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="<?= $post['title'] ?>">
                        <img src="assets/img/cmnt.png" alt="">
                        <span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span>
                    </div>
                    <div class="product-hover-share like-post-fn" 
                        data-post-id="<?php echo $post['id']; ?>" 
                        data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                        <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                        <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                    </div>
                </div>
            </div>
        <?php }else{ ?>
            <div class="vDeaSh">
                <div class="view-details-container">
                    <div class="product-container">
                        <div class="view-share-Img-wraper">
                            <?php if($isVideo == 1){ ?>
                                <video width="450" height="600" controls>
                                    <source src="<?php echo MEDIA_BASE_URL.$post['post_images'][0]['image']; ?>" type="video/<?= $extension ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php }else{ ?>
                                <img src="<?= count($post['post_images']) ? MEDIA_BASE_URL.@$post['post_images'][0]['image'] : 'assets/img/favicon.png'; ?>" width="450" height="550" alt="" onerror="this.onerror=null;this.src=\'admin/assets/img/fav-icon.png\'" data-fancybox="gallery">
                            <?php } ?>
                            <div class="product-hover-interactions">
                                <a href="javascript:void(0)" class="product-hover-share">
                                    <img src="assets/img/share.png" alt="">
                                    <span><?php echo ($post['total_share'] == 0) ? '' : $post['total_share']; ?></span>
                                </a>
                                <a href="javascript:void(0)" class="product-hover-share fav-post-fn" 
                                data-post-id="<?php echo $post['id']; ?>" 
                                data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                data-post-type="2">
                                    <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" class="img-fluid" alt="">
                                </a>
                                <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                            || (isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['id'] == $post['user']['id'])){ ?>
                                    <div class="mt-3 dropdown action-item cmnt-action set-three-dots-org">
                                        <button class="btn btn-link p-0 dropdown-toggle" type="button"
                                            id="listActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img src="assets/img/dots.png" alt="More Options" height="18px">
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="listActionsDropdown1">
                                            <li><a class="py-0 px-0" href="create-deal-share.php?id=<?= base64_encode($post['id']) ?>"><p class="dropdown-item text-secondary">Edit</p></a></li>
                                            <li><p class="dropdown-item delete-pst-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <a href="javascript:void(0)" class="product-hover-share">
                                    <img src="assets/img/cmnt.png" alt="">
                                    <span><?php echo ($post['total_comments'] == 0) ? '' : $post['total_comments']; ?></span>
                                </a>
                                <a href="javascript:void(0)" class="product-hover-share like-post-fn" 
                                    data-post-id="<?php echo $post['id']; ?>" 
                                    data-liked="<?php echo $post['i_like'] ? '1' : '0'; ?>">
                                    <img src="assets/img/<?php echo $post['i_like'] ? 'lovefill.png' : 'love.png'; ?>" alt="Like">
                                    <span class="like-count"><?php echo ($post['total_likes'] == 0) ? '' : $post['total_likes']; ?></span>
                                </a>
                            </div>
                            <div class="view-share-post-author">
                                <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="mail-avatar-rounded-ds" alt="">
                                <a href="user-details.php?id=<?php echo base64_encode($post['user']['id']); ?>"><span class="author-name">@<?= $post['user']['handle_name'] ?></span></a>
                            </div>
                            <?php if(!empty($post['address'])){ ?>
                            <div class="shared-deals-location">
                                <img src="assets/img/location-white.png" alt="">
                                <span class="loc-name"><?= $post['address'] ?></span>
                            </div>
                            <?php } ?>
                            <h2><?= $post['title'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        <?php } }
    }
}


/**
 * Generates a company card layout.
 *
 */
if (!function_exists('renderCompanyCard')) {
    function renderCompanyCard($post, $guestMode) {
        if (!empty($post)) { ?>
        <div class="col-lg-12 mt-12">
            <div class="company-item  pxienio-2893-29392-<?= $post['id'] ?>" id="ccpp-<?= $post['id'] ?>">

            <!-- ribbon  -->
                <?php if(!empty($post['tab_label_option'])){ ?>
                    <div class="ribbon set-ribbon-company-org"><?= $post['tab_label_option'] ?></div>
                <?php } ?>
                
                <?php
                $listingImages = isset($post['company_cover_images']) ? array_filter($post['company_cover_images'], function ($img) {
                    return $img['img_type'] == 1; // Filter only images with img_type = 1
                }) : [];

                // Add MEDIA_BASE_URL to image paths
                $listingImages = array_map(function ($img) {
                    $img['image'] = MEDIA_BASE_URL . $img['image'];
                    return $img;
                }, $listingImages);

                // Placeholder image
                $placeholderImage = generateBase64Image($post['name']); // Change this to your actual placeholder image path

                // If no valid images are found, use the placeholder
                if (empty($listingImages)) {
                    $listingImages = [
                        ["image" => $placeholderImage]
                    ];
                }

                // Unique Carousel ID
                $carouselId = "companyCarousel_" . uniqid();
                ?>
                <!-- Company Image Carousel -->
                <!-- <div class="company-carousel">
                    <div id="<?php echo $carouselId; ?>" class="carousel slide" data-bs-ride="carousel">
                       
                        <?php if (count($post['company_cover_images'])) { ?>
                            
                            <div class="carousel-indicators">
                                <?php if (count($post['company_cover_images']) > 1): ?>
                                    <?php foreach (array_values($listingImages) as $index => $image) : ?>
                                        <button type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide-to="<?php echo $index; ?>" 
                                            class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                                            aria-label="Slide <?php echo $index + 1; ?>" 
                                            <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>></button>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php } ?>

                        
                        <div class="carousel-inner">
                            <?php foreach (array_values($listingImages) as $index => $image) : ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo $image['image']; ?>" class="img-fluid comp-carosl" alt="Company Image <?php echo $index + 1; ?>" data-fancybox="gallery-company-<?php echo $post['id']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div> -->

        
                <!-- Company Details -->
                <div class="company-details d-flex gap-3">
                    <div class="set-flex-grow">
                        <div class="d-flex gap-2">
                            <!-- Company Logo -->
                            <div class="company-logo">
                                <img src="<?php echo !empty($post['logo']) ? MEDIA_BASE_URL.$post['logo'] : generateBase64Image($post['name']); ?>" width="35" alt="Company Logo" class="img-fluid">
                                <!-- <img src="assets/img/profiile-dash.png" width="35" alt="Company Logo" class="img-fluid"> -->
                            </div>
                            <!-- Company Info Section -->
                            <div class="company-info">
                                <!-- Company Title -->                                           
                                <h3 class="f-12-gb fw-semibold">
                                <?php if (empty($guestMode)) { ?>
                                    <a href="company-details.php?id=<?= base64_encode($post['id']) ?>">
                                        <?= mb_substr($post['name'], 0, 20) . (strlen($post['name']) > 20 ? '...' : '') ?>
                                    </a>
                                    <a href="single-chat.php?id=<?= base64_encode($post['id']) ?>&type=<?= base64_encode(3) ?>">
                                        <?php if($post['owner_id'] == 0){ ?>
                                            <img src="assets/images/message.svg" alt="" data-bs-toggle="tooltip" data-bs-placement="top" title="Not Claimed">
                                        <?php }else{  ?>
                                            <?php if ($post['online'] == 0){ ?>
                                                <img src="assets/images/message-1.svg" alt="" data-bs-toggle="tooltip" data-bs-placement="top" title="Offline">
                                            <?php }else{ ?>
                                                <img src="assets/images/message-2.svg" alt="" data-bs-toggle="tooltip" data-bs-placement="top" title="Online">
                                            <?php } ?>
                                        <?php } ?>
                                    </a>
                                <?php } else { ?>
                                    <a href="javascript:void(0);" onclick="guestLoginModal()">
                                        <?= mb_substr($post['name'], 0, 20) . (strlen($post['name']) > 20 ? '...' : '') ?>
                                    </a>
                                    <a href="javascript:void(0);" onclick="guestLoginModal()">
                                        <img src="assets/img/chat1.png" alt="">
                                    </a>
                                <?php } ?>
                                </h3>
                                <div class="d-flex justify-content-between">
                                    
                                    <!-- Company Location -->
                                    <?php
                                    $branches = $post['company_branches'];
                                    $main_branch = $branches[0] ?? null; // First branch as main
                                    $other_branches = array_slice($branches, 1); // Remaining branches
                                    ?>
                                    <?php if(count($branches)>0){ ?>
                                    <div class="company-location">
                                        <img src="assets/images/location-05.png" alt="Location Icon">
                                        <span class="loc-name">
                                            <?php if ($main_branch && isset($main_branch['latitude'], $main_branch['longitude'])): ?>
                                                <a href="https://www.google.com/maps?q=<?php echo $main_branch['latitude']; ?>,<?php echo $main_branch['longitude']; ?>" target="_blank">
                                                    <?php echo $main_branch['city'] . ', ' . $main_branch['state']; ?>
                                                </a>
                                            <?php else: ?>
                                                No Location
                                            <?php endif; ?>
                                        </span>
                                        <?php if(count($other_branches)>0){ ?>
                                        <span class="other-loc" data-bs-toggle="dropdown" aria-expanded="true">
                                            +<?php echo count($other_branches); ?> Locations
                                        </span>
                                        <?php } ?>
                                        <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                        <?php foreach ($branches as $index => $branch): ?>
                                            <li>
                                                <a href="https://www.google.com/maps?q=<?php echo $branch['latitude']; ?>,<?php echo $branch['longitude']; ?>" target="_blank">
                                                    <strong>
                                                        <?php echo $index === 0 ? 'Primary' : (!empty($branch['name']) ? $branch['name'] : 'Unnamed Branch'); ?>
                                                    </strong><br>
                                                    <strong><i class="fa fa-map-marker"></i></strong> <?php echo $branch['address']; ?><br>
                                                    <strong><i class="fa fa-phone"></i></strong> <?php echo $branch['phone_numbers']; ?>
                                                </a>
                                            </li>
                                            <?php if ($index < count($branches) - 1): ?>
                                                <hr class="com-location"/>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php } ?>
                                    <!-- Company Rating -->
                                    <div class="company-rating">
                                        <?php 
                                            $rating = floatval($post['company_rating']);
                                            $starRating = floatval($rating) > 2 ? floatval($rating) : 0.0;
                                            ; // Ensure rating is a float
                                            $displayRating = $rating < 3 ? 1 : 5;
                                        ?>
                                        
                                        <p>
                                            <?php echo number_format($rating, 1); ?>
                                        </p>

                                        <div class="rateit mt-1" 
                                            data-rateit-value="<?php echo $starRating; ?>"
                                            data-rateit-min="0" data-rateit-max="<?= $displayRating ?>"
                                            data-rateit-readonly="true"
                                            <?php echo $rating < 3 ? 'data-rateit-resetable="false"' : ''; ?>>
                                        </div>

                                        <p>
                                            <?php echo $rating < 3 ? 'Not Yet Rated' : '' ?>
                                        </p>
                                    </div>
                                </div>
                        
                                <!-- Company Category -->
                                <?php
                                $categories = $post['company_categories'];
                                $main_category = $categories[0] ?? null; // First branch as main
                                $other_categories = array_slice($categories, 1); // Remaining branches
                                ?>
                                <?php if(count($categories)>0){ ?>
                                <div class="company-location set-company-category">
                                    <div class="company-category">
                                        <h4><?php echo $main_category['parentCategory']['name']; ?></h4>
                                        <p><?php echo @$main_category['parentCategory']['parentCategory']['name']; ?></p>
                                        <img src="assets/img/arrow-company-org.png" alt="">
                                    </div>
                                    <?php if(count($other_categories)>0){ ?>
                                    <span class="other-loc mt-1" data-bs-toggle="dropdown" aria-expanded="true">
                                        +<?php echo count($other_categories); ?> Categories
                                    </span>
                                    <?php } ?>
                                    <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                    <?php foreach ($categories as $index => $category): ?>
                                        <li>
                                            <div class="company-category">
                                                <h4><?php echo $category['parentCategory']['name']; ?></h4>
                                                <p><?php echo $category['parentCategory']['parentCategory']['name']; ?></p>
                                                <img src="assets/img/arr-com.png" alt="">
                                            </div>
                                        </li>
                                        <?php if ($index < count($categories) - 1): ?>
                                            <hr class="com-location"/>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php }else{ ?>
                                <div class="company-rating mb-2 set-company-category">
                                    <p class="ads-text">Not Categorized</p>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div>
                            <!-- Company Stats -->
                            <div class="company-stats">
                                <div class="company-posts">
                                    <a href="company-details.php?id=<?= base64_encode($post['id']) ?>&active=1">
                                        <p>Posts</p>
                                        <h3 class="<?= ($post['total_posts'] == 0) ? 'zero-count' : '' ?>">
                                            (<?= $post['total_posts'] ?>)
                                        </h3>
                                    </a>
                                </div>
                                <div class="company-posts">
                                    <a href="company-details.php?id=<?= base64_encode($post['id']) ?>">
                                        <p>Connections</p>
                                        <h3 class="<?= ($post['total_followers'] == 0) ? 'zero-count' : '' ?>">
                                            (<?= $post['total_followers'] ?>)
                                        </h3>
                                    </a>
                                </div>
                                <div class="company-posts">
                                    <a href="company-details.php?id=<?= base64_encode($post['id']) ?>&active=0">
                                        <p>Showcase</p>
                                        <h3 class="<?= ($post['total_showcase'] == 0) ? 'zero-count' : '' ?>">
                                            (<?= $post['total_showcase'] ?>)
                                        </h3>
                                    </a>
                                </div>
                                <div class="company-posts">
                                    <a href="company-details.php?id=<?= base64_encode($post['id']) ?>&active=3">
                                        <p>Recommends</p>
                                        <h3 class="<?= ($post['total_recommend_web'] == 0) ? 'zero-count' : '' ?>">
                                            (<?= ($post['total_recommend_web'] == 0) ? '0' : $post['total_recommend_web'] ?>)
                                        </h3>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Company Share Icons -->
                    <div class="company-share-icons set-company-share-icons-detail">
                            <!-- Like Button -->
                            <button class="share-item share-like action-rcmd-container" onclick="getCompanyRecommends({ post_container: 'ccpp-<?php echo ($post['id']); ?>' || '',company_id: '<?= addslashes($post['id']) ?>' || 0, company_name: '<?= addslashes($post['name']) ?>', total_recommends: '<?= addslashes($post['total_recommend_web']) ?>' || 0, container: '#pc-recommends-container', i_recommend: '<?= $post['i_recommend'] ?>' })">
                                <img src="assets/img/<?php echo $post['i_recommend'] ? 'like-fill' : 'like' ?>.png" alt="Recommend">
                                <span class="like-count action-rcmd-count"><?php echo ($post['total_recommend_web'] == 0) ? '' : $post['total_recommend_web']; ?></span>
                            </button>
                            <!-- Share Button -->
                            <button class="share-item share-button share-fn" data-id="<?php echo base64_encode($post['id']); ?>" data-title="Company" data-type="get_companies">
                                <img src="assets/img/share.png" alt="Share Icon">
                            </button>
                            <!-- Bookmark Button -->
                            <button class="share-item share-bookmark fav-company-fn" 
                                    data-company-id="<?php echo $post['id']; ?>" 
                                    data-favorited="<?php echo $post['i_fav'] ? '1' : '0'; ?>" 
                                    data-company-type="0">
                                <img src="assets/img/<?php echo $post['i_fav'] ? 'bookmarkfill.png' : 'bookmarkBlack.png'; ?>" width="18" alt="Fav">
                            </button>
                            <?php if((isset($_SESSION['hm_wb_auth_data']) && $_SESSION['hm_wb_auth_data']['account_type'] == 2) 
                                    || ($post['i_main_admin'] == 1)){ ?>
                                <a href="create-company.php?id=<?= base64_encode($post['id']) ?>"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                <i class="fa fa-trash text-danger" onclick="deleteCompanies('<?php echo base64_encode($post['id']); ?>')" aria-hidden="true"></i>
                            <?php } ?>
                    </div>
                </div>
                <!-- ribbon  -->
                <?php if(!empty($post['tab_label_option'])){ ?>
                <!-- <p class="company-ribbon">
                    <?= $post['tab_label_option'] ?>
                </p> -->
                <?php } ?>
            </div>
        </div>
        <?php }
    }
}

/**
 * Generates a company recommend card layout.
 *
 */
if (!function_exists('renderCompanyRecommendCard')) {
    function renderCompanyRecommendCard($post) {
        if (!empty($post)) { ?>
        <div class="notification-box">
            <div class="notification-left">
                <div class="unread-notification">
                </div>
                <div class="notification-imgBox">
                    <div class="notification-img set-rounded-if-user">
                        <img src="<?php echo !empty($post['user']['user']) ? MEDIA_BASE_URL.$post['user']['user'] : generateBase64Image($post['user']['name']); ?>" class="img-fluid w-100" alt="">
                    </div>
                    <div class="notification-content ms-3">
                        <h3 class="read-title justify-content-start align-items-start flex-column">
                            <strong class="text-capitalize">
                                <?= $post['user']['name'] ?>
                            </strong>
                            <span class="notification-date"><?= time_ago($post['created_at']) ?></span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        <?php }
    }
}


/**
 * Generates a post comment card layout.
 *
 */
if (!function_exists('renderPostCommentCard')) {
    function renderPostCommentCard($post,$user_id) {
        if (!empty($post)) { ?>
        <!-- single cmnt box  -->
        <div class="comment-box" id="parntPc<?= $post['id'] ?>">
            <div class="comment-author">
                <div class="cmnt-author-img">
                    <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="img-fluid w-100 rounded-pz" alt="">
                </div>
                <div class="cmnt-author-details">
                    <h4><?= $post['user']['name'] ?></h4>
                    <p><?= $post['comment'] ?></p>
                </div>
            </div>
            <div class="cmnt-action">
                <p><?= time_ago($post['created_at']) ?></p>
                <?php if($post['user_id']==$user_id){ ?>
                <div class="dropdown action-item cmnt-action">
                    <button class="btn btn-link p-0 dropdown-toggle" type="button"
                        id="postActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="assets/img/dots.png" alt="More Options">
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="postActionsDropdown1">
                        <li><p class="dropdown-item delete-pc-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php }
    }
}

/**
 * Generates a ad comment card layout.
 *
 */
if (!function_exists('renderAdCommentCard')) {
    function renderAdCommentCard($post,$user_id) {
        if (!empty($post)) { ?>
        <!-- single cmnt box  -->
        <div class="comment-box" id="parntAc<?= $post['id'] ?>">
            <div class="comment-author">
                <div class="cmnt-author-img">
                    <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="img-fluid w-100 rounded-pz" alt="">
                </div>
                <div class="cmnt-author-details">
                    <h4><?= $post['user']['name'] ?></h4>
                    <p><?= $post['comment'] ?></p>
                </div>
            </div>
            <div class="cmnt-action">
                <p><?= time_ago($post['created_at']) ?></p>
                <?php if($post['user_id']==$user_id){ ?>
                <div class="dropdown action-item cmnt-action">
                    <button class="btn btn-link p-0 dropdown-toggle" type="button"
                        id="postActionsDropdown1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="assets/img/dots.png" alt="More Options">
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="postActionsDropdown1">
                        <li><p class="dropdown-item delete-ac-fn" data-id="<?= $post['id'] ?>">Delete</p></li>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php }
    }
}


/**
 * Generates a notification card layout.
 *
 */
if (!function_exists('renderNotificationCard')) {
    function renderNotificationCard($post) {
        if (!empty($post)) { ?>
        <div class="notification-box">
            <div class="notification-left">
                <?php if($post['is_seen']==0){ ?>
                <div class="unread-notification">
                    <img src="assets/img/unred.png" class="w-100 img-fluid" alt="">
                </div>
                <?php } ?>
                <div class="notification-imgBox <?= ($post['is_seen']==0) ? 'sn_ntf' : '' ?>">
                    <?php echo getNotificationContentByCode($post,$post['code']); ?>
                </div>
            </div>
        </div>
        <?php }
    }
}

/**
 * Generates a notification content.
 *
 */
if (!function_exists('getNotificationContentByCode')) {
    function getNotificationContentByCode($post, $code) {
        if (!empty($post)) { 
            // Default values
            $headerMessage = $post['message'];
            $bodyText = "";
            $button1 = "";
            $button2 = "";
            $redirectUrl = "#"; // Default URL

            // Modify based on code
            if ($code == 61 || $code == 63) { // Ads Expire
                $bodyText = $post['_metadata']['name'] ?? '';
                if($post['_metadata']){
                    $button2 = '<a href="create-ad.php?ad_id='.base64_encode($post['request_id']).'"><button class="cancel-btn">Extend Ad Time</button></a>';
                }
            } elseif ($code == 58 || $code == 28 || $code == 1 || $code == 16 || $code == 18 || $code == 8999 || $code == 666) { // LikePost
                $button2 = '<a href="post-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 31 || $code == 12 || $code == 30) { // newListing
                $button2 = '<a href="post-details.php?id='.base64_encode($post['request_id']).'&type=1"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 10) { // LikeAd
                $button2 = '<a href="ad-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 600) { // AssociationRequest
                if($post['_metadata']){
                    $button2 = '<a href="company-details.php?id='.base64_encode($post['_metadata']['company']['id']).'&active=2&activeReq=1"><button class="cancel-btn">View</button></a>';
                }
            } elseif ($code == 601) { // AssociationRequestAccepted
                if($post['_metadata']){
                    $button2 = '<a href="company-details.php?id='.base64_encode($post['_metadata']['company']['id']).'&active=2"><button class="cancel-btn">View</button></a>';
                }
            } elseif ($code == 14) { // Community Request
                if($post['_metadata']){
                    $button1 = '<button type="button" class="cancel-btn acceptRejectCommunityReq" data-id="'.$post['request_id'].'" data-community-id="'.$post['_metadata']['id'].'" data-status="2">Reject</button>';
                    $button2 = '<button type="button" class="cancel-btn acceptRejectCommunityReq" data-id="'.$post['request_id'].'" data-community-id="'.$post['_metadata']['id'].'" data-status="1">Accept</button>';
                }
            } elseif ($code == 603) { // ConnectionRequest
                $button1 = '<button type="button" class="cancel-btn ar-cnt-fn" data-request-id="'.$post['request_id'].'" data-type="2">Reject</button>';
                $button2 = '<button type="button" class="cancel-btn ar-cnt-fn" data-request-id="'.$post['request_id'].'" data-type="1">Accept</button>';
            } elseif ($code == 11) { // RecommendCompany
                $button2 = '<a href="company-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 55) { // Posted for company

                $button1 = '<button type="button" class="cancel-btn ar-pst-fn" data-request-id="'.$post['request_id'].'" data-type="2">Reject</button>';

                $button2 = '<button type="button" class="cancel-btn ar-pst-fn" data-request-id="'.$post['request_id'].'" data-type="1">Accept</button>';

                // Extract URL from message
                preg_match('/(https?:\/\/[^\s]+)/', $headerMessage, $matches);

                // If a link is found, wrap it in <a> tag
                if (!empty($matches[0])) {
                    $link = $matches[0];

                    // Prepare the anchor tag
                    $bodyText = '<a class="text-primary" href="' . $link . '">' . $link . '</a>';

                    // Remove the link from the header message
                    $headerMessage = str_replace($link, '', $headerMessage);
                }
            } elseif ($code == 66 || $code == 6) { // owner of company
                if($code == 6){
                    $button2 = '<a href="company-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
                }else{
                    $button2 = '<a href="create-ad.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">Continue To Create Ad</button></a>';
                }
            } elseif ($code == 54) { // post available in feed
                $button2 = '<a href="post-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 1190) { // recommend appreciated
                $button2 = '<a href="company-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 603) { // CompanyFollow
                $button1 = '<button class="cancel-btn">Reject</button>';
                $button2 = '<button class="cancel-btn">Accept</button>';
            } elseif ($code == 3 || $code == 5) { // PostComment , listingComment
                $redirectUrl = "post-details.php?id=".base64_encode($post['request_id']);
                $button2 = '<a href="post-details.php?id='.base64_encode($post['request_id']).'&trigger=comments"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 99) { // AdComment
                $redirectUrl = "ad-details.php?id=".base64_encode($post['request_id']);
                $button2 = '<a href="ad-details.php?id='.base64_encode($post['request_id']).'&trigger=comments"><button class="cancel-btn">View</button></a>';
            } elseif ($code == 64 || $code == 65) { // AdLive

                $button2 = '<a href="ad-details.php?id='.base64_encode($post['request_id']).'"><button class="cancel-btn">View</button></a>';

                // Extract URL from message
                preg_match('/(https?:\/\/[^\s]+)/', $headerMessage, $matches);

                // If a link is found, wrap it in <a> tag
                if (!empty($matches[0])) {
                    $link = $matches[0];

                    // Prepare the anchor tag
                    $bodyText = '<a class="text-primary" href="' . $link . '">' . $link . '</a>';

                    // Remove the link from the header message
                    $headerMessage = str_replace($link, '', $headerMessage);
                }
            }
            ?>
            
            <div class="notification-img nbg<?= $code ?> rid<?= $post['id'] ?>">
                <img src="<?php echo !empty($post['user']['image']) ? MEDIA_BASE_URL.$post['user']['image'] : generateBase64Image($post['user']['name']); ?>" class="noti-avatar-rounded-ds" alt="">
            </div>
            <div class="notification-content" onclick="window.location.href='<?= $redirectUrl ?>'">
                <!-- Header -->
                <h3 class="mt-2">
                    <?= $headerMessage ?> 
                    <span class="notification-date"><?= time_ago($post['created_at']) ?></span>
                </h3>

                <!-- Body (Optional) -->
                <?php if (!empty($bodyText)) { ?>
                    <p><?= $bodyText ?></p>
                <?php } ?>

                <!-- Footer Buttons (Optional) -->
                <?php if (!empty($button1) || !empty($button2)) { ?>
                    <div class="notification-btns">
                        <?php if (!empty($button1)) { ?>
                            <?= $button1 ?>
                        <?php } ?>
                        <?php if (!empty($button2)) { ?>
                            <?= $button2 ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php }
    }
}

/**
 * Generates a connections content.
 *
 */
if (!function_exists('renderConnectionCard')) {
    function renderConnectionCard($post,$id) {
        if (!empty($post)) { ?>
            <div class="connection-card-wrapper mt-2">
                <div class="connection-card">
                    <div class="connection-avatar">
                        <?php if($id==$post['user_id']){ ?>
                            <img src="<?php echo !empty($post['logo']) ? MEDIA_BASE_URL.$post['logo'] : generateBase64Image($post['company_name']); ?>" class="img-fluid w-100" alt="image">
                        <?php }else{ ?>
                            <img src="<?php echo !empty($post['image2']) ? MEDIA_BASE_URL.$post['image2'] : generateBase64Image($post['name']); ?>" class="img-fluid w-100" alt="image">
                        <?php } ?>
                    </div>
                    <div class="connection-info ms-2">
                        <?php if($id==$post['user_id']){ ?>
                            <h3 class="connection-name"><?= $post['company_name'] ?></h3>
                        <?php }else{ ?>
                            <h3 class="connection-name"><?= $post['name'] ?></h3>
                            <a href="company-details.php?id=<?= base64_encode($post['company_id']) ?>" class="connection-username">@<?= $post['company_name'] ?></a>
                        <?php } ?>
                    </div>
                </div>
                <div class="connection-actions" data-mraun="<?= $post['id']?>">
                    <img src="assets/img/<?= ($id==$post['user_id']) ? 'connect-right.png' : 'connect.png' ?>" alt="Connection Icon" class="connection-icon">
                    <?php if($id==$post['user_id'] && $post['status']==0){ ?>
                        <a href="javascript:void(0)" class="connection-disconnect ar-cnt-fn" data-request-id="<?= $post['id'] ?>" data-type="2">Pending</a>
                    <?php }else{ ?>
                        <?php if($post['status']==1){ ?>
                            <a href="#" class="connection-disconnect ar-cnt-fn" data-request-id="<?= $post['id'] ?>" data-type="2">Disconnect</a>
                        <?php }else{ ?>
                            <i class="fa fa-check-circle text-primary fa-2x ar-cnt-fn" data-request-id="<?= $post['id'] ?>" data-type="1"></i>
                            <i class="fa fa-times-circle text-danger fa-2x ar-cnt-fn" data-request-id="<?= $post['id'] ?>" data-type="2"></i>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php }
    }
}

/**
 * Render blank card / no data
 *
 */
if (!function_exists('renderNoDataCard')) {
    function renderNoDataCard($title, $button, $description = '') { ?>
        <div class="no-ads-wrapper">
            <div class="no-ads-wrapper-image">
                <img src="assets/img/bi_image-fill.svg" alt="" />
            </div>
            <p><?php echo $title; ?></p>
            <span><?php echo $description; ?></span>
            <?php if(!empty($button)){ echo $button; } ?>
        </div>
    <?php }
}
/**
 * Render blank card / no data Posts / listing / Deals
 *
 */
if (!function_exists('renderNoDataCardPosts')) {
    function renderNoDataCardPosts($title, $button, $description = '') { ?>
        <div class="no-ads-wrapper-posts">
            <div class="no-ads-wrapper-image">
                <img src="assets/img/bi_image-fill.svg" alt="" />
            </div>
            <p><?php echo $title; ?></p>
            <span><?php echo $description; ?></span>
            <?php if(!empty($button)){ echo $button; } ?>
        </div>
    <?php }
}

/**
 * Read Branch Link Data
 *
 */
if (!function_exists('getBranchLinkData')) {
    function getBranchLinkData($branchUrl) {
        $apiUrl = "https://api2.branch.io/v1/url?url=" . urlencode($branchUrl) . "&branch_key=" . BRANCH_KEY;

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Handle response
        if ($httpCode == 200) {
            return json_decode($response, true);
        } else {
            return ["error" => "Failed to fetch data", "http_code" => $httpCode, "response" => $response];
        }
    }
}



/**********
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 *  S A L E S
 * 
 * 
 *  P O R T A L
 * 
 * 
 * 
 * 
 */


/**
 * Generates a commission card row for either Post or Community type.
 *
 * @param array $post The post/community data.
 * @param int   $type 0 = Post Referral, 1 = Community
 * @return string
 */
if (!function_exists('renderCommissionCardSales')) {
    function renderCommissionCardSales($post, $type = 0) {
        if (empty($post) || !is_array($post)) {
            return '<tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No data found
                        </td>
                    </tr>';
        }

        ob_start();

        // Referral layout
        $refLink   = $post['referral_link'] ?? [];
        $community = $refLink['community'] ?? [];
        $postData  = $refLink['post'] ?? [];

        $name = ($refLink['type'] == 0) ? ($postData['title'] ?? 'Deleted Post') : ($community['name'] ?? 'Deleted Community');
        $created = !empty($post['created_at']) ? date("M d, Y", strtotime($post['created_at'])) : '-';
        $commission = !empty($post['commission']) ? "$" . number_format($post['commission'], 2) : "$0.00";

        // Status
        $statusClass = "bg-gray-100 text-gray-800";
        $statusIcon  = "fa-envelope";
        $statusText  = "Not Opened";

        if ($post['is_convert'] === 1) {
            $statusClass = "bg-green-100 text-green-800";
            $statusIcon  = "fa-check";
            $statusText  = "Converted";
        } else {
            $statusClass = "bg-orange-100 text-orange-800";
            $statusIcon  = "fa-clock";
            $statusText  = "Pending";
        }

        if ($type == 0) { // post ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium <?= !empty($postData['title']) ? 'text-gray-900' : 'text-red-900' ?>"><?= htmlspecialchars($name) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    -
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $created ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="text-green-600 font-medium">Yes</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= ($post['is_convert']) ? '<span class="text-green-600 font-medium">Yes</span>' : '<span class="text-blue-600 font-medium">No</span>' ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm <?= $post['commission'] > 0 ? 'font-medium text-green-600' : 'text-gray-500' ?>">
                    <?= $commission ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                        <i class="fa-solid <?= $statusIcon ?> mr-1"></i>
                        <?= $statusText ?>
                    </span>
                </td>
            </tr>
            <?php
        } else { ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-medium <?= !empty($community['name']) ? 'text-gray-900' : 'text-red-900' ?>"><?= htmlspecialchars($name ?? '', ENT_QUOTES) ?></div>
                    <div class="text-sm text-gray-500">Created <?= htmlspecialchars($created ?? '', ENT_QUOTES) ?></div>
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="text-blue-600 font-medium"><?= htmlspecialchars(displayWithFallback(isset($post['community_member']) ? $post['community_member'] : 0), ENT_QUOTES) ?></span>
                    <span class="text-gray-500 text-sm">via your invite</span>
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php 
                        $totalAds = (isset($post['total_paid_ad']) ? (int)$post['total_paid_ad'] : 0) 
                                + (isset($post['total_unpaid_ad']) ? (int)$post['total_unpaid_ad'] : 0); 
                    ?>
                    <?= htmlspecialchars(displayWithFallback($totalAds), ENT_QUOTES) ?>
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= htmlspecialchars(displayWithFallback(isset($post['total_paid_ad']) ? $post['total_paid_ad'] : 0), ENT_QUOTES) ?>
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= htmlspecialchars(displayWithFallback(isset($post['community_commision']) ? $post['community_commision'] : 0), ENT_QUOTES) ?>%
                </td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?= (isset($post['commission']) && $post['commission'] > 0) ? 'text-green-600' : 'text-gray-500' ?>">
                    <?= htmlspecialchars($commission ?? 0, ENT_QUOTES) ?>
                </td>
            </tr>
            <?php
        }

        return ob_get_clean();
    }
}

/**
 * Generates a post card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('renderPostCardSales')) {
    function renderPostCardSales($post, $type) {
        if (!empty($post)) {

        if($type == 1 && !empty($post['community'])){ ?>
            <tr id="community-<?= htmlspecialchars($post['community']['id'] ?? '0', ENT_QUOTES) ?>">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 -100 rounded-lg flex items-center justify-center mr-3">
                            <img src="<?php echo !empty($post['community']['image']) ? MEDIA_BASE_URL.$post['community']['image'] : generateBase64Image($post['community']['name']); ?>" class="mail-avatar-rounded" alt="">
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($post['community']['name'] ?? '', ENT_QUOTES) ?></div>
                            <div class="text-sm text-gray-500">
                                <?= htmlspecialchars(mb_strimwidth($post['community']['description'] ?? '', 0, 35, '...'), ENT_QUOTES) ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center text-sm text-gray-600">
                        <?php if (!empty($post['community']['address'])): ?>
                            <i class="mr-1 fa fa-map-marker"></i>
                            <?= htmlspecialchars($post['community']['address'], ENT_QUOTES) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <?php $isPrivate = $post['community']['is_private'] ?? 0; ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?= $isPrivate == 0 ? 'green' : 'pink' ?>-100 text-<?= $isPrivate == 0 ? 'green' : 'pink' ?>-800">
                        <i class="mr-1 fa <?= $isPrivate == 0 ? 'fa-globe' : 'fa-lock' ?>"></i>
                        <?= $isPrivate == 0 ? 'Public' : 'Private' ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium <?= ($post['community']['from_invite'] == 0) ? 'text-red-600' : 'text-gray-900' ?>">
                        <?= htmlspecialchars(displayWithFallback($post['community_member'] ?? 0), ENT_QUOTES) ?>
                    </span>
                    <span class="text-xs text-gray-500 block"> <?= htmlspecialchars(displayWithFallback($post['community']['from_invite']), ENT_QUOTES) ?> via your invite</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars(displayWithFallback($post['total_paid_ad'] + $post['total_unpaid_ad']), ENT_QUOTES) ?></span>
                    <span class="text-xs text-green-600 block"><?= htmlspecialchars(displayWithFallback($post['total_paid_ad']), ENT_QUOTES) ?> paid</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                    <?= ($post['commission'] == 0) ? 'text-gray-500' : 'text-green-600' ?>">
                    $<?= htmlspecialchars(displayWithFallback($post['commission']), ENT_QUOTES) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-3 text-gray-600 text-lg">
                        <!-- INvite -->
                        <a href="invite-links.php?type=1&id=<?= base64_encode($post['community']['id']) ?>">
                            <button 
                                class="bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center space-x-1" 
                            >
                                <i class="text-xs fa fa-link"></i>
                                <span>Invite</span>
                            </button>
                        </a>

                        <!-- Copy -->
                        <button 
                            type="button"
                            class="hover:text-pink-600 relative group"
                            onclick="copyToClipboard('<?= htmlspecialchars($post['share_link'] ?? '', ENT_QUOTES) ?>')"
                        >
                            <i class="fa fa-copy"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Copy Referral Link</span>
                        </button>

                        <!-- Edit -->
                        <button 
                            onclick="window.location.href='create-community.php?id=<?= base64_encode($post['community']['id']) ?>'" 
                            class="hover:text-green-600 relative group">
                            <i class="fa fa-edit"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Edit</span>
                        </button>

                        <!-- Delete -->
                        <button 
                            type="button"
                            class="hover:text-red-600 relative group delete-community-fn" data-id="<?= $post['community']['id'] ?>">
                            <i class="fa fa-trash"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        <?php }else if($type == 0 && !empty($post['post'])){ ?>
            <tr id="post-<?= htmlspecialchars($post['post']['id'] ?? '0', ENT_QUOTES) ?>">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-10 h-10 -100 rounded-lg flex items-center justify-center mr-3">
                            <img src="<?php echo !empty($post['post']['image']) ? MEDIA_BASE_URL.$post['post']['image'] : generateBase64Image($post['post']['title']); ?>" class="mail-avatar-rounded" alt="">
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?= htmlspecialchars(mb_strimwidth($post['post']['title'] ?? '-', 0, 40, '...'), ENT_QUOTES) ?></div>
                            <div class="text-sm text-gray-500">
                                <?= htmlspecialchars(mb_strimwidth($post['post']['service'] ?? '-', 0, 35, '...'), ENT_QUOTES) ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center text-sm text-gray-600">
                        <?php if (!empty($post['post']['community'])): ?>
                            <?= htmlspecialchars($post['post']['community'], ENT_QUOTES) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600"><?= time_ago($post['created_at']) ?></td>

                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= htmlspecialchars(displayWithFallback($post['total_click']), ENT_QUOTES) ?></td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600"><?= htmlspecialchars(displayWithFallback($post['total_convert']), ENT_QUOTES) ?></td>

                <td class="px-6 py-4 whitespace-nowrap">
                    <?php if ($post['status'] == 1): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fa-solid fa-check mr-1"></i>
                            Active
                        </span>
                    <?php elseif ($post['status'] == 2): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fa-solid fa-pencil mr-1"></i>
                            Draft
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fa-solid fa-xmark mr-1"></i>
                            Inactive
                        </span>
                    <?php endif; ?>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-3 text-gray-600 text-lg">

                        <!-- INvite -->
                        <a href="invite-posts.php?type=0&id=<?= base64_encode($post['link_id']) ?>">
                            <button 
                                class="bg-blue-600 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center space-x-1" 
                            >
                                <i class="text-xs fa fa-link"></i>
                                <span>Invite</span>
                            </button>
                        </a>

                        <!-- View -->
                        <button 
                            type="button"
                            class="hover:text-blue-600 relative group upcomingFeatureTrigger">
                            <i class="fa fa-eye"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">View</span>
                        </button>

                        <!-- Edit -->
                        <button 
                            type="button"
                            class="hover:text-green-600 relative group upcomingFeatureTrigger">
                            <i class="fa fa-edit"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Edit</span>
                        </button>

                        <!-- Copy -->
                        <button 
                            type="button"
                            class="hover:text-pink-600 relative group"
                            onclick="copyToClipboard('<?= htmlspecialchars($post['share_link'] ?? '', ENT_QUOTES) ?>')"
                        >
                            <i class="fa fa-copy"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Copy Referral Link</span>
                        </button>

                        <!-- Delete -->
                        <button 
                            type="button"
                            class="hover:text-red-600 relative group delete-post-referral-fn" data-id="<?= $post['link_id'] ?>">
                            <i class="fa fa-trash"></i>
                            <span class="absolute bottom-full mb-1 hidden group-hover:block text-xs bg-gray-800 text-white px-2 py-1 rounded">Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        <?php } }
    }
}

/**
 * Generates a existing post card layout for dropdown.
 *
 * @param array $post The post data.
 */
if (!function_exists('renderExistingPostDropdownSales')) {
    function renderExistingPostDropdownSales($post) {
        if (!empty($post)) { 
            $images = $post['post_images'] ?? [];
            $lastIndex = !empty($images) ? array_key_last($images) : null;
            $lastImage = $lastIndex !== null ? $images[$lastIndex]['image'] : 'default.png';
            ?>
            
            <label class="flex items-center p-3 bg-white border rounded-xl shadow-sm cursor-pointer hover:bg-blue-50 transition group relative">
                <!-- Hidden Checkbox (radio-like behavior with unselect support) -->
                <input 
                    type="checkbox" 
                    name="selected_post" 
                    value="<?= $post['id'] ?>" 
                    class="hidden peer post-selector"
                />
                
                <!-- Thumbnail -->
                <img src="<?php echo MEDIA_BASE_URL.$lastImage; ?>" 
                     alt="Post Thumbnail" 
                     class="w-16 h-16 rounded-lg object-cover mr-4" />
                
                <!-- Post Details -->
                <div>
                    <h4 class="font-semibold text-gray-900 group-hover:text-blue-600"><?= $post['title'] ?></h4>
                    <p class="text-sm text-gray-500"><?= $post['company'] ?></p>
                </div>

                <!-- Selection Ring -->
                <span class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-blue-500 pointer-events-none"></span>
            </label>
            
        <?php  
        } 
    }
}

/**
 * Generates a message template card layout.
 *
 * @param array $post The post data.
 */
if (!function_exists('renderMessageTemplateDropdownSales')) {
    function renderMessageTemplateDropdownSales($post) {
        if (!empty($post)) { ?>            
            <div id="template-card-1" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fa-brands fa-whatsapp text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($post['title'] ?? '', ENT_QUOTES) ?></h3>
                            <p class="text-xs text-gray-500">Last used: <?= time_ago($post['updated_at']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button type="button" class="p-1 text-gray-400 hover:text-blue-600 upcomingFeatureTrigger">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <a href="create-template.php?id=<?= base64_encode($post['id']) ?>">
                            <button type="button" class="p-1 text-gray-400 hover:text-blue-600">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                        </a>
                        <button type="button" class="p-1 text-gray-400 hover:text-red-600 delete-template-fn" data-id="<?= $post['id'] ?>" data-type="<?= $post['type'] ?>">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 mb-4 show-more-container">
                    <?php 
                        $message = $post['message'] ?? '';
                        $plainText = strip_tags($message);
                        $limit = 120;

                        $isLong = mb_strlen($plainText) > $limit;
                        $shortText = mb_strimwidth($plainText, 0, $limit, '...');
                    ?>
                    
                    <p class="text-sm text-gray-700">
                        <span class="short-text"><?= htmlspecialchars($shortText, ENT_QUOTES) ?></span>
                        <?php if ($isLong): ?>
                            <span class="full-text hidden"><?= htmlspecialchars($plainText, ENT_QUOTES) ?></span>
                            <button type="button" class="toggle-btn text-blue-500 ml-2 text-xs">Show More</button>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span class="flex items-center space-x-1">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Used <?= $post['is_used'] ?> times</span>
                    </span>
                    <?php if ($post['status'] == 1): ?>
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full">Active</span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full">Inactive</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
        } 
    }
}

/**
 * Render blank card / no data Posts / listing / Deals
 *
 */
if (!function_exists('renderNoDataCardPostsSales')) {
    function renderNoDataCardPostsSales($title, $button, $description = '', $icon='frown') { ?>
        <div id="empty-state" class="min-h-[600px] flex items-center justify-center">
            <div class="text-center max-w-md mx-auto">
                <!-- Illustration -->
                <div class="mb-8">
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-primary to-secondary rounded-full flex items-center justify-center mb-6 relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary to-secondary rounded-full animate-pulse opacity-20"></div>
                        <i class="fa-solid fa-<?= $icon ?> text-white text-4xl"></i>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="relative">
                        <div class="absolute -top-16 -left-8 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center animate-bounce" style="animation-delay: 0.5s;">
                            <i class="fa-solid fa-frown text-blue-500"></i>
                        </div>
                        <div class="absolute -top-12 -right-6 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center animate-bounce" style="animation-delay: 1s;">
                            <i class="fa-solid fa-frown text-green-500"></i>
                        </div>
                        <div class="absolute -top-20 right-8 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center animate-bounce" style="animation-delay: 1.5s;">
                            <i class="fa-solid fa-frown text-purple-500"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= $title ?></h2>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                       <?= $description ?>
                    </p>
                </div>
                
                <!-- CTA Button -->
                <div class="space-y-4">
                    <?= $button ?>
                </div>
            </div>
        </div>
    <?php }
}