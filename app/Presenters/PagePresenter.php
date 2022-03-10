<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostModel;
use App\Forms\FormFactory;
use App\Forms\TagsFactory;
use App\Presenters\BasePresenter;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\NetteDatabaseDataSource\NetteDatabaseDataSource;

final class PagePresenter extends BasePresenter
{
	private postModel $model;
	public $page;
	private $related;
	private FormFactory $formFactory;
	private TagsFactory $tagsFactory;
	public Nette\Database\Context $ndb;

	public function __construct(PostModel $model, FormFactory $formFactory, TagsFactory $tagsFactory, Nette\Database\Context $ndb)
	{
		parent::__construct($model, $formFactory, $tagsFactory, $ndb);
		$this->model = $model;
		$this->formFactory = $formFactory;
		$this->tagsFactory = $tagsFactory;
		$this->ndb = $ndb;
	}

	protected function startup(){
		parent::startup();
		if(!$this->user->isLoggedIn()){
			$this->redirect('Sign:in');
		}
	}

	public function createComponentDataGrid(): DataGrid
	{
		$grid = new DataGrid();
		$datasource = new NetteDatabaseDataSource($this->ndb, 'SELECT * from pages');
		$grid->setDataSource($datasource);

		$grid->addColumnText('id', 'id')
		->setAlign('left')
		->setSortable();

		// $grid->add

		$grid->addColumnText('createdAt', 'Vytvořeno')
		->setAlign('right')
		->setSortable();

		// $grid->setTemplateFile(__DIR__ . '/../../datagrid.latte');

		// $grid->addAction('this', '')
		// ->setIcon('redo')
		// ->setClass('btn btn-xs btn-success');

		// $actionCallback = $grid->addActionCallback('custom_callback', '');

		// $actionCallback
		// ->setIcon('sun')
		// ->setTitle('Hello, sun')
		// ->setClass('btn btn-xs bt-default btn secondary ajax');

		// $actionCallback->onClick[] = function ($itemId): void 
		// {
		// 	$this->flashMessage('Custom Callback trig, id: ' . $itemId);
		// 	$this->redrawControl('flashes');
		// };
		
		// $grid->addAction('delete', '', 'delete!')
		// ->setIcon('trash')
		// ->setTitle('Delete')
		// ->setClass('btn btn-xs btn-danger ajax')
		// ->setConfirmation(
		// 	new IConfirmation('Opravdu chcete smazat tuto stránku %s', 'name')
		// );

		
		return $grid;
	}

	public function renderOverview(int $page = 1): void
	{
		$pages = $this->model->getCreatedPages();
		$endPage = 0;

		$this->template->layPages = $pages->page($page, 5, $endPage);
		$this->template->endPage = $endPage;
		$this->template->page = $page;
	}

	protected function createComponentPageForm(): Form
	{
		return $this->formFactory->createPageForm($this->page);
	}

	public function actionCreatePage()
	{
		$this->getComponent('pageForm')->onSuccess[] = function(Form $form)
		{
			$this->flashMessage('Úspěšně jste vytvořil stránku');
			$this->redirect('Page:overview');
		};
	}

	public function actionEditPage(int $pageId): void
	{
		$this->page = $this->model->getPages()->get($pageId);
		$this->related = [];
		$postTags = $this->page->related('pages_tags');
		foreach ($postTags as $postTag) 
		{
			$this->related[] = $postTag;
		}

		$this->getComponent('pageForm')->setDefaults($this->page->toArray())
		->onSuccess[] = function (Form $form)
		{
		$this->flashMessage('Stránka byla aktualizovana.');
		$this->redirect('Homepage:showPage', $this->page->id);
		};
	}

	public function renderEditPage(): void
	{
		$this->template->page = $this->page;
		$this->template->tagsActive = $this->related;
	}

	protected function createComponentAddTagForm(): Form
	{
		return $this->tagsFactory->createAddTagForm($this->page->id);
	}

	public function actionAddTagPage(int $pageId): void
	{
		$this->page = $this->model->getPages()->get($pageId);
		$this->getComponent('addTagForm')->setDefaults($this->page->toArray())
		->onSuccess[] = function (Form $form){
			$this->flashMessage('Úspěšně jste přidal kategorii');
			$this->redirect('Page:editPage', $this->page->id);
		};
	}

	public function renderAddTagPage()
	{
		$this->template->page = $this->page;
	}

	public function handleDeleteTag(int $tagId)
	{
		$tags = $this->page->related('pages_tags');
		$size = $tags->count('*');
		foreach ($tags as $tag) {
			if ($tag->tag_id == $tagId) {
				if ($size > 1) {
					$tag->delete();
					$this->flashMessage('Kategorie byla odstraněna');
					$this->redirect('this');
				} else {
					$this->flashMessage('Nelze odstranit, příspěvek musí obsahovat min. 1. kategorii');
					$this->redirect('this');
				}
			}
		}
	}

	public function handleAdd(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);
		if ($page->inMenu == 0) {
			$page->update([
				'inMenu' => 1
			]);
			$this->flashMessage('Stránka byla přidána do menu');
			$this->redirect('this');
		} else {
			$page->update([
				'inMenu' => 0
			]);
			$this->flashMessage('Stránka byla odebrána z menu');
			$this->redirect('this');
			}
	}

	public function handleDeletePage(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);
		$page->related('pages_tags')->delete();
		$page->delete();
		$this->flashMessage('Stránka byla úspěšně odstraněna.');
		$this->redirect('Page:overview');
	}
}