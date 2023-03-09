<?php

function easy_media_download_display_extensions()
{
    //echo '<div class="wrap">';
    //echo '<h2>' .__('Easy Media Download Extensions', 'easy-media-download') . '</h2>';
    echo '<link type="text/css" rel="stylesheet" href="'.EASY_MEDIA_DOWNLOAD_URL.'/extensions/easy-media-download-extensions.css" />' . "\n";
    
    $extensions_data = array();

    $extension_1 = array(
        'name' => 'User Only Download',
        'thumbnail' => EASY_MEDIA_DOWNLOAD_URL.'/extensions/images/emd-user-only-download.png',
        'description' => 'Show a download button to logged in users only',
        'page_url' => 'https://noorsplugin.com/how-to-show-a-download-button-to-logged-in-users-only/',
    );
    array_push($extensions_data, $extension_1);
    
    //Display the list
    $output = '';
    foreach ($extensions_data as $extension) {
        $output .= '<div class="easy_media_download_extensions_item_canvas">';

        $output .= '<div class="easy_media_download_extensions_item_thumb">';
        $img_src = $extension['thumbnail'];
        $output .= '<img src="' . $img_src . '" alt="' . $extension['name'] . '">';
        $output .= '</div>'; //end thumbnail

        $output .='<div class="easy_media_download_extensions_item_body">';
        $output .='<div class="easy_media_download_extensions_item_name">';
        $output .= '<a href="' . $extension['page_url'] . '" target="_blank">' . $extension['name'] . '</a>';
        $output .='</div>'; //end name

        $output .='<div class="easy_media_download_extensions_item_description">';
        $output .= $extension['description'];
        $output .='</div>'; //end description

        $output .='<div class="easy_media_download_extensions_item_details_link">';
        $output .='<a href="'.$extension['page_url'].'" class="easy_media_download_extensions_view_details" target="_blank">View Details</a>';
        $output .='</div>'; //end detils link      
        $output .='</div>'; //end body

        $output .= '</div>'; //end canvas
    }
    echo $output;
    
    //echo '</div>';//end of wrap
}
