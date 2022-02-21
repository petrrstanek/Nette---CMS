<?php

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use App\Model\MyAuthenticator;

final class SignPresenter extends Nette\Application\UI\Presenter
{

	use Nette\SmartObject;

	private postModel $model;

	private myAuthenticator $auth;

	public function __construct(PostModel $model, myAuthenticator $auth)
	{
		parent::__construct($model, $auth);
		$this->model = $model;
		$this->auth = $auth;

	}

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
			$user = $this->getUser();
			bdump($user);
			$user->setAuthenticator($this->auth);
			$user->login($data->username, $data->password);
			$this->redirect('Homepage:');
		
	}

	public function actionOut(): void
	{
		$this->user->logout();
		$this->flashMessage('Odhlášení problěho úspěšně');
		$this->redirect('Homepage:');
	}

	protected function createComponentRegisterForm(): Form
	{
		$regForm = new Form;

		$regForm->addText('username', 'Uživatelské jméno(email)')
		->setRequired('Prosím vyplňte');

		$regForm->addPassword('password', 'Heslo')
		->addRule($regForm::MIN_LENGTH, 'Heslo musí mít alespoň 8 znaků', 8)
		->setRequired('Prosím vyplňte');

		$regForm->addPassword('cpassword', 'Potvrdit Heslo')
		->addRule($regForm::EQUAL, 'Hesla se neshodují', $regForm['password'])
		->setOmitted()
		->setRequired('Prosím vyplňte');

		$regForm->addSubmit('send', 'Registrovat');
		
		$regForm->onSuccess[] = [$this, 'registerProcess'];

		return $regForm;
	}


	public function registerProcess(Form $regForm, \stdClass $values): void
	{
		$this->model->getUsers()->insert([
			'username' => $values->username,
			'password' => $values->password 
		]);
		$this->flashMessage('Úspěšně jste se registrovali.');
		$this->redirect('Sign:in');
	}
}