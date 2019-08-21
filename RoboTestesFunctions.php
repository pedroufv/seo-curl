<?php
/*
 * Obtem o html de um link
 */
function getHtml($url){
	$cURL = curl_init($url);

    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

	// Seguir qualquer redirecionamento que houver na URL
    curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);

	return curl_exec($cURL);
}

/*
 * Função que encontra e acessa links, verificando a profundidade
 * e chama uma função para testar o link
 */
function crawling($url, $depth = 5, $action){
    static $seen = array();
    if (isset($seen[$url]) || $depth === 0) {
        return;
    }

    $seen[$url] = true;

    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);

    $anchors = $dom->getElementsByTagName('a');
    foreach ($anchors as $element) {
        $href = $element->getAttribute('href');
        if (0 !== strpos($href, 'http')) {
            $path = '/' . ltrim($href, '/');
            if (extension_loaded('http')) {
                $href = http_build_url($url, array('path' => $path));
            } else {
                $parts = parse_url($url);
                $href = $parts['scheme'] . '://';
                if (isset($parts['user']) && isset($parts['pass'])) {
                    $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                }
                $href .= $parts['host'];
                if (isset($parts['port'])) {
                    $href .= ':' . $parts['port'];
                }
                $href .= $path;
            }
        }
        crawling($href, $depth - 1, $action);
    }
    $action($url);
}

/*
 * Função que verifica se a url possui cabeçalho 404
 */
function check404($url){
	$html = getHtml($url);

	// Pega o código de resposta HTTP
	$resposta = curl_getinfo($cURL, CURLINFO_HTTP_CODE);

	curl_close($cURL);

	if ($resposta == '404') {
		echo $url.' - 404<br/>';
	} else {
		echo $url.' - OK! <br/>';
	}
}

/*
 * Função que encontra erros php na url (caso a exibição esteja ativa)
 */
function checkPhpError($url){
    $html = getHtml($url);

    preg_match_all('((Notice|Warning|Deprecated|(Fatal error))+:(.*?)[0-9]{1,200})', $html, $matches);
    if(count($matches[0]) > 0)
    {
        $problemas['url']   = $url;
        $problemas['erros'] = $matches[0];
        echo '<pre>';
        print_r($problemas);
        echo '</pre>';
    }
    else
        echo 'Não encontramos problemas!<br />';

}

/*
 * Função que verifica se as imgs em um link possuem alt e title
 */
function checkImg($url){
    $html = getHtml($url);

    $doc = new DOMDocument;
    $doc->loadHtml($html);

    $seo    = array();
    $count  = 0;
    foreach($doc->getElementsByTagName('img') as $img)
    {
        $src    = $img->getAttribute('src');
        $alt    = $img->getAttribute('alt');
        $title  = $img->getAttribute('title');

        preg_match_all('(\/([a-zA-Z0-9-_\.]*?)\.(jpg|gif|png|ico))', strtolower($src), $matches);
        $name  =  $matches[1][0];

        if(empty($alt) OR empty($title))
        {
            $count++;
            $seo[$count]['src'] = utf8_decode($src);
            $seo[$count]['sugest'] = 'title="'.utf8_decode($name).'"';

	        if(empty($alt))
	            $seo[$count]['alt'] = true;
	        if(empty($title))
	            $seo[$count]['title'] = true;
        }
    }
    echo "<pre>";
    print_r($seo);
    echo "</pre>";
}

/*
 * Função que verifica se os links de uma url possuem alt e title
 */
function checkLink($url){
   	$html = getHtml($url);

    $doc = new DOMDocument;
    $doc->loadHtml($html);

    $seo    	 = array();
    $count  	 = 0;
    $notitlenum  = 0;
    foreach($doc->getElementsByTagName('a') as $link)
    {
        $href    = $link->getAttribute('href');
        $rel     = $link->getAttribute('rel');
        $title   = $link->getAttribute('title');
        $line 	 = $link->getLineNo()+4;

        $uri   	  = explode('/', $href);
        $lasturi  = $uri[count($uri)-1];
        $sugest   = ucwords(str_replace('-', ' ', $lasturi));

        if(empty($title))
        {
            $count++;
            $seo[$count]['url'] 	= $url;
            $seo[$count]['href'] 	= utf8_decode($href);
            $seo[$count]['line'] 	= $line;
            $seo[$count]['sugest'] 	= 'title="'.utf8_decode($sugest).'"';
	        if(!empty($rel))
	            $seo[$count]['rel'] = utf8_decode($rel);
    	}
    }
	echo "<pre>";
    print_r($seo);
    echo "</pre>";
}
?>