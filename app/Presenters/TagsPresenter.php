<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;

class TagsPresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	protected function createComponentControlTagForm(): Form
	{
		$formTag = new Form();
		$formTag->addText('name', 'Název Kategorie:')->setRequired();
		$formTag->addSubmit('send', 'Aktualizovat')->setHtmlAttribute('class', 'button_submit');
		return $formTag;
	}

	public function actionEditTag(int $tagId): void
	{
		
		$tag = $this->model->getTags()->get($tagId);
		$formTag = $this->getComponent('controlTagForm');
		$formTag->setDefaults($tag->toArray());
		$formTag->onSuccess[] = [$this, 'editTagProcess'];
	}

	public function actionCreateTag(): void
	{
		$formTag = $this->getComponent('controlTagForm');
		$formTag->onSuccess[] = [$this, 'createTagProcess'];
	}

	public function editTagProcess(\stdClass $values): void
	{
		$tagId = $this->getParameter('tagId');
		$tag = $this->model->getTags()->get($tagId);
			$tag->update([
				'name' => $values->name,
			]);
			$this->flashMessage('Kategorie byla aktualizována.');
			$this->redirect('Tags:');
	}

	public function createTagProcess(\stdClass $values): void
	{
		$tagId = $this->getParameter('tagId');
		$tags = $this->model->getTags()->select('name');
		$exist = false;

			foreach ($tags as $tag) {
				if ($tag->name == $values->name) {
					$this->flashMessage('Kategorie: ' . "$values->name" . ' již existuje');
					$this->redirect('this');
					$exist = true;
					break;
				}
			}
			if ($exist === false) {
				$this->model->getTags()->insert([
					'name' => $values->name,
				]);
				$this->flashMessage('Úspěšně jste přidal kategorii: ' . "$values->name" . ' ', 'success');
				$this->redirect('Tags:');
			}
		}


	function handleDelete($tagId)
	{
		parent::startup();
			if($this->getUser()->isLoggedIn())
			{
				$this->redirect('Sign:in');
			} else{
				try 
				{
					$tagId = $this->getParameter('tagId');
					$tagToDelete = $this->model
						->getTags()
						->get($tagId)
						->delete();
					$this->flashMessage('Kategorie byla smazána.');
					$this->redirect('Tags:');
				} 
				catch (Nette\Database\ForeignKeyConstraintViolationException $e) 
				{
					$this->flashMessage('Nelze odstranit, protože jeden z příspěvku obsahuje tuto kategorii.');
					$this->redirect('this');
				}
			}
	}

	public function renderDefault(): void
	{
		$this->template->tags = $this->model->getTags();
	}
}
