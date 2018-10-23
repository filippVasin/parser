<?php
// разкомментировать в случае желания лицезреть ошибки
//ini_set("display_errors",1);
//error_reporting(E_ALL);

require_once ('setting.php');
require_once('vendor/autoload.php');

use DiDom\Document;

$count_page = 31;

for ($i = 1; $i <= $count_page; $i++) {
    sleep(2);

    $url = URL . '?page=' . $i;
    $html = file_get_contents($url);


    $document = new Document($url, true);

    $teaser_imgs = $document->find('.b-teaser img');
    $teaser_titles = $document->find('.b-teaser__title');
    $teaser_cashs = $document->find('.b-shop-teaser__cash-value-row');

    $list = [];
    foreach ($teaser_imgs as $key => $item) {
        $list[$key]['img'] = $item->src;
    }
    foreach ($teaser_titles as $key => $item) {
        $list[$key]['title'] = trim($item->text());
    }
    foreach ($teaser_cashs as $key => $item) {

        if ($item->child(1)->text() == 'до') {
            $list[$key]['cash'] = 'до ' . $item->child(3)->text();
        } else {
            $list[$key]['cash'] = $item->child(1)->text();
        }
    }

    foreach ($list as $item) {
        $saveto = IMG_DIR;
        $filename = grab_image($item['img'], $saveto);
        $title = $item['title'];
        $cash = $item['cash'];
        $tofile = "'$title';'$cash';'$filename'\n";
        edit_csv($tofile);
    }
    echo $i.PHP_EOL;
}

function grab_image($url,$saveto)
{

    $ext = strtolower(substr($url, strripos($url, '.') + 1));
    $filename = md5($url).'.'.$ext;
    $saveto .= $filename;

    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($saveto)){
        unlink($saveto);
    }
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);

    return $filename;
}

function edit_csv($tofile)
{
    $file = CSV;
    $bom = "\xEF\xBB\xBF";
    file_put_contents($file,  $bom .$tofile . file_get_contents($file));
}