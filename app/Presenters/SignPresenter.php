<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostModel;
use App\Forms\SignFactory;
use App\Presenters\BasePresenter;

final class SignPresenter extends BasePresenter
{
	use Nette\SmartObject;
	private  $model;
	private  $signFactory;

	public function __construct(PostModel $model, SignFactory $signFactory)
	{
		parent::__construct($model, $signFactory);
		$this->model = $model;
		$this->signFactory = $signFactory;
	}

	protected function createComponentSignInForm(): Form
	{
		return $this->signFactory->createSignIn();
	}
	
	public function actionIn()
	{
		$this->getComponent('signInForm')->onSuccess[] = function(Form $form)
		{
			$this->flashMessage('Přihlášení proběhlo úspěšně');
			$this->redirect('Admin:dashboard');
		};
	}

	public function actionOut(): void
	{
		$this->user->logout();
		$this->flashMessage('Odhlášení problěho úspěšně');
		$this->redirect('Homepage:');
	}

	protected function createComponentSignUpForm(): Form
	{
		return $this->signFactory->createSignUp();
	}

	public function actionSignUp()
	{
		$this->getComponent('signUpForm')->onSuccess[] = function(Form $form)
		{
			$this->flashMessage('Úspěšně jste se zaregistrovali.');
			$this->redirect('Sign:in');
		};
	}
}