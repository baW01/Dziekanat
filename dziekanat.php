<?php
/*
Plugin Name: Dziekanat
Description: Ten plugin scrapuje infomracje na temat grup z dziekanatu.
Version: 1.0
Author: Lechu & baW
*/

function custom_scrape() {
    $url = "https://dos.usz.edu.pl/cukrowa/zajecia-cukrowa/";
    $response = wp_safe_remote_get($url);

    if (is_wp_error($response)) {
        return "Błąd podczas pobierania strony.";
    }

    $body = wp_remote_retrieve_body($response);
    $soup = new DOMDocument();
    @$soup->loadHTML($body);
    $entries = $soup->getElementsByTagName("h2");

    $output = '';

    // Modal z informacjami (początkowo ukryty)
    $output .= "
    <div class='modal fade' id='infoModal' tabindex='-1' role='dialog' aria-labelledby='infoModalLabel' aria-hidden='true'>
        <div class='modal-dialog' role='document'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title' id='infoModalLabel'>Informacje grupa 271</h5>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <div class='modal-body'>";

    $firstEntry = true;
    $found = false;

    foreach ($entries as $entry) {
        if (strpos($entry->textContent, '271') !== false) {
            if (!$firstEntry) {
                $output .= "<hr>";
            }

            $output .= "<h2>Tytuł: " . esc_html($entry->textContent) . "</h2";

            $post_meta = $entry->nextSibling;
            while ($post_meta && $post_meta->nodeName != 'p') {
                $post_meta = $post_meta->nextSibling;
            }

            if ($post_meta) {
                $output .= "<p>" . esc_html($post_meta->textContent) . "</p>";
            } else {
                $output .= "<p>Brak elementu &lt;p&gt; class='post-meta' dla tego tytułu.</p>";
            }

            $post_content_inner = $entry->nextSibling;
            while ($post_content_inner && $post_content_inner->nodeName != 'div') {
                $post_content_inner = $post_content_inner->nextSibling;
            }

            if ($post_content_inner) {
                $paragraph = $post_content_inner->getElementsByTagName("p")->item(0);

                if ($paragraph) {
                    $output .= "<p>" . esc_html($paragraph->textContent) . "</p>";
                } else {
                    $output .= "<p>Brak elementu &lt;p&gt; wewnątrz &lt;div&gt; class='post-content-inner' dla tego tytułu.</p>";
                }
            }

            $firstEntry = false;
            $found = true;
        }
    }

    if (!$found) {
        $output .= "<h2>Brak informacji</h2>";
    }

    $output .= "
                </div>
            </div>
        </div>
    </div>";

    return $output;
}
add_shortcode('custom_scrape', 'custom_scrape');


