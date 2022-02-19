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
			$this->redirect('Tags:default');
	}

	public function createTagProcess(\stdClass $values): void
	{
		$tagId = $this->getParameter('tagId');
		$tags = $this->model->getTags()->select('name');
		$exist = false;

			foreach ($tags as $tag) {
				if ($tag->name == $values->name) {
					$this->flashMessage('Kategorie: ' . "$values->name" . ' již existuje');
					$this->redirect('Tags:default');
					$exist = true;
					break;
				}
			}
			if ($exist === false) {
				$this->model->getTags()->insert([
					'name' => $values->name,
				]);
				$this->flashMessage('Úspěšně jste přidal kategorii: ' . "$values->name" . ' ', 'success');
				$this->redirect('Tags:default');
			}
		}


	function handleDelete($tagId)
	{
		try 
		{
			$tagId = $this->getParameter('tagId');
			$tagToDelete = $this->model
				->getTags()
				->get($tagId)
				->delete();
			$this->flashMessage('Kategorie byla smazána.');
			$this->redirect('Tags:default');
		} 
		catch (Nette\Database\ForeignKeyConstraintViolationException $e) 
		{
			$this->flashMessage('Nelze odstranit');
			$this->redirect('this');
		}
	}

	public function renderDefault(): void
	{
		$tags = $this->model->getTags();
		$this->template->tags = $this->model->getTags();
	}

	public function renderEditTag(int $tagId): void
	{
		$tag = $this->model->getTags()->get($tagId);
		$this->getComponent('controlTagForm')->setDefaults($tag->toArray());
	}
}
