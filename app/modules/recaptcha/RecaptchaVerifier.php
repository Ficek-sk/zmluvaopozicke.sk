<?php

namespace App\Modules\Recaptcha;

use Nette\Http\IRequest;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Asks Google whether the reCAPTCHA token posted with the form is valid.
 *
 * The token comes in the POST field "g-recaptcha-response" (the widget adds it to the
 * form itself). Secret, token and the visitor's IP go to siteverify in the query string
 * of an otherwise empty POST - the same request uestla/recaptcha-control sent. Any
 * failure (no token, curl error, unreadable answer) counts as "not verified".
 */
class RecaptchaVerifier
{
    const VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const RESPONSE_KEY = 'g-recaptcha-response';

    /** @var string */
    private $secretKey;

    /** @var IRequest */
    private $httpRequest;

    /**
     * @param string $secretKey
     * @param IRequest $httpRequest
     */
    public function __construct($secretKey, IRequest $httpRequest)
    {
        $this->secretKey = $secretKey;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @return bool
     */
    public function verify()
    {
        $response = $this->httpRequest->getPost(self::RESPONSE_KEY);
        if (!$response) {
            return false;
        }

        $result = $this->post(self::VERIFICATION_URL . '?' . http_build_query([
            'secret' => $this->secretKey,
            'response' => $response,
            'remoteip' => $this->httpRequest->getRemoteAddress(),
        ], '', '&'));

        if (!$result) {
            return false;
        }

        try {
            $json = Json::decode($result);
            return isset($json->success) && $json->success;
        } catch (JsonException $e) {
            return false;
        }
    }

    /**
     * @param string $url
     * @return string|false
     */
    private function post($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno !== 0) {
            return false;
        }

        return $response;
    }
}
