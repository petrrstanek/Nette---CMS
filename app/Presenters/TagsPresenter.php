<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use App\Forms\TagsFactory;
use Nette\Database\Explorer;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\NetteDatabaseDataSource\NetteDatabaseDataSource;

class TagsPresenter extends BasePresenter
{
	private postModel $model;
	private TagsFactory $tagsFactory;
	private $tag;
	private $page;
	public Nette\Database\Explorer $conn;

	public function __construct(PostModel $model, TagsFactory $tagsFactory, Nette\Database\Explorer $conn)
	{
		parent::__construct($model, $tagsFactory, $conn);
		$this->model = $model;
		$this->tagsFactory = $tagsFactory;
		$this->conn = $conn;
	}

	public function createComponentDataGrid()
	{
		$grid = new DataGrid();
		$dataSource = new NetteDatabaseDataSource($this->conn, 'SELECT * from tags');
		$grid->setDataSource($dataSource);

		$grid->addColumnNumber('id', 'ID')->setSortable();
		$grid->addColumnText('name', 'Název')->setSortable();

		$grid->addAction('edit', '', 'editTag!')->setIcon('pencil pencil-alt')->setClass('btn btn-xs btn-default btn-secondary');
		$grid->addAction('delete', '', 'delete!')->setIcon('trash')->setClass('btn btn-xs btn-danger');

		return $grid;
	}

	protected function createComponentCreateTagForm(): Form
	{
		return $this->tagsFactory->createTag($this->tag);
	}

	public function renderDefault(): void
	{
		$this->template->tags = $this->model->getTags();
	}

	public function actionCreateTag(): void
	{
		$this->getComponent('createTagForm')->onSuccess[] = function(Form $form)
		{
			$this->flashMessage('Úspěšně jste přidal kategorii');
			$this->redirect('Tags:');
		};
	}

	public function actionEditTag(int $tagId): void
	{
		$this->tag = $this->model->getTags()->get($tagId);
		$this->getComponent('createTagForm')->setDefaults($this->tag->toArray())
		->onSuccess[] = function(Form $form){
			$this->flashMessage('Kategorie byla aktualizována.');
			$this->redirect('this');
		};
	}

	public function renderEditTag(): void
	{
		$this->template->tag = $this->tag;
	}

	public function handleEditTag($id)
	{
		$tag = $this->model->getTags()->get($id);
		$this->redirect('Tags:editTag', $tag->id);
	}

	
	function handleDelete($id)
	{
		try 
		{
			$this->model->getTags()->get($id)->delete();
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