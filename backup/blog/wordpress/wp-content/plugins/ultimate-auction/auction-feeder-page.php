<?php
//auction listing page - pagination
$page_num = 20;
function auction_pagination($pages = '', $range = 2, $paged)
{  
     $showitems = ($range * 2)+1;  

     if(empty($paged)) $paged = 1;

     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         
         if(!$pages)
         {
             $pages = 1;
         }
     }   

     if(1 != $pages)
     {
         echo "<div class='pagination'>";
         printf('<span>'.__('Page %1$s of %2$s', 'wdm-ultimate-auction').'</span>', $paged, $pages);
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo;</a>";
         if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";

         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
             }
         }

         if ($paged < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a>";  
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>&raquo;</a>";
         echo "</div>\n";
     }
}

$wdm_auction_array=array();

if (get_query_var('paged')) { $paged = get_query_var('paged'); }
elseif (get_query_var('page')) { $paged = get_query_var('page'); }
else { $paged = 1; }

$args = array(
		'posts_per_page'=> $page_num,
		'post_type'	=> 'ultimate-auction',
		'auction-status'  => 'live',
		'post_status' => 'publish',
		'paged' => $paged,
		'suppress_filters' => false
		);

	do_action('wdm_ua_before_get_auctions');

	$wdm_auction_array = get_posts($args);
	
	do_action('wdm_ua_after_get_auctions');
        
		$show_content = '';
		$show_content = apply_filters('wdm_ua_before_auctions_listing', $show_content);
		echo $show_content;
		?>
		
		<div class="wdm-auction-listing-container">
			<ul class="wdm_auctions_list">
			<li class="auction-list-menus">
				<ul>
					<li class="wdm-apn auc_single_list"><strong><?php _e('Product', 'wdm-ultimate-auction');?></strong></li>
					<li class="wdm-apt auc_single_list"><strong></strong></li>
					<li class="wdm-app auc_single_list"><strong><?php _e('Current Price', 'wdm-ultimate-auction');?></strong></li>
					<li class="wdm-apb auc_single_list"><strong><?php _e('Bids Placed', 'wdm-ultimate-auction');?></strong></li>
					<li class="wdm-ape auc_single_list"><strong><?php _e('Ending', 'wdm-ultimate-auction');?></strong></li>
				</ul>
			</li>
			
		<?php
		//auction listing page container
		foreach($wdm_auction_array as $wdm_single_auction){
			global $wpdb;
			$query="SELECT MAX(bid) FROM ".$wpdb->prefix."wdm_bidders WHERE auction_id =".$wdm_single_auction->ID;
			$curr_price = $wpdb->get_var($query);
			?>
			<li class="wdm-auction-single-item">
				<a href="<?php echo get_permalink().$set_char."ult_auc_id=".$wdm_single_auction->ID; ?>" class="wdm-auction-list-link">
				<ul>
			<li class="wdm-apn auc_single_list">
				<div  class="wdm_single_auction_thumb">
				   <?php $vid_arr = array('mpg', 'mpeg', 'avi', 'mov', 'wmv', 'wma', 'mp4', '3gp', 'ogm', 'mkv', 'flv');
					$auc_thumb = get_post_meta($wdm_single_auction->ID, 'wdm_auction_thumb', true);
					$imgMime = wdm_get_mime_type($auc_thumb); 
					$img_ext = end(explode(".",$auc_thumb));
					
					if(strstr($imgMime, "video/") || in_array($img_ext, $vid_arr) || strstr($auc_thumb, "youtube.com") || strstr($auc_thumb, "vimeo.com")){
					$auc_thumb = plugins_url('img/film.png', __FILE__);	
				}
				if(empty($auc_thumb)){$auc_thumb = plugins_url('img/no-pic.jpg', __FILE__);}
				?>
				<img src="<?php echo $auc_thumb; ?>" width="100" height="80" alt="<?php echo $wdm_single_auction->post_title; ?>" />
				</div>
			</li>
			
			<li class="wdm-apt auc_single_list">
				<div class="wdm-auction-title"><?php echo $wdm_single_auction->post_title; ?></div>
			</li>
			
			<li class="wdm-app auc_single_list auc_list_center">
			<span class="wdm-auction-price wdm-mark-green">
			<?php
			$cc = substr(get_option('wdm_currency'), -3);
			$ob = get_post_meta($wdm_single_auction->ID, 'wdm_opening_bid', true);
			$bnp = get_post_meta($wdm_single_auction->ID, 'wdm_buy_it_now', true);
			
			if((!empty($curr_price) || $curr_price > 0) && !empty($ob))
				echo $cc ." ". sprintf("%.2f", $curr_price);
			elseif(!empty($ob))
				echo $cc ." ".sprintf("%.2f", $ob);
			elseif(empty($ob) && !empty($bnp))
				printf(__('Buy at %s %.2f', 'wdm-ultimate-auction'), $cc, $bnp);
				?>
			</span>
			</li>
			
			<li class="wdm-apb auc_single_list auc_list_center">
			<?php
			$get_bids = "SELECT COUNT(bid) FROM ".$wpdb->prefix."wdm_bidders WHERE auction_id =".$wdm_single_auction->ID;
			$bids_placed = $wpdb->get_var($get_bids);
			if(!empty($bids_placed) || $bids_placed > 0)
				echo "<span class='wdm-bids-avail wdm-mark-normal'>".$bids_placed."</span>";
			else
				echo "<span class='wdm-no-bids-avail wdm-mark-red'>".__('No bids placed', 'wdm-ultimate-auction')."</span>";
			?>
			</li>
			
			<li class="wdm-ape auc_single_list auc_list_center">
				<?php
				$now = mktime(); 
				$ending_date = strtotime(get_post_meta($wdm_single_auction->ID, 'wdm_listing_ends', true));
				$act_trm = wp_get_post_terms($wdm_single_auction->ID, 'auction-status',array("fields" => "names"));
				
				$seconds = $ending_date - $now;
				
				if(in_array('expired',$act_trm))
				{
					echo "<span class='wdm-mark-red'>".__('Expired', 'wdm-ultimate-auction')."</span>";
				}
				elseif($seconds > 0 && !in_array('expired',$act_trm))
				{
					$days = floor($seconds / 86400);
					$seconds %= 86400;

					$hours = floor($seconds / 3600);
					$seconds %= 3600;

					$minutes = floor($seconds / 60);
					$seconds %= 60;
					
					if($days > 1)
						echo "<span class='wdm-mark-normal'>". $days ." ".__('days', 'wdm-ultimate-auction')."</span>";
					elseif($days == 1)
						echo "<span class='wdm-mark-normal'>". $days ." ".__('day', 'wdm-ultimate-auction')."</span>";	
					elseif($days < 1)
					{
						if($hours > 1)
							echo "<span class='wdm-mark-normal'>". $hours ." ".__('hours', 'wdm-ultimate-auction')."</span>";
						elseif($hours == 1)
							echo "<span class='wdm-mark-normal'>". $hours ." ".__('hour', 'wdm-ultimate-auction')."</span>";
						elseif($hours < 1)
						{
							if($minutes > 1)
								echo "<span class='wdm-mark-normal'>". $minutes ." ".__('minutes', 'wdm-ultimate-auction')."</span>";
							elseif($minutes == 1)
								echo "<span class='wdm-mark-normal'>". $minutes ." ".__('minute', 'wdm-ultimate-auction')."</span>";
							elseif($minutes < 1)
							{
								if($seconds > 1)
									echo "<span class='wdm-mark-normal'>". $seconds ." ".__('seconds', 'wdm-ultimate-auction')."</span>";
								elseif($seconds == 1)
									echo "<span class='wdm-mark-normal'>". $seconds ." ".__('second', 'wdm-ultimate-auction')."</span>";
								else
									echo "<span class='wdm-mark-red'>".__('Expired', 'wdm-ultimate-auction')."</span>";
							}
						}
					}
						
				}
				else
				{
					echo "<span class='wdm-mark-red'>".__('Expired', 'wdm-ultimate-auction')."</span>";
				}

				?>
				<br/>
			</li>
			<li class="wdm-apbid auc_single_list auc_list_center">
			 <input class="wdm_bid_now_btn" type="button" value="<?php _e('Bid Now', 'wdm-ultimate-auction');?>" />
			</li>
			<li><div class="wdm-apd"><?php echo $wdm_single_auction->post_excerpt ; ?> </div></li>
				</ul>
				</a>
			</li>
			<?php
		}
        
global $wpdb;

$live_posts = array();

$comm_query = "SELECT object_id
FROM ".$wpdb->prefix."term_relationships
WHERE term_taxonomy_id = (SELECT term_id
FROM ".$wpdb->prefix."terms
WHERE slug = 'live')";

$comm_query = apply_filters('wdm_ua_filtered_auctions', $comm_query);

$live_posts = $wpdb->get_col($comm_query);

if(!empty($live_posts)){
     $live_posts = implode("," , $live_posts);

     $count_query = "SELECT count(ID)
     FROM ".$wpdb->prefix."posts
     WHERE post_type = 'ultimate-auction'
     AND ID IN($live_posts)
     AND post_status = 'publish'";

     $count_query = apply_filters('wdm_ua_filtered_counts', $count_query);

     $count_pages = $wpdb->get_var($count_query);
     
     if(!empty($count_pages)){
	  echo '<input type="hidden" id="wdm_ua_auc_avail" value="'.$count_pages.'" />';

	  $c=ceil($count_pages/$page_num);
	  auction_pagination($c, 1, $paged);
     }
}
?>
</ul>
</div>