<?php
declare(strict_types=1);

namespace App\Model;
use Nette;
use Nette\Application\Routers\SimpleRouter;
use Nette\Security\SimpleIdentity;
use Nette\Security\IIdentity;

class MyAuthenticator implements Nette\Security\Authenticator
{
	use Nette\SmartObject;
	private $database;
	private $passwords;

	public function __construct(Nette\Database\Explorer $database, Nette\Security\Passwords $passwords)
	{
		$this->database = $database;
		$this->passwords = $passwords;
	}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		$row = $this->database->table('users')
		->where('username', $username)
		->fetch();
		
		if(!$row){
			throw new Nette\Security\AuthenticationException('Uživatel nebyl nalezen');
		}

		 else if(!$this->passwords->verify($password, $row->password)){
			throw new Nette\Security\AuthenticationException('Špatné heslo');
		}

		return new SimpleIdentity(
			$row->id,
			['name' => $row->username]
		);
	}
}