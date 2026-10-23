<?php

namespace App\Modules\Recaptcha;

use Nette\Forms\Container;

/**
 * Adds $form->addReCaptcha($name, $label, $message) to every Nette form.
 *
 * The service is tagged "run" in services.neon, so the container creates it - and its
 * setup calls register() - on every request, before any presenter builds a form.
 */
class RecaptchaFormExtension
{
    /** @var string */
    private $siteKey;

    /** @var RecaptchaVerifier */
    private $verifier;

    /**
     * @param string $siteKey
     * @param RecaptchaVerifier $verifier
     */
    public function __construct($siteKey, RecaptchaVerifier $verifier)
    {
        $this->siteKey = $siteKey;
        $this->verifier = $verifier;
    }

    /**
     * @param string $methodName
     */
    public function register($methodName)
    {
        $siteKey = $this->siteKey;
        $verifier = $this->verifier;

        Container::extensionMethod($methodName, function (Container $container, $name, $label = null, $message = null) use ($siteKey, $verifier) {
            return $container[$name] = new RecaptchaInput($verifier, $siteKey, $label, $message);
        });
    }
}
