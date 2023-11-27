<?php

add_shortcode('wm_grid_track', 'wm_grid_track_maremma');

function wm_grid_track_maremma($atts)
{

    if (!is_admin()) {

        if (defined('ICL_LANGUAGE_CODE')) {
            $language = ICL_LANGUAGE_CODE;
        } else {
            $language = 'it';
        }

        extract(shortcode_atts(array(
            'activity' => 'a piedi',
            'quantity' => -1,
            'ids' => ''
        ), $atts));
        $ids_array = array();
        if ($ids) {
            if ($language == 'en') {
                $idsarray = explode(',', $ids);
                foreach ($idsarray as $id) {
                    $post_type = get_post_type($id);
                    $post_default_language_id = apply_filters('wpml_object_id', $id, $post_type, FALSE, $language);
                    array_push($ids_array, $post_default_language_id);
                }
            } else {
                $ids_array = explode(',', $ids);
            }
            $posts = get_posts(array('post_type' => 'page', 'post__in' => $ids_array, 'numberposts' => -1));
            $quantity = count($posts);
        } else {
            $activity = strtolower($activity);

            $activity_mapped = geohub_taxonomy_mapping($activity);
            $activities_url = "https://geohub.webmapp.it/api/app/elbrus/1/taxonomies/track_activity_$activity_mapped.json";
            // $posts = json_decode(file_get_contents($activities_url), TRUE);
            $posts = json_decode(file_get_contents($activities_url), TRUE);


            if ($quantity == -1 || $quantity > count($posts)) {
                $quantity = count($posts);
            }
        }

        ob_start();
?><div class="wm-grid-track-item-container"><?php
                                            for ($i = 0; $i < $quantity; $i++) {
                                                $post = $posts[$i];
                                                $hideClass = '';
                                                if ($ids) {
                                                    $post_url = esc_url(get_permalink($post->ID));
                                                    $image_url = get_the_post_thumbnail_url($post->ID);
                                                    $name =  $post->post_title;
                                                    $excerpt =  wp_filter_nohtml_kses(wp_trim_excerpt(preg_replace('#\[[^\]]+\]#', '', $post->post_content), $post->ID));
                                                    $hideClass = 'hidesection';
                                                } else {
                                                    $post_url = esc_url(get_permalink(get_page_by_title($post['name'][$language])));
                                                    $image_url = $post['image']['sizes']['400x200'];
                                                    if (array_key_exists('name', $post) && is_array($post['name']) && array_key_exists($language, $post['name'])) {
                                                        $name = $post['name'][$language];
                                                    } else {
                                                        $name = ''; // valore di default se non definito
                                                    }

                                                    if (array_key_exists('excerpt', $post) && is_array($post['excerpt']) && array_key_exists($language, $post['excerpt'])) {
                                                        $excerpt = $post['excerpt'][$language];
                                                    } else {
                                                        $excerpt = ''; // valore di default se non definito
                                                    }

                                                    if (array_key_exists('difficulty', $post) && is_array($post['difficulty']) && array_key_exists($language, $post['difficulty'])) {
                                                        $difficulty = $post['difficulty'][$language];
                                                    } else {
                                                        $difficulty = ''; // valore di default se non definito
                                                    }
                                                    if (array_key_exists('distance', $post)) {
                                                        $distance = $post['distance'];
                                                    } else {
                                                        $distance = ''; // o un'altra logica di default che preferisci per la distanza non definita
                                                    }
                                                }

                                            ?>

                <div class="wm-grid-track-item">
                    <a href="<?= $post_url ?>" class="wm-grid-track-link">
                        <div class="wm-grid-track-intro" style="background-image:url(<?= $image_url ?>);">
                            <div class="wm-grid-track-overlay"></div>
                            <div class="wm-grid-track-title"><?= $name ?></div>
                            <div class="wm-grid-track-excerpt"><?= $excerpt ?></div>
                        </div>
                        <div class="wm-grid-track-info">
                            <div class="wm-grid-track-difficulty"><?php if ($hideClass) {
                                                                        echo __('Discover more', 'wm-child-maremma');
                                                                    } else {
                                                                        echo __('Difficulty', 'wm-child-maremma') . ': <span>' . $difficulty . '</span>';
                                                                    } ?></div>
                            <div class="wm-grid-track-distance <?= $hideClass ?>"><?= __('Distance', 'wm-child-maremma') . ' ' . $distance . 'km' ?></div>
                            <div class="wm-grid-track-link-icon"><i class="far fa-arrow-right"></i></div>
                        </div>
                    </a>
                </div>

            <?php
                                            }
            ?>
        </div><?php


                echo ob_get_clean();
            } else {
                return;
            }
        }
