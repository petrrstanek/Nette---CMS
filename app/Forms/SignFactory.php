<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostModel;
use App\Model\MyAuthenticator;
use Nette\Security\Passwords;
use Nette\Security\User;

final class SignFactory
{
  private $model;
  private $auth;
  private $passwords;
  private $user;
  
  public function __construct(postModel $model, myAuthenticator $auth, Passwords $passwords, User $user)
  {
    $this->model = $model;
    $this->auth = $auth;
    $this->passwords = $passwords;
    $this->user = $user;
  }

  public function createSignIn(): Form
  {
    $signInForm = new Form;
   
    $signInForm  ->addText('username', 'Uživatelské jméno')->setRequired('Prosím vyplňte své uživatelské jméno.');
    $signInForm->addPassword('password', 'Heslo:') ->setRequired('Prosím vyplňte své heslo.');
    
    $signInForm->addSubmit('send', 'Přihlásit');
    $signInForm->onSuccess[] = [$this, 'signInProcess'];
    return $signInForm;
  }

  public function signInProcess(Form $signIn, \stdClass $data): void
  {
    $this->user->setAuthenticator($this->auth);
    try{
      $this->user->login($data->username, $data->password);  
    }catch(Nette\Security\AuthenticationException $e){
      $signIn->addError('Špatné jméno nebo heslo');
    }  
  }

  public function createSignUp(): Form
  {
    $signUp = new Form;
    $signUp->addText('username', 'Uživatelské jméno')
    ->setRequired('Prosím vyplňte');

    $signUp->addPassword('password', 'Heslo:')
    ->addRule($signUp::MIN_LENGTH, 'Heslo musí mít alespoň 8 znaků.', 8)
    ->setRequired('Prosím vyplňtě');

    $signUp->addPassword('cpassword', 'Potvrdit Heslo')
    ->addRule($signUp::EQUAL, 'Hesle se neshodují', $signUp['password'])
    ->setOmitted()
    ->setRequired('Prosím vyplňte');

    $signUp->addSubmit('send', 'Registrovat');
    $signUp->onSuccess[] = [$this, 'signUpProcess'];
    return $signUp;
  }

  public function signUpProcess(Form $signUp, \stdClass $values): void
  {
    try{
      $this->passwords = new Passwords(PASSWORD_BCRYPT, ['cost' => 12]);
      $hash = $this->passwords->hash($values->password);
      $this->model->getUsers()->insert([
        'username' => $values->username,
        'password' => $hash,
      ]);
    }
    catch(Nette\Database\UniqueConstraintViolationException $e){
      $signUp->addError('Zadané jméno již existuje, prosím zvolte jiné');
    }
  }
}