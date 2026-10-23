<?php

namespace App\Modules\Recaptcha;

use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Form field of the reCAPTCHA v2 widget: renders
 * <div class="g-recaptcha" data-sitekey="..." id="..."></div>, which the template turns
 * into the widget with recaptcha/api.js (loaded in @layout.latte). The field
 * has no value of its own (omitted from getValues()); on submit it is valid only when
 * Google confirms the posted token, otherwise the form gets $message as its error.
 */
class RecaptchaInput extends BaseControl
{
    /** @var RecaptchaVerifier */
    private $verifier;

    /**
     * @param RecaptchaVerifier $verifier
     * @param string $siteKey
     * @param string|null $caption
     * @param string|null $message
     */
    public function __construct(RecaptchaVerifier $verifier, $siteKey, $caption = null, $message = null)
    {
        parent::__construct($caption);

        $this->verifier = $verifier;
        $this->setOmitted();
        $this->control = Html::el('div')
            ->class('g-recaptcha')
            ->data('sitekey', $siteKey);
        $this->setRequired(true)
            ->addRule(self::class . '::validateValid', $message);
    }

    /**
     * @return Html
     */
    public function getControl()
    {
        $this->setOption('rendered', true);
        $el = clone $this->control;

        return $el->addAttributes([
            'id' => $this->getHtmlId(),
        ]);
    }

    /**
     * The widget posts its token under its own name, never under the field's name -
     * the required rule must not fail on that, the token is checked by validateValid().
     *
     * @return bool
     */
    public function isFilled()
    {
        return true;
    }

    /**
     * @param RecaptchaInput $control
     * @return bool
     */
    public static function validateValid(RecaptchaInput $control)
    {
        return $control->verifier->verify();
    }
}
