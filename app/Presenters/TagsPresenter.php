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
	private $tag;

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

	public function renderDefault(): void
	{
		$this->template->tags = $this->model->getTags();
	}

	public function actionCreateTag(): void
	{
		$this->tag = $this->model->getTags();
		$this->getComponent('controlTagForm')->onSuccess[] = [$this, 'createTagProcess'];
	}

	public function actionEditTag(int $tagId): void
	{
		$this->tag = $this->model->getTags()->get($tagId);
		$this->getComponent('controlTagForm')->setDefaults($this->tag->toArray())
		->onSuccess[] = [$this, 'editTagProcess'];
	}

	public function editTagProcess(array $values): void
	{
			$this->tag->update($values);
			$this->flashMessage('Kategorie byla aktualizována.');
			$this->redirect('Tags:');
	}

	public function createTagProcess(\stdClass $values): void
	{
		$exist = false;
		foreach ($this->tag as $tag) {
			if ($tag->name == $values->name) {
				$this->flashMessage('Kategorie: ' . "$values->name" . ' již existuje');
				$this->redirect('this');
				$exist = true;
				break;
			}
		}
		if ($exist === false) {
			$this->tag->insert([
				'name' => $values->name,
			]);
			$this->flashMessage('Úspěšně jste přidal kategorii: ' . "$values->name" . ' ', 'success');
			$this->redirect('Tags:');
		}
	}
	
	function handleDelete($tagId)
	{
		try 
		{
			$this->model->getTags()->get($tagId)->delete();
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
