<?php

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;


final class SignPresenter extends BasePresenter
{
	protected function createComponentSignInForm(): Form
	{
		$signForm = new Form;
		$signForm->addText('username', 'Uživatelské jméno')
			 ->setRequired('Prosím vyplňte své uživatelské jméno.');

		$signForm->addPassword('password', 'Heslo:')
			 ->setRequired('Prosím vyplňtě své heslo.');

		$signForm->addSubmit('send', 'Přihlásit');
		$signForm->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $signForm;
	}

	public function signInFormSucceeded(Form $signForm, \stdClass $data): void
	{
		try {
			$this->getUser()->login($data->username, $data->password);
			$this->redirect('Homepage:');
		} catch (Nette\Security\AuthenticationException $e) {
			$signForm->addError('Nesprávné přihlašovací jméno nebo heslo');
		}
	}

	public function actionOut(): void
	{
		$this->user->logout();
		$this->flashMessage('Odhlášení problěho úspěšně');
		$this->redirect('Homepage:');
	}
}