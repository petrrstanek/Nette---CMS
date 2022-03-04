<?php

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use App\Model\MyAuthenticator;
use Nette\Security\Passwords;

final class SignPresenter extends BasePresenter
{
	use Nette\SmartObject;
	private postModel $model;
	private myAuthenticator $auth;
	private Passwords $passwords;

	public function __construct(PostModel $model, myAuthenticator $auth, Passwords $passwords)
	{
		parent::__construct($model, $auth, $passwords);
		$this->model = $model;
		$this->auth = $auth;
		$this->passwords = $passwords;
	}

	protected function createComponentSignInForm(): Form
	{
		$signForm = new Form;
		$signForm->addText('username', 'Uživatelské jméno')
			 ->setRequired('Prosím vyplňte své uživatelské jméno.');

		$signForm->addPassword('password', 'Heslo:')
			 ->setRequired('Prosím vyplňtě své heslo.');

		$signForm->addSubmit('send', 'Přihlásit');
		return $signForm;
	}

	public function actionIn()
	{
		$this->getComponent('signInForm')->onSuccess[] = [$this, 'signInFormProcess'];
	}

	public function actionOut(): void
	{
		$this->user->logout();
		$this->flashMessage('Odhlášení problěho úspěšně');
		$this->redirect('Homepage:');
	}

	public function signInFormProcess(\stdClass $data): void
	{
			$user = $this->getUser();
			$user->setAuthenticator($this->auth);
			$user->login($data->username, $data->password);
			$this->redirect('Admin:dashboard');
	}

	protected function createComponentRegisterForm(): Form
	{
		$regForm = new Form;
		$regForm->addText('username', 'Uživatelské jméno')
		->setRequired('Prosím vyplňte');

		$regForm->addPassword('password', 'Heslo')
		->addRule($regForm::MIN_LENGTH, 'Heslo musí mít alespoň 8 znaků', 8)
		->setRequired('Prosím vyplňte');

		$regForm->addPassword('cpassword', 'Potvrdit Heslo')
		->addRule($regForm::EQUAL, 'Hesla se neshodují', $regForm['password'])
		->setOmitted()
		->setRequired('Prosím vyplňte');

		$regForm->addSubmit('send', 'Registrovat');
		return $regForm;
	}

	public function actionRegister()
	{
		$this->getComponent('registerForm')->onSuccess[] = [$this, 'registerProcess'];
	}

	public function registerProcess(\stdClass $values): void
	{
		try{
			$this->passwords = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
			$hash = $this->passwords->hash($values->password);
			$this->model->getUsers()->insert([
				'username' => $values->username,
				'password' => $hash,
			]);
			$this->flashMessage('Úspěšně jste se registrovali.');
			$this->redirect('Sign:in');
		}
		catch(Nette\Database\UniqueConstraintViolationException $e){
			$this->flashMessage('Zadané jméno již existuje, prosím zvolte jiné');
			$this->redirect('this');
		}
	}
}