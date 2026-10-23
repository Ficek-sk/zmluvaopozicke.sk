<?php

use \Nette\Application\UI;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use App\Model\SystemModel\LegalCounselQuestion;
use App\Model\SystemModel\LegalCounselQuestionAnswer;
use App\Model\SystemModel\LegalCounselQuestionRelatedUrl;


Nette\Forms\Controls\BaseControl::$idMask = 'form-%s';

class HomepagePresenter extends Presenter
{

    /** @var FormRepository @inject */
    private $formRepository;

    /** @var MessageRepository @inject */
    private $messageRepository;

    /** @var EmailSender @inject */
    private $emailSender;

    /** @var DlznikRepository @inject */
    private $dlznikRepository;

    /** @var TemplateFactory @inject */
    private $templateFactory;

    /** @var LegalCounselQuestion @inject */
    public $legalCounselQuestion;

    /** @var LegalCounselQuestionAnswer @inject */
    public $legalCounselQuestionAnswer;

    /** @var LegalCounselQuestionRelatedUrl @inject  */
    public $legalCounselQuestionRelatedUrl;

    private $contractReviewManager;

    public $message_id;

    public const DOTS = '......................................';

    /**
     * HomepagePresenter constructor.
     * @param FormRepository $formRepository
     * @param MessageRepository $messageRepository
     * @param EmailSender $emailSender
     * @param DlznikRepository $dlznikRepository
     * @param TemplateFactory $templateFactory
     */
    public function __construct(FormRepository $formRepository,
                                MessageRepository $messageRepository,
                                EmailSender $emailSender,
                                DlznikRepository $dlznikRepository,
                                ITemplateFactory $templateFactory,
                                ContractReviewManager $contractReviewManager
    )
    {
        $this->formRepository = $formRepository;
        $this->messageRepository = $messageRepository;
        $this->emailSender = $emailSender;
        $this->dlznikRepository = $dlznikRepository;
        $this->templateFactory = $templateFactory;
        $this->contractReviewManager = $contractReviewManager;
    }

    public function renderQuestion($slug)
    {
        $question = $this->legalCounselQuestion->findRootQuestion($slug);

        if (!$question) {
            throw new \Nette\Application\BadRequestException('Stránka neexistuje!', 404);
        }

        if($question['redirect'] !== null){
            $this->redirectUrl($question['redirect'], 301);
        }

        $subQuestions = $this->legalCounselQuestion->getSubQuestions($question['id']);

        $this->template->question = $question;
        $this->template->recentQuestions = $this->legalCounselQuestion->getRecentQuestions(5);
        $this->template->subQuestions = $subQuestions;
        $this->template->answers = $this->legalCounselQuestionAnswer->findQuestionAnswers($question['id']);
        $this->template->titleLength = strlen($question['name']);
        $this->template->showCourse = ($question['category'] === 1 || $question['category'] === 11 || $question['category'] === 12);
        $this->template->title = $question['name'];
        $this->template->photoUrl = $this->contractReviewManager->getPhoto();
        $this->template->recentQuestions = $this->legalCounselQuestion->getRecentQuestions(5);
        $this->template->seoTitle = '[' . date('Y') . '] ' . $question['name'] . ' | Vzor zmluvy zadarmo';
        $this->template->relatedQuestions = $this->legalCounselQuestionRelatedUrl->getByQuestionId($question['id']);
    }


    public function renderDefault()
    {
        $currencyValues = [
            '€' => 'EUR',
            'CZK' => 'CZK',
            'USD' => 'USD',
            'other_currency' => 'Iná mena'
        ];

        $this->template->currencyValues = $currencyValues;

        $this->template->forms = $this->formRepository->findAll();
        $this->template->photoUrl = $this->contractReviewManager->getPhoto();
        $this->template->recentQuestions = $this->legalCounselQuestion->getRecentQuestions(5);
    }

    public function censorValue($value)
    {
        $deniedStrings = [
            'sro',
            's.r.o.',
            's r.o.',
            'a.s.',
            'akcio',
            'nar',
            'byt',
            'byd'
        ];

        foreach ($deniedStrings as $deniedString) {
            if (stripos($value, $deniedString) !== false)
                return true;
        }

        // Check all numbers with size 10 and ignore whitespaces and slashes between them
        $pattern = '/[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*[0-9]\/*\s*/';
        if (preg_match($pattern, $value))
            return true;

        // Check all slovak names
        if (\StringUtils::isSlovakName($value))
            return true;

        return false;
    }

    public function censorArray($form, $censorFields)
    {
        foreach ($form as $key => $value) {
            if (in_array($key, $censorFields) && $this->censorValue($value))
                $form[$key] = self::DOTS;
        }

        return $form;
    }

    public function renderForm($id)
    {
        $formId = $this->formRepository->find($id);

        if (!$formId) {
            $this->redirect('Homepage:default');
        }

        $form = \Nette\Utils\ArrayHash::from($this->formRepository->find($id)->toArray());

        $dlzniciSelection = $this->dlznikRepository->find_by_form_id($formId);
        $dlznici = [];

        foreach ($dlzniciSelection as $dlznik) {
            $dlznici[] = \Nette\Utils\ArrayHash::from($dlznik->toArray());
        }

        if (!$this->getUser()->isLoggedIn()) {
            $form['d_name'] = "....................................";
            $form['d_date_of_birdth'] = "......................................";
            $form['d_home'] = "......................................";
            $form['d_citizenship'] = "......................................";
            $form['d_company'] = "......................................";
            $form['d_ico'] = "......................................";
            $form['d_residence'] = "......................................";
            $form['d_representative'] = "......................................";
            $form['d_other'] = "......................................";
            $form['v_name'] = "......................................";
            $form['v_date_of_birdth'] = "......................................";
            $form['v_home'] = "......................................";
            $form['v_citizenship'] = "......................................";
            $form['v_company'] = "......................................";
            $form['v_ico'] = "......................................";
            $form['v_residence'] = "......................................";
            $form['v_representative'] = "......................................";
            $form['v_other'] = "......................................";
            $form['v_fop_other'] = "......................................";
            $form['return_account'] = "......................................";
            $form['money_receiving_account'] = "......................................";
            $form['money_receiving_part_transfer_account'] = '';

            $form = $this->censorArray($form, [
                'd_form',
                'd_name',
                'd_date_of_birdth',
                'd_rc',
                'd_address',
                'd_home',
                'd_citizenship',
                'd_company',
                'd_ico',
                'd_residence',
                'd_representative',
                'd_other',
                'd_fop_other',
                'v_form',
                'v_name',
                'v_date_of_birdth',
                'v_rc',
                'v_address',
                'v_home',
                'v_citizenship',
                'v_company',
                'v_ico',
                'v_residence',
                'v_representative',
                'v_other',
                'v_fop_other',
                'dept_amount',
                'dept_words',
                'money_receiving_account',
                'urok_form',
                'urok_amount',
                'urok_total_dept',
                'urok_total_dept_words',
                'money_receiving_part_cash_price',
                'money_receiving_part_cash_price_word',
                'money_receiving_part_transfer_price',
                'money_receiving_part_transfer_price_word',
                'money_receiving_part_transfer_account',
                'paying_form',
                'payments_kind',
                'onetime_day',
                'monthly_amount',
                'monthly_day',
                'other',
                'dalsie_dojednania',
                'sign_place',
                'sign_date',
                'return_form',
                'return_account',
                'first_monthly_payment'
            ]);

            $i = 0;
            foreach ($dlznici as $dlznik) {
                $i++;

                $dlznik["name"] = "....................................";
                $dlznik["date_of_birdth"] = "......................................";
                $dlznik["home"] = "......................................";
                $dlznik["citizenship"] = "......................................";
                $dlznik["company"] = "......................................";
                $dlznik["ico"] = "......................................";
                $dlznik["residence"] = "......................................";
                $dlznik["representative"] = "......................................";
                $dlznik["other"] = "......................................";

                foreach ($dlznik as $key => $value) {
                    if ($this->censorValue($value)) {
                        if ($key != 'dalsie_dojednania')
                            $dlznik[$key] = "......................................";
                        else
                            $dlznik[$key] = '';
                    }
                }
            }
        }

        $this->template->form = $form;
        $this->template->forms = $this->formRepository->findAll();
        $this->template->dlznici = $dlznici;
    }

    public function renderMessage()
    {
        $this->template->messages = $this->messageRepository->findAll()->order('id ASC');
    }

    public function renderMessageEdit($id)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Nemáte oprávnenie.');
            $this->redirect('Homepage:default');
        }

        $this->message_id = $id;
        $this->template->message = $this->messageRepository->find($id);
    }


    public function actionPdf($id)
    {
    }

    public function handleRemove($id)
    {
        $this->messageRepository->delete($id);
        $this->flashMessage('Správa bola vymazaná.');
        $this->redirect('this');
    }

    public function renderPage($slug)
    {
        if($slug === "message"){
            $this->redirectUrl('https://admin.ficek.sk/online-pravna-poradna?webId=2');
        }
        // setFile() does not check the file - a missing page would fail only while rendering (500)
        $file = dirname(__DIR__) . "/templates/Homepage/$slug.latte";
        if (!is_file($file)) {
            throw new BadRequestException();
        }

        $this->getTemplate()->setFile($file);
    }

    protected function createComponentMessage()
    {
        $form = new UI\Form;

        $form->addText('name', 'Vaše meno')
            ->setAttribute('placeholder', 'Meno a priezvisko')
            ->setAttribute('autocomplete', 'name')
            ->setAttribute('class', 'form-control')
            ->setRequired('Nezadali ste meno');

        $form->addText('email', 'Váš e-mail')
            ->setAttribute('placeholder', 'E-mailová adresa')
            ->setAttribute('autocomplete', 'email')
            ->setAttribute('class', 'form-control')
            ->setRequired('Nezadali ste e-mail');

        $form->addTextArea('message', 'Vaša správa')
            ->setAttribute('placeholder', 'Správa')
            ->setAttribute('class', 'form-control form-textarea')
            ->setRequired('Nezadali ste správu');

        $form->addSubmit('submit', 'Odoslať správu');

        $form->onSuccess[] = [$this, 'messageSubmitted'];

        return $form;
    }

    public function messageValidated($form)
    {
        $validator = $this->context->reCaptcha;

        if (!$validator->isValid(filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING))) {
            $form->addError('Nepodarilo sa nám overiť Vašu identitu.');
        }
    }

    public function messageSubmitted($form)
    {
        $values = $form->getValues();
        $this->emailSender->sendNewMessage($values);
        $this->flashMessage('Vaša správa bola úspešne odoslaná. Ďakujeme.', 'success');
        $this->redirect('this');
    }

    protected function createComponentContract()
    {
        $form = new Form();

        $optionsPerson = [];
        $optionsPerson['fon'] = "Fyzická osoba nepodnikateľ";
        $optionsPerson['fop'] = "Fyzická osoba podnikateľ";
        $optionsPerson['po'] = "Právnická osoba";

        $form->addSelect('v_form', 'Typ veriteľa', $optionsPerson)
            ->setAttribute('class', 'form-control person-select')
            ->setRequired('Nevybrali ste typ veriteľa!');

        $form->addText('v_name', 'Meno a priezivsko')
            ->setAttribute('placeholder', 'Meno a priezvisko')
            ->setAttribute('class', 'form-control');

        $form->addText('v_date_of_birdth', 'Dátum narodenia')
            ->setAttribute('placeholder', 'Dátum narodenia')
            ->setAttribute('class', 'form-control');

        $form->addText('v_rc', 'Rodné číslo')
            ->setAttribute('placeholder', 'Rodné číslo')
            ->setAttribute('class', 'form-control');

        $form->addText('v_address', 'Adresa')
            ->setAttribute('placeholder', 'Adresa')
            ->setAttribute('class', 'form-control');

        $form->addText('v_home', 'Bydlisko')
            ->setAttribute('placeholder', 'Trvalý pobyt')
            ->setAttribute('class', 'form-control');

        $form->addText('v_citizenship', 'Občianstvo')
            ->setAttribute('placeholder', 'Občianstvo')
            ->setAttribute('class', 'form-control');

        $form->addText('v_company', 'Názov firmy')
            ->setAttribute('placeholder', 'Názov firmy')
            ->setAttribute('class', 'form-control');

        $form->addText('v_ico', 'IČO')
            ->setAttribute('placeholder', 'IČO')
            ->setAttribute('class', 'form-control');

        $form->addText('v_residence', 'Sídlo')
            ->setAttribute('placeholder', 'Sídlo')
            ->setAttribute('class', 'form-control');

        $form->addText('v_representative', 'Zástupca')
            ->setAttribute('placeholder', 'Zástupca')
            ->setAttribute('class', 'form-control');

        $form->addText('v_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addText('v_fop_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addText('v_po_company', 'Názov firmy')
            ->setAttribute('placeholder', 'Názov firmy')
            ->setAttribute('class', 'form-control');

        $form->addText('v_po_ico', 'IČO')
            ->setAttribute('placeholder', 'IČO')
            ->setAttribute('class', 'form-control');

        $form->addText('v_po_residence', 'Sídlo')
            ->setAttribute('placeholder', 'Sídlo')
            ->setAttribute('class', 'form-control');

        $form->addText('v_po_representative', 'Zástupca')
            ->setAttribute('placeholder', 'Zástupca')
            ->setAttribute('class', 'form-control');

        $form->addText('v_po_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addSelect('d_form', 'Typ dlžníka', $optionsPerson)
            ->setAttribute('class', 'form-control person-select')
            ->setRequired('Nevybrali ste typ dlžníka!');

        $form->addText('d_name', 'Meno a priezivsko')
            ->setAttribute('placeholder', 'Meno a priezvisko')
            ->setAttribute('class', 'form-control');

        $form->addText('d_date_of_birdth', 'Dátum narodenia')
            ->setAttribute('placeholder', 'Dátum narodenia')
            ->setAttribute('class', 'form-control');

        $form->addText('d_rc', 'Rodné číslo')
            ->setAttribute('placeholder', 'Rodné číslo')
            ->setAttribute('class', 'form-control');

        $form->addText('d_address', 'Adresa')
            ->setAttribute('placeholder', 'Trvalý pobyt dlžníka')
            ->setAttribute('class', 'form-control');

        $form->addText('d_home', 'Bydlisko')
            ->setAttribute('placeholder', 'Trvalý pobyt')
            ->setAttribute('class', 'form-control');

        $form->addText('d_citizenship', 'Občianstvo')
            ->setAttribute('placeholder', 'Občianstvo')
            ->setAttribute('class', 'form-control');

        $form->addText('d_company', 'Názov firmy')
            ->setAttribute('placeholder', 'Názov firmy')
            ->setAttribute('class', 'form-control');

        $form->addText('d_ico', 'IČO')
            ->setAttribute('placeholder', 'IČO')
            ->setAttribute('class', 'form-control');

        $form->addText('d_residence', 'Sídlo')
            ->setAttribute('placeholder', 'Sídlo')
            ->setAttribute('class', 'form-control');

        $form->addText('d_representative', 'Zástupca')
            ->setAttribute('placeholder', 'Zástupca')
            ->setAttribute('class', 'form-control');

        $form->addText('d_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addText('d_fop_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addText('d_po_company', 'Názov firmy')
            ->setAttribute('placeholder', 'Názov firmy')
            ->setAttribute('class', 'form-control');

        $form->addText('d_po_ico', 'IČO')
            ->setAttribute('placeholder', 'IČO')
            ->setAttribute('class', 'form-control');

        $form->addText('d_po_residence', 'Sídlo')
            ->setAttribute('placeholder', 'Sídlo')
            ->setAttribute('class', 'form-control');

        $form->addText('d_po_representative', 'Zástupca')
            ->setAttribute('placeholder', 'Zástupca')
            ->setAttribute('class', 'form-control');

        $form->addText('d_po_other', 'Iné údaje')
            ->setAttribute('placeholder', 'Iné údaje')
            ->setAttribute('class', 'form-control');

        $form->addText('dept_amount', 'Výška dlhu')
            ->setAttribute('placeholder', 'Akú sumu požičiavate?')
            ->setAttribute('class', 'form-control');

        $form->addText('dept_words', 'Slovom')
            ->setAttribute('placeholder', 'Výška pôžičky slovom')
            ->setAttribute('class', 'form-control');

        $optionsMoneyTransfer = [
            'hotovost' => 'V hotovosti pri podpise',
            'prevodom' => 'Prevodom na bankový účet',
            'hotovost_prevodom' => 'Časť v hotovosti a časť prevodom'
        ];

        $form->addRadioList('money_receiving_form', 'Spôsob poskytnutia peňazí:', $optionsMoneyTransfer)
            ->setDefaultValue('hotovost');

        $form->addText('money_receiving_account', 'Číslo účtu')
            ->setAttribute('placeholder', 'Uveďte číslo účtu')
            ->setAttribute('class', 'form-control');

        /* Cash part */
        $form->addText('money_receiving_part_cash_price', 'Suma')
            ->setAttribute('placeholder', 'Uveďte výšku sumy')
            ->setAttribute('class', 'form-control');

        $form->addText('money_receiving_part_cash_price_word', 'Suma slovom')
            ->setAttribute('placeholder', 'Uveďte sumu slovom')
            ->setAttribute('class', 'form-control');

        /* Transfer part */
        $form->addText('money_receiving_part_transfer_price', 'Suma')
            ->setAttribute('placeholder', 'Uveďte výšku sumy')
            ->setAttribute('class', 'form-control');

        $form->addText('money_receiving_part_transfer_price_word', 'Suma slovom')
            ->setAttribute('placeholder', 'Uveďte sumu slovom')
            ->setAttribute('class', 'form-control');

        $form->addText('money_receiving_part_transfer_account', 'Číslo účtu')
            ->setAttribute('placeholder', 'Uveďte číslo účtu dlžníka')
            ->setAttribute('class', 'form-control');

        $optionsDebt = [
            'bezurocne' => 'Bezúročne',
            's_urokom' => 'S úrokom'
        ];

        $form->addRadioList('urok_form', 'Vyberte spôsob úročenia:', $optionsDebt)
            ->setDefaultValue('bezurocne');

        $form->addText('urok_amount', 'Výška úroku')
            ->setValue(' €')
            ->setAttribute('placeholder', 'Uveďte výšku úroku')
            ->setAttribute('class', 'form-control');

        $form->addText('urok_total_dept', 'Číslo účtu')
            ->setAttribute('placeholder', 'Celková suma na vrátenie')
            ->setAttribute('class', 'form-control');

        $form->addText('urok_total_dept_words', 'Číslo účtu')
            ->setAttribute('placeholder', 'Suma na vrátenie slovom')
            ->setAttribute('class', 'form-control');

        $optionsPayment = [
            'onetime' => 'Jednorázovo',
            'payments' => 'V splátkach'
        ];

        $form->addRadioList('paying_form', 'Spôsob splácania:', $optionsPayment)
            ->setDefaultValue('onetime');

        $form->addText('onetime_day', 'Jednorázovo dňa')
            ->setAttribute('placeholder', 'Dátum vrátenia dlhu')
            ->setAttribute('class', 'form-control date');

        $optionsPaymentTime = [
            'monthly' => 'V mesačných splátkach',
            'other' => 'V iných ako mesačných splátkach'
        ];

        $form->addRadioList('payments_kind', 'Spôsob splácania:', $optionsPaymentTime)
            ->setDefaultValue('monthly')
            ->addConditionOn($form['paying_form'], UI\Form::IS_IN, ['payments'])
            ->addRule(UI\Form::FILLED, 'Nevybrali ste formu splácania.', TRUE);

        $form->addText('monthly_amount', 'Mesačne vo výške')
            ->setAttribute('placeholder', 'Výška mesačnej splátky')
            ->setAttribute('class', 'form-control');

        $optionsPaymentMonthly = [
            'do 10. dňa v mesiaci' => 'do 10. dňa v mesiaci',
            'do 15. dňa v mesiaci' => 'do 15. dňa v mesiaci',
            'do 20. dňa v mesiaci' => 'do 20. dňa v mesiaci',
            'do 25. dňa v mesiaci' => 'do 25. dňa v mesiaci'
        ];

        $form->addSelect('monthly_day', 'Mesačne vždy k', $optionsPaymentMonthly)
            ->setAttribute('class', 'form-control')
            ->setPrompt('Vyberte');

        $form->addText('first_monthly_payment', 'Uveďte dátum prvej splátky')
            ->setAttribute('placeholder', 'Uveďte dátum')
            ->setAttribute('class', 'form-control');

        $form->addTextArea('other', 'Iné')
            ->setAttribute('placeholder', 'Uveďte čas a spôsob úhrady dlhu')
            ->setAttribute('class', 'form-control form-textarea mt-20');

        $form->addTextArea('dalsie_dojednania', 'Ďalšie dojednania')
            ->setAttribute('placeholder', 'Sem vložte prípadne ďalšie dojednania, ak to považujete za potrebné.')
            ->setAttribute('class', 'form-control form-textarea');

        $optionsMoneyReturn = [
            'hotovost' => 'V hotovosti',
            'prevodom' => 'Prevodom na bankový účet'
        ];

        $form->addRadioList('return_form', 'Vyberte spôsob vrátenia:', $optionsMoneyReturn)
            ->setDefaultValue('hotovost');

        $form->addText('return_account', 'Číslo účtu')
            ->setAttribute('placeholder', 'Uveďte číslo účtu')
            ->setAttribute('class', 'form-control');

        $form->addText('sign_place', 'Miesto podpisu')
            ->setAttribute('placeholder', 'Obec alebo mesto')
            ->setAttribute('class', 'form-control');

        $form->addText('sign_date', 'Dátum podpisu')
            ->setAttribute('placeholder', 'Dátum podpisu')
            ->setAttribute('class', 'form-control');

        $form->addText('send_by_email', 'Dokument odoslať na e-mail:')
            ->setAttribute('placeholder', 'E-mailová adresa')
            ->setAttribute('autocomplete', 'email')
            ->setAttribute('class', 'form-control')
            ->setDefaultValue('');

        $form->addHidden('d_counter', '')
            ->setDefaultValue(0);

        for ($i = 1; $i <= 20; $i++) {
            $form->addSelect("d_form$i", 'Dlžník', $optionsPerson)
                ->setAttribute('class', 'form-control person-select');

            $form->addText("d_name$i", 'Meno a priezivsko')
                ->setAttribute('placeholder', 'Meno a priezvisko dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_date_of_birdth$i", 'Dátum narodenia')
                ->setAttribute('placeholder', 'Dátum narodenia dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_rc$i", 'Rodné číslo')
                ->setAttribute('placeholder', 'Rodné číslo')
                ->setAttribute('class', 'form-control');

            $form->addText("d_address$i", 'Adresa')
                ->setAttribute('placeholder', 'Adresa trvalého bydliska dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_home$i", 'Bydlisko')
                ->setAttribute('placeholder', 'Aktuálne bydlisko dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_citizenship$i", 'Občianstvo')
                ->setAttribute('placeholder', 'Občianstvo dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_company$i", 'Názov firmy')
                ->setAttribute('placeholder', 'Vyplňte názov firmy dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_ico$i", 'IČO')
                ->setAttribute('placeholder', 'Vyplňte IČO dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_residence$i", 'Sídlo')
                ->setAttribute('placeholder', 'Vyplňte sídlo dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_representative$i", 'Zástupca')
                ->setAttribute('placeholder', 'Vyplňte zástupcu dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_other$i", 'Iné údaje')
                ->setAttribute('placeholder', 'Vyplňte iné údaje')
                ->setAttribute('class', 'form-control');

            $form->addText("d_fop_other$i", 'Iné údaje')
                ->setAttribute('placeholder', 'Vyplňte iné údaje')
                ->setAttribute('class', 'form-control');

            $form->addText("d_po_company$i", 'Názov firmy')
                ->setAttribute('placeholder', 'Vyplňte názov firmy dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_po_ico$i", 'IČO')
                ->setAttribute('placeholder', 'Vyplňte IČO dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_po_residence$i", 'Sídlo')
                ->setAttribute('placeholder', 'Vyplňte sídlo dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_po_representative$i", 'Zástupca')
                ->setAttribute('placeholder', 'Vyplňte zástupcu dlžníka')
                ->setAttribute('class', 'form-control');

            $form->addText("d_po_other$i", 'Iné údaje')
                ->setAttribute('placeholder', 'Vyplňte iné údaje')
                ->setAttribute('class', 'form-control');
        }

        $form->addReCaptcha(
            'captcha',
            'reCAPTCHA for you',
            "Prosím potvrďte že nejste robot"
        );

        $form->onSubmit[] = [$this, 'contractSubmitted'];

        return $form;
    }

    public function contractSubmitted($form)
    {
        $values = $form->getValues();

        if(!$_POST['g-recaptcha-response']){
            exit;
        }

        //pro pridani meny
        $priceKeys = ['dept_amount', 'urok_total_dept', 'monthly_amount', 'money_receiving_part_cash_price', 'money_receiving_part_transfer_price'];

        foreach($priceKeys as $key){
            if(isset($values[$key])){
                if ($values[$key] != "") {
                    $currency = $_POST['currency-' . $key] == 'other_currency' ? $_POST['currency-other-' . $key] : $_POST['currency-' . $key];
                    $values[$key] = $this->transformPrice($values[$key]) . ' ' . $currency;
                }
            }
        }

        //pole pro vyplneni teckami
        $keys = [
            'd_name', 'd_date_of_birdth', 'd_home', 'd_citizenship', 'd_company', 'd_ico', 'd_residence', 'd_representative', 'd_other', 'd_fop_other',
            'd_po_company', 'd_po_ico', 'd_po_residence', 'd_po_representative', 'd_po_other',
            'v_name', 'v_date_of_birdth', 'v_home', 'v_citizenship', 'v_company', 'v_ico', 'v_residence', 'v_representative', 'v_other', 'v_fop_other',
            'dept_amount', 'dept_words', 'onetime_day', 'monthly_amount', 'monthly_day', 'other', 'sign_place', 'return_account',
            'urok_amount', 'urok_total_dept', 'urok_total_dept_words'
        ];

        //dlznik klice
        for ($i = 1; $i <= 20; $i++) {
            $keys[] = "d_name$i";
            $keys[] = "d_date_of_birdth$i";
            $keys[] = "d_rc$i";
            $keys[] = "d_address$i";
            $keys[] = "d_home$i";
            $keys[] = "d_citizenship$i";
            $keys[] = "d_ico$i";
            $keys[] = "d_residence$i";
            $keys[] = "d_other$i";
            $keys[] = "d_fop_other$i";
            $keys[] = "d_residence$i";
            $keys[] = "d_po_representative$i";
            $keys[] = "d_po_other$i";
            $keys[] = "d_po_residence$i";
            $keys[] = "d_po_ico$i";
        }

        //fill free spaces by dots
        foreach($keys as $key){
            if(isset($values[$key])){
                if($values[$key] == ""){
                    $values[$key] = self::DOTS;
                }
            }
        }

        $sendEmail = $values['send_by_email'];

        $file = dirname(__DIR__) . "/templates/Homepage/form_pdf.latte";

        $template = $this->templateFactory->createTemplate()
            ->setFile($file);

        $template->form = $values;
        $html = $template->__toString();


        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);

        $path = dirname(__DIR__, 2) . "/temp/pdfs/";
        if (!file_exists($path)) {
            mkdir($path);
        }

        $number = random_int(5, 15);

        $filename = dirname(__DIR__, 2) . "/temp/pdfs/zmluva-o-pozicke-" . $number . ".pdf";

        $mpdf->Output($filename, 'F');

        if ($sendEmail != "") {
            $this->emailSender->sendForm($sendEmail, $filename, $number);
        }

        $mpdf->Output('zmluva-o-pozicke.pdf', 'I');
    }

    public function transformPrice($string)
    {
        $string = str_replace(' ', '', $string);
        $number = floatval(str_replace(',', '.', str_replace('.', '', $string)));
        $number = number_format($number, 2, ',', '.');
        $number = str_replace(',00', '', $number);

        return $number;
    }
}
