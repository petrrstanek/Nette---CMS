<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostModel;

final class TagsFactory
{
  private $model;
  private $tag;

  public function __construct(postModel $model)
  {
    $this->model = $model;
  }

  public function createTag($tag): Form
  {
    $this->tag = $tag;
    $tagForm = new Form;
    $tagForm->addText('name', 'Název Kategorie: ')->setRequired();
    $tagForm->addSubmit('send', 'Aktualizovat')->setHtmlAttribute('class', 'button_submit');
    if(!$this->tag){
      $tagForm->onSuccess[] = [$this, 'tagProcess'];
    } else {
      $tagForm->onSuccess[] = [$this, 'tagEditProcess'];
    }
    
    return $tagForm;
  }

  public function tagProcess(Form $form, \stdClass $values): void
  {
       try{
        $this->model->getTags()->insert([
          'name' => $values->name,
        ]);
    }catch(Nette\Database\UniqueConstraintViolationException $e){
      $form->addError('Zadaný tag již existuje');
    }
  }

  public function tagEditProcess(array $values): void
  {
    $this->tag->update($values);
  }

  public function createAddTagForm($pageId): Form
	{
		$this->page = $pageId;
		$form = new Form;
    
		$form->addMultiSelect('tags', 'Kategorie:', $this->model->fetchTags())
			->setRequired();
		$form->addSubmit('send', 'Přidat kategorii');

		$form->onSuccess[] = [$this, 'AddTagProcess'];
		return $form;
	}

	public function addTagProcess(Form $form, \stdClass $values): void
	{
		try{
			foreach($values->tags as $tag){
				$this->model->getRelatedTags()->insert([
					'page_id' => $this->page,
					'tag_id' => $tag,
				]);
			}
		}catch(Nette\Database\UniqueConstraintViolationException $e) {
			$form->addError('Stránka již obsahuje jednu z kategorii');
		}
	}
}