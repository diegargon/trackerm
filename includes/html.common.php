<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function html_mediainfo_tags($mediainfo, $tags = null) {

    $return_tags = [];

    $tags = [
        'General' => ['Format', 'FrameRate', 'AudioCount', 'VideoCount', 'TextCount'],
        'Video' => ['FrameRate_Mode', 'ColorSpace', 'Encoded_Library_Name'],
        'Audio' => ['Format', 'BitRate_Mode', 'Format_Commercial_IfAny', 'Channels', 'BitRate', 'Compression_Mode'],
//        'Text' => '',
    ];
    foreach ($tags as $tag_ary_key => $tag_ary) {
        if ($tag_ary_key == 'General') {
            foreach ($tag_ary as $tag_value) {
                if (isset($mediainfo[$tag_ary_key][$tag_value])) {
                    $return_tags[] = ['mediainfo_tag_title' => $tag_value, 'mediainfo_tag_value' => $mediainfo[$tag_ary_key][$tag_value]];
                }
            }
        }
        if ($tag_ary_key == 'Video') {
            foreach ($tag_ary as $tag_value) {
                if (isset($mediainfo[$tag_ary_key][0][$tag_value])) {
                    $return_tags[] = ['mediainfo_tag_title' => $tag_value, 'mediainfo_tag_value' => $mediainfo[$tag_ary_key][0][$tag_value]];
                }
            }
        }
        if ($tag_ary_key == 'Audio') {
            foreach ($tag_ary as $tag_value) {
                if (isset($mediainfo[$tag_ary_key][1][$tag_value])) {
                    $return_tags[] = ['mediainfo_tag_title' => $tag_value, 'mediainfo_tag_value' => $mediainfo[$tag_ary_key][1][$tag_value]];
                }
            }
        }
        if ($tag_ary_key == 'Text') {
            if (isset($mediainfo[$tag_ary_key][1][$tag_value])) {
                $return_tags[] = ['mediainfo_tag_title' => $tag_value, 'mediainfo_tag_value' => $mediainfo[$tag_ary_key][1][$tag_value]];
            }
        }
    }

    if (isset($mediainfo['Video'][0]['Width']) && isset($mediainfo['Video'][0]['Height'])) {
        $resolution = $mediainfo['Video'][0]['Width'] . 'x' . $mediainfo['Video'][0]['Height'];
        $return_tags[] = ['mediainfo_tag_title' => $resolution, 'mediainfo_tag_value' => $resolution];
    }
    if (isset($mediainfo['Audio'])) {
        foreach ($mediainfo['Audio'] as $audio) {
            if (isset($audio['Language'])) {
                $return_tags[] = ['mediainfo_tag_title' => $audio['Language'], 'mediainfo_tag_value' => $audio['Language']];
            }
        }
    }

    return $return_tags;
}
