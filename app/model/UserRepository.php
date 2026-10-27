<?php

/**
 * Tabulka user
 *
 */
class UserRepository extends Repository
{

	public function find($id) {
		return $this->findBy(['id' => $id])->fetch();
	}

	public function find_by_username($username) {
		return $this->findBy(['username' => $username])->fetch();
	}

	public function resetPassword($emailSender, $user) {
		$user = $user;

		$salt = Authenticator::generateSalt();
		$password = Authenticator::generatePassword();

    $this->getTable()->where(['id' => $user->id])->update(['password_hash' => base64_encode(Authenticator::calculateHash($password,$salt)), 'password_salt' => $salt]);

    $templateFile = dirname(__DIR__) . '/templates/Emails/forgotten.latte';
		$template = $emailSender->createTemplate($templateFile);
		$template->password = $password;
		$template->name = $user->name;

		$emailAddress=$user->email;
		$subject = "Zmluva o pôžičke - Obnovenie hesla";

		$emailSender->sendEmail($emailAddress, null, $subject, $template);

		//return $password; /// VYMAZAT!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	    //$emailSender->sendPassword($user, $password);
	}
}
