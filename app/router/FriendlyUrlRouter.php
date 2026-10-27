<?php

namespace App;

use Nette\Application\BadRequestException;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\UrlScript;
use Nette\Routing\Router;
use App\Model\SystemModel\LegalCounselQuestion;

class FriendlyUrlRouter implements Router
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
     * Maps HTTP request to the parameters of the Homepage:question action.
     */
    public function match(HttpRequest $httpRequest): ?array
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

        $idStr = end( $idWithSeoTitleParts );
        if(!is_numeric($idStr)){
            //Check if it's old link {id}-{seotitle}
            $idStr = reset( $idWithSeoTitleParts );
            if(!is_numeric($idStr)){
                return null;
            }
        }

        $id = intval( $idStr );

        $question = $this->legalCounselQuestion->findRootQuestion($idWithSeoTitle);

        if ($question === null) {
            throw new BadRequestException();
        }

        $params = $httpRequest->getQuery();
        unset($params['paginator-page']);
        $params['presenter'] = 'Homepage';
        $params['action'] = 'question';
        $params['id'] = $id;
        $params['slug'] = $question['slug'];

        return $params;
    }

    /**
     * Constructs absolute URL of the Homepage:question action.
     */
    public function constructUrl(array $params, UrlScript $refUrl): ?string
    {
        if (($params['presenter'] ?? null) !== 'Homepage' || ($params['action'] ?? null) !== 'question') {
            return null;
        }

        $question = $this->legalCounselQuestion->findRootQuestion($params['slug'] ?? null);

        if($question === null){
            return null;
        }

        $baseUrl = preg_replace('/'.$this->projectDir.'\//', '', $refUrl->getBaseUrl());
        $url = $baseUrl . self::urlify($question['slug']);

        //Remove processed parameters
        unset($params['presenter'], $params['action'], $params['id'], $params['slug']);

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
