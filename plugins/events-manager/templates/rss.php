<?php
/*
 * RSS Page
 * This page handles the event RSS feed.
 * You can override this file by copying it to yourthemefolder/plugins/events-manager/templates/ and modifying as necessary.
 * 
 */ 
header ( "Content-type: application/rss+xml; charset=UTF-8" );
echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title><?php echo esc_html(get_option ( 'dbem_rss_main_title' )); ?></title>
        <link><?php echo EM_URI; ?></link>
        <description><?php echo esc_html(get_option('dbem_rss_main_description')); ?></description>
        <docs>http://blogs.law.harvard.edu/tech/rss</docs>
        <pubDate><?php echo date('D, d M Y H:i:s +0000', get_option('em_last_modified')); ?></pubDate>
        <atom:link href="<?php echo esc_attr(EM_RSS_URI); ?>" rel="self" type="application/rss+xml" />
        <?php
        $description_format = str_replace ( ">", "&gt;", str_replace ( "<", "&lt;", get_option ( 'dbem_rss_description_format' ) ) );
        $rss_limit = get_option('dbem_rss_limit');
        $page_limit = $rss_limit > 50 || !$rss_limit ? 50 : $rss_limit; //set a limit of 50 to output at a time, unless overall limit is lower
        
        // Calculate the start and end dates for the next week
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+14 days'));

        $args = !empty($args) ? $args : array(); /* @var $args array */
        $args = array_merge(array(
            'scope' => '1-months',
            'owner' => false, 
            'limit' => $page_limit, 
            'page' => 1, 
            'order' => get_option('dbem_rss_order'), 
            'orderby' => get_option('dbem_rss_orderby')
        ), $args);
        $args = apply_filters('em_rss_template_args', $args);
        $EM_Events = EM_Events::get($args);
        $count = 0;
        while (count($EM_Events) > 0) {
            foreach ($EM_Events as $EM_Event) {

                $event_url = $EM_Event->output('#_EVENTURL');
                $event_img_url = $EM_Event->output('#_EVENTIMAGEURL');
                $event_tags = $EM_Event->output('#_EVENTTAGS');
                $event_location_url = $EM_Event->output('#_LOCATIONURL');
                $online_location_info = $EM_Event->output('#_EVENTLOCATION');
                $physical_location_info = $EM_Event->output('#_LOCATIONFULLLINE');
                $physical_location_name = $EM_Event->output('#_LOCATIONNAME');
                $physical_location_address = $EM_Event->output('#_LOCATIONADDRESS');

                if (!empty($online_location_info)) {
                    $location_description = "<b>Location:</b> " . $online_location_info . "<br/>";
                } elseif (!empty($physical_location_info)){
                    // $location_description = "<b>Location:</b> <a href='" . $event_location_url .  "'>" . $physical_location_name . "</a>, " . $physical_location_address . "<br/>";
                    $location_description = "<b>Location:</b> <a href='{$event_location_url}'>{$physical_location_name}</a>, {$physical_location_info}<br/>";
                }

                $description = "<b>Date:</b> " . $EM_Event->output('#_EVENTDATES') . ", " . $EM_Event->output('#_EVENTTIMES') . "<br/>";
                $description .= $location_description;
                $description .= $EM_Event->output('#_EVENTEXCERPT');
                ?>
                <item>
                    <title><?php echo $EM_Event->output(get_option('dbem_rss_title_format'), "rss"); ?></title>
                    <link><?php echo $event_url; ?></link>
                    <guid><?php echo $event_location_url; ?></guid>
                    <pubDate><?php echo $EM_Event->start(true)->format('D, d M Y H:i:s +0000'); ?></pubDate>
                    <description><![CDATA[<?php echo $description; ?>]]></description>
                    <enclosure url="<?php echo $event_img_url; ?>" type="image" />
                    <category><?php echo $event_tags; ?></category>
                </item>
                <?php
                $count++;
            }
            if ($rss_limit != 0 && $count >= $rss_limit) { 
                //we've reached our limit, or showing one event only
                break;
            } else {
                //get next page of results
                $args['page']++;
                $EM_Events = EM_Events::get($args);
            }
        }
        ?>
    </channel>
</rss>
