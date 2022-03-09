<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostModel;
use Nette\Utils\DateTime;

final class FormFactory
{
	private postModel $model;
	private $page;

	public function __construct(postModel $model)
	{
		$this->model = $model;
	}

	public function createPageForm($page): Form
	{
		$this->page = $page;
		$pageForm = new Form();
		$pageForm->addText('title', 'Titulek:')->setRequired();
		$pageForm->addMultiSelect('tags', 'Kategore: ', $this->model->fetchTags());
		$pageForm->onSuccess[] = [$this, 'pageProcess'];
		

		$pageForm->addTextArea('content', 'Obsah')
		->setHtmlAttribute('id', 'editor')
		->setRequired();

		$pageForm->addSubmit('send', 'Aktualizovat')
		->setHtmlAttribute('class',  'button__submit');

		if($this->page){
			$pageForm->onSuccess[] = [$this, 'pageEditProcess'];
		}
		
		return $pageForm;
	}	

	public function pageProcess(Form $form, \stdClass $values): void
	{
		try{
			$page = $this->model->getPages()->insert([
				'title' => $values->title,
				'content' => $values->content,
				'updatedAt' => new Datetime(),
				'createdAt' => new DateTime(),
			]);

			foreach($values->tags as $tag){
				$this->model->getRelatedTags()->insert([
					'page_id' => $page->id,
					'tag_id' => $tag,
				]);
			}
		} catch(AnyModelException $e){
			$form->addError('Error');
		}
	}

	public function pageEditProcess(Form $form, \stdClass $values): void
	{
		try{
			$this->page->update([
				'updatedAt' => new DateTime(),
				'title' => $values->title,
				'content' => $values->content,
			]);
			foreach($values->tags as $tag){
				$this->model->getRelatedTags()->insert([
					'page_id' => $this->page->id,
					'tag_id' => $tag
				]);	
			}
			
		} catch(AnyModelException $e){
			$form->addError('Error');
		}
	}
}