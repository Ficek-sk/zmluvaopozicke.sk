<?php

use Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

	/** @var UserRepository @inject */
	public $userRepository;

	/** @var EmailSender @inject */
	public $emailSender;

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = new UI\Form;
		$form->addText('username', 'Prihlasovacie meno:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Ostať prihlásený');

		$form->addSubmit('send', 'Prihlásiť');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}

	public function signInFormSucceeded($form) {
		$values = $form->getValues();

		if ($values->remember) {
			$this->getUser()->setExpiration('+ 14 days', false);
		} else {
			$this->getUser()->setExpiration('+ 20 minutes', true);
		}

		try {
			$this->getUser()->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->redirect('Homepage:');
	}

	public function actionOut()	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

	protected function createComponentForgottenPasswordForm()	{
		$form = new UI\Form;

		$form->addText('username', 'Prihlasovacie meno:')
			->setRequired('Prosím zadajte prihlasovacie meno.');

		$form->addSubmit('send', 'Odoslať');

		$form->onSuccess[] = [$this, 'forgottenPasswordFormSubmitted'];
		return $form;
	}

	public function forgottenPasswordFormSubmitted($form)	{
		$values = $form->getValues();
		$user = $this->userRepository->find_by_username($values['username']);

		if ($user) {
			//zákazník  existuje
			$this->userRepository->resetPassword($this->emailSender, $user);
      $this->flashMessage('Na vašu e-mailovú adresu bolo zaslané nové heslo.');
      $this->redirect('in');

		} else {
			//zákazník  neexistuje
			$form->addError('Zákazník s týmito údajmi v systéme neexistuje.');
		}
	}
}
