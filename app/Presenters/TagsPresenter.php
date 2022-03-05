<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use App\Model\TagsFactory;

class TagsPresenter extends BasePresenter
{
	private postModel $model;
	private TagsFactory $tagsFactory;
	private $tag;
	private $page;

	public function __construct(PostModel $model, TagsFactory $tagsFactory)
	{
		parent::__construct($model, $tagsFactory);
		$this->model = $model;
		$this->tagsFactory = $tagsFactory;
	}

	protected function createComponentControlTagForm(): Form
	{
		return $this->tagsFactory->createTag($this->tag);
	}

	public function renderDefault(): void
	{
		$this->template->tags = $this->model->getTags();
	}

	public function actionCreateTag(): void
	{
		$this->tag = $this->model->getTags();
		$this->getComponent('controlTagForm')->onSuccess[] = function(Form $form)
		{
			$this->flashMessage('Úspěšně jste přidal kategorii');
			$this->redirect('Tags:');
		};
	}

	public function actionEditTag(int $tagId): void
	{
		$this->tag = $this->model->getTags()->get($tagId);
		$this->getComponent('controlTagForm')->setDefaults($this->tag->toArray())
		->onSuccess[] = function(Form $form){
			$this->flashMessage('Kategorie byla aktualizována.');
			$this->redirect('this');
		};
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
