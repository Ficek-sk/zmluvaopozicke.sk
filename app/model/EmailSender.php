<?php

use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Mail\Message;

class EmailSender extends Repository
{

    private $email;

    /** @var TemplateFactory */
    private $templateFactory;

    public function __construct(\Nette\Database\Explorer $db, \Nette\DI\Container $container, TemplateFactory $templateFactory)
    {
        parent::__construct($db);
        $this->email = $container->getParameters()['email'];
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param string $templateFile
     * @return \Nette\Bridges\ApplicationLatte\Template
     */
    public function createTemplate($templateFile)
    {
        $template = $this->templateFactory->createTemplate();
        $template->setFile($templateFile);

        return $template;
    }

    /**
     * @return Nette\Mail\SmtpMailer
     */
    private function createMailer()
    {
        return new Nette\Mail\SmtpMailer(
            (string) $this->email['host'],
            (string) $this->email['username'],
            (string) $this->email['password'],
            null,
            'ssl'
        );
    }

    public function sendEmail($emailAddress, $replyTo, $subject, $template)
    {
        try {
            $mailer = $this->createMailer();

            $mail = new Message;

            $mail->setFrom($this->email['setFrom'], 'Zmluvaopozicke.sk')
                ->addTo($emailAddress)
                ->setSubject($subject)
                ->setHtmlBody((string) $template);

            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            $mailer->send($mail);

        } catch (Exception $e) {
            echo "E-mail neodoslaný";
            echo $e->getMessage();
        }
    }

    public function sendNewMessage($message)
    {
        $templateFile = dirname(__DIR__) . '/templates/Emails/message.latte';
        $template = $this->createTemplate($templateFile);
        $template->message = $message;
        $replyTo = $message->email;

        $emailAddress = "advokat@ficek.sk";
        $subject = "Zmluvaopozicke.sk - Nová správa";

        // Email template variables
        $template->url = "https://zmluvaopozicke.sk";
        $template->logo = "Zmluva o pôžičke";
        $template->domain = "Zmluvaopozicke.sk";
        $template->domainUppercase = "ZMLUVAOPOZICKE.SK";
        $template->subject = "NOVÁ SPRÁVA";
        $template->title = "Bola odoslaná nová správa cez kontaktný formulár";
        $template->preview = "Dobrý deň, bola odoslaná nová správa cez kontaktný formulár na stránke www.zmluvaopozicke.sk";
        $template->primaryColor = "393d60";
        $template->secondaryColor = "484e7c";
        $template->facebook = "https://www.facebook.com/advokat.bratislava.judr.ficek";

        // Send email
        $this->sendEmail($emailAddress, $replyTo, $subject, $template);
    }

    public function sendForm($sendEmail, $pdffile, $contractId)
    {
        $templateFile = dirname(__DIR__) . '/templates/Emails/contract.latte';
        $template = $this->createTemplate($templateFile);
        $template->contractId = $contractId;
        $emailAddress = $sendEmail;
        $subject = "Zmluvaopozicke.sk - Vaša zmluva o pôžičke";

        // Email template variables
        $template->url = "https://zmluvaopozicke.sk";
        $template->logo = "Zmluva o pôžičke";
        $template->domain = "Zmluvaopozicke.sk";
        $template->domainUppercase = "ZMLUVAOPOZICKE.SK";
        $template->subject = "VAŠA ZMLUVA";
        $template->title = "Posielame Vám vytvorenú zmluvu o pôžičke";
        $template->preview = "Dobrý deň, posielame Vám zmluvu o pôžičke, ktorú ste si vytvorili na stránke www.zmluvaopozicke.sk";
        $template->contract = "zmluvy";
        $template->primaryColor = "393d60";
        $template->secondaryColor = "484e7c";
        $template->ficek = "https://ficek.sk";
        $template->ficekUppercase = "FICEK.SK";
        $template->facebook = "https://www.facebook.com/advokat.bratislava.judr.ficek";

        try {
            $mailer = $this->createMailer();

            $mail = new Message;
            $mail->addAttachment($pdffile);

            $mail->setFrom($this->email['setFrom'], 'Zmluvaopozicke.sk')
                ->addTo($emailAddress)
                ->setSubject($subject)
                ->setHtmlBody((string) $template);

            $mailer->send($mail);

        } catch (Exception $e) {
            echo "E-mail neodoslaný";
            echo $e->getMessage();
        }
    }
}
