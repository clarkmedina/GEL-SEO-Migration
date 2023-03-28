<?php

/**
 * Plugin Name: GEL-SEO-migration Script Beta
 * Description: SEO checker tool to make your life easier when Migrating Website from STG to PRD
 * Author:      Clark Medina
 * License:     GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

//=== Basic security, prevents file from being loaded directly from the folder or root folder.===
defined('ABSPATH') or die('Die&#8217; YOU!');

add_action("init", "GEL_compare_urls");

function GEL_compare_urls()
{
    if (isset($_GET["GEL_compare_urls"])) {
        echo "<body>";
        echo "<div class='GEL_container'>";

        //wp_die(print_r(get_post_types()));

        $args = array(
            'posts_per_page'   => -1,
            'fields' => 'ids',
            'post_status' => 'publish',
            'post_type' => 'any',
            'lang' => '',
            'suppress_filters' => true
        );
        $the_query = new WP_Query($args);

        //print_r($the_query->posts);
		echo "<header>";
		echo "<h1>GEL SEO Migrating Script Beta 1</h1>";
		echo "<h1><small>SEO checker Tool to make your life easier when Migrating Website from STG to PRD</small></h1>";
		echo "</header>";
        echo "<header>";
        echo "<h1>Comparing " . count($the_query->posts) . " URLS : <small>from STG<b>" . get_bloginfo("url") . "</b> to PRD<b>" . $_GET["GEL_compare_urls"] . "</b></small></h1>";

        echo "<button id='download-button'>Download CSV</button>";
        echo "</header>";

        echo "<table class='table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Ok ?</td>";
        echo "<th>STG URL</td>";
        echo "<th>PRD URL</td>";
        echo "<th>Status code</td>";
        echo "</tr>";
        echo "</thead>";
        foreach ($the_query->posts as $post) {

            $url_to_check = str_replace(get_bloginfo("url"), $_GET["GEL_compare_urls"], get_the_permalink($post));

            $url_headers = getUrlHttpCode($url_to_check);

            $url_status = (isset($url_headers) && $url_headers == "200") ? "ok" : "nok";

            echo "<tr class='" . $url_status . "'>";
            echo "<td>" . $url_status . "</td>";
            echo "<td><a href='" . get_the_permalink($post) . "' target='_blank'>" . get_the_permalink($post) . "</a></td>";
            echo "<td><a href='" . $url_to_check . "' target='_blank'>" . $url_to_check . "</a></td>";
            echo "<td>" . $url_headers . "</td>";
            echo "</tr>";

            
        }
        echo "</table>";
        echo "</div>";
        echo "</body>";



        echo "<style>
        body {
            font-family:sans-serif;
            background-color:#f7f7f7
        }
        a {
            color: #278de5;
            text-decoration:none
        }
        a:hover {
            text-decoration:underline
        }
        header {
            display:flex;
            align-items:baseline;
            justify-content: space-between;
            margin-top: 80px;
            margin-bottom:  40px;
        }
        #download-button {
            background-color: transparent;
            border:1px solid #278de5;
            font-size:1.2em;
            color:#278de5;
            padding:0.3em 0.5em;
            border-radius:5px;
            transition:all .3s ease-out;
            cursor:pointer
        }
        #download-button:hover {
            background-color: #278de5;
            color:#fff;
        }
        h1 {
            font-size: 2rem;
            margin:0;
            padding:0;
            font-weight:100;
            color: #041524;
        }
        h1 small {
            display:block;
            font-size: 0.6em
        }
        h1 b {
            font-weight:400;
            border:1px solid #e5e5e5;
            border-radius:5px;
            display:inline-block;
            padding:0.3em 0.5em
        }
        .GEL_container {
            max-width:70vw;
            margin:auto;
            margin-bottom:80px
        }
        .table {
            border: 1px solid #ccc;
            border-collapse:collapse;
            width:100%;
            border:1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }
        .table thead tr {
            background-color: #041524;
            color:#fff
        }
        .table thead th {
            white-space:nowrap;
            padding: 0.5em 1em;
            font-weight:500
        }
        .table tr.ok {
            background-color:#fff;
        }
        .table tr.nok {
            background-color:#c23140;
            color: #fff
        }
        .table tr.nok a {
            color:#fff
        }

        .table td {
            font-size:0.8em;
            padding: 1em 1em;
        }
        
        </style>";

        echo '<script>
        
            function htmlToCSV(html, filename) {
            var data = [];
            var rows = document.querySelectorAll("table tr");
                    
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");
                        
                for (var j = 0; j < cols.length; j++) {
                        row.push(cols[j].innerText);
                }
                        
                data.push(row.join(",")); 		
            }
        
            downloadCSVFile(data.join("\n"), filename);
        }

            function downloadCSVFile(csv, filename) {
                var csv_file, download_link;
            
                csv_file = new Blob([csv], {type: "text/csv"});
            
                download_link = document.createElement("a");
            
                download_link.download = filename;
            
                download_link.href = window.URL.createObjectURL(csv_file);
            
                download_link.style.display = "none";
            
                document.body.appendChild(download_link);
            
                download_link.click();
            }
            document.getElementById("download-button").addEventListener("click", function () {
                var html = document.querySelector("table").outerHTML;
                htmlToCSV(html, "check_urls.csv");
            });
        </script>';

        die;
    }
}



function getUrlHttpCode($url = NULL)
{
    if ($url == NULL) return false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    //sleep(1);
    return $httpcode;
}