<?php
declare(strict_types=1);

namespace Nette\Security;
use Nette;
use Nette\Security\SimpleIdentity;

class MyAuthenticator implements Nette\Security\Authenticator
{
	private $database;
	private $passwords;

	public function __construct(
		 Nette\Database\Explorer $database,
		 Nette\Security\Passwords $passwords
	){
		$this->database = $database;
		$this->passwords = $passwords;
	}
}