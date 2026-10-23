<?php

namespace App;

use App\Model\SystemModel\Post;
use App\Model\SystemModel\Redirect;
use Nette\Application\BadRequestException;
use Nette\Application\Request as AppRequest;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\Url;
use App\Model\SystemModel\LegalCounselQuestion;;

class FriendlyUrlRouter implements \Nette\Application\IRouter
{
    public $projectDir;

    /** @var LegalCounselQuestion */
    public $legalCounselQuestion;

    public function __construct(
        LegalCounselQuestion $legalCounselQuestion
    ) {
        $this->legalCounselQuestion = $legalCounselQuestion;

        //WARNING: Only for predefine directory structure
        $this->projectDir = basename( dirname(__DIR__, 2) );
    }

    /**
     * Maps HTTP request to a Request object.
     * @return Nette\Application\Request|NULL
     */
    function match(HttpRequest $httpRequest)
    {
        $url = $httpRequest->getUrl();
        $relPath = (string) substr($url->getPath(), strlen($url->getBasePath()));
        if (!$relPath)
            return null;

        if (!preg_match('/[a-z-]*-[0-9]+/', $relPath)){
            //Check if it's old link {id}-{seotitle}
            if (!preg_match('/[0-9]+-[a-z-]*/', $relPath)){
                return null;
            }
        }

        $relPathParts = explode('/', $relPath);
        $idWithSeoTitle = end( $relPathParts );

        //ID string can be {seotitle}-{id} e.g. abcd-51, abcd-efgh-ijkl-55
        $idWithSeoTitleParts = explode( '-', $idWithSeoTitle );
        if (count($idWithSeoTitleParts) <= 1)
            return null;

        $seoTitle = null;
        $idStr = end( $idWithSeoTitleParts );
        if(is_numeric($idStr)){
            $seoTitle = implode('-', array_slice($idWithSeoTitleParts, 0, -1));
        } else {
            //Check if it's old link {id}-{seotitle}
            $idStr = reset( $idWithSeoTitleParts );
            if(!is_numeric($idStr)){
                return null;
            }

            $seoTitle = implode('-', array_slice($idWithSeoTitleParts, 1));
        }

        $id = intval( $idStr );

        $question = $this->legalCounselQuestion->findRootQuestion($idWithSeoTitle);

        if ($question === null) {
            throw new BadRequestException();
        }

        $action = 'question';

        $params = $httpRequest->getQuery();
        $params['id'] = $id;
        $params['action'] = $action;
        unset($params['paginator-page']);

        if($action === 'question') {
            $params['slug'] = $question['slug'];
        }

        return new AppRequest(
            'Homepage',
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [AppRequest::SECURED => $httpRequest->isSecured()]
        );
    }

    /**
     * Constructs absolute URL from Request object.
     * @param AppRequest $appRequest
     * @param Url $refUrl
     * @return string|NULL
     * @throws \Exception
     */
    function constructUrl(AppRequest $appRequest, Url $refUrl)
    {
        $row = null;
        $params = $appRequest->getParameters();

        if ($params['action'] != 'question' && $params['action'] != 'article')
            return null;

        if ($params['action'] == 'article')
            $row = $this->post->find($params['id']);
        elseif ($params['action'] == 'question'){
            $question = $this->legalCounselQuestion->findRootQuestion($params['slug']);

            if($question === null){
                return null;
            }

            $baseUrl = preg_replace('/'.$this->projectDir.'\//', '', $refUrl->getBaseUrl());
            $url = $baseUrl . self::urlify($question['slug']);

            //Remove processed parameters
            unset($params['action'], $params['id'], $params['slug']);
            //unset($params['paginator-page']);

            $sep = ini_get('arg_separator.input');

            $query = http_build_query($params, '', $sep ? $sep[0] : '&');
            if ($query != ''){ // intentionally ==
                $url .= '?' . $query;
            }

            return $url;
        }

        if (!$row)
            return null;

        $baseUrl = preg_replace('/'.$this->projectDir.'\//', '', $refUrl->getBaseUrl());
        $url = $baseUrl . self::urlify( $row->seo_title ) . '-' . self::urlify( $params['id'] );

        //Remove processed parameters
        unset($params['action'],$params['id']);

        $sep = ini_get('arg_separator.input');
        $query = http_build_query($params, '', $sep ? $sep[0] : '&');
        if ($query != ''){ // intentionally ==
            $url .= '?' . $query;
        }

        return $url;
    }

    public static function urlify( $string ){
        return self::fromCamelCase( $string );
    }

    public static function fromCamelCase( $string, $glue = '-' ) {
        $string = preg_replace( '/(^[a-z0-9]+|[a-z])([A-Z])/', '$1-$2', $string );
        $string = preg_replace( '/([A-Z])([A-Z])/', '$1-$2', $string );
        $string = preg_replace( '/([a-z])([0-9]+)([a-z])/', '$1-$2-$3', strtolower( $string ) );

        return $string;
    }

    public static function toCamelCase( $string, $glue = '-' ) {
        return lcfirst( str_replace( ' ', '', ucwords( str_replace( $glue, ' ', $string) ) ) );
    }
}
