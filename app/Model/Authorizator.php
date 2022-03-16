<?php
declare(strict_types = 1);

namespace App\Model;

use Nette;

class Authorizator implements Nette\Security\Authorizator
{
  public function isAllowed($role, $resource, $operation): bool
  {
    if($role === 'admin'){
      return true;
    }

    if($role === 'user' && $resource === 'page'){
      return true;
    } 

    $acl = new Nette\Security\Permission;

    $acl->addRole('guest');
    $acl->adRole('registered', 'guest');
    $acl->addRole('admin', 'registered');
    
    $acl->addResource('page');
    $acl->addResource('edit');

    $acl->allow('admin', $acl::ALL, ['view', 'edit', 'add']);

    return false;
  }
}