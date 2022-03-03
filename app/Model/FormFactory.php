<?php

namespace App\Model;

use Nette;
use Nette\Application\UI\Form;

class FormFactory
{
	private $facade;

	public function __construct(Facade $facade)
	{

		$this->facade = $facade;
	}

	public function create(): Form
	{
		$form = new Form;

		$form->addRadioList('tags', 'Kategorie:', $this->model->fetchTagManager())
			->setRequired();

		$form->addSubmit('send', 'Přidat kategorii');

		$form->onSuccess[] = [$this, 'processForm'];

		return $form;

	}

	public function processForm(Form $addTagForm, array $values): void
	{
		try{
			$this->facade->process($values);

		}catch(AnyModelException $e) {
			$addTagForm->addError('ERROR');
		}

	}
}
