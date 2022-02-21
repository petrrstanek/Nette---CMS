<?php
declare(strict_types=1);

namespace App\Model;
use Nette;
use Nette\Security\SimpleIdentity;

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
		
		bdump($row);

		if(!$row){
			throw new Nette\Security\AuthenticationException('Uživatel nebyl nalezen');
		}
		bdump($password);

		if($password !== $row->password){
			throw new Nette\Security\AuthenticationException('Špatné heslo');
		}

		return new SimpleIdentity(
			$row->id,
			['name' => $row->username]
		);
	}
}