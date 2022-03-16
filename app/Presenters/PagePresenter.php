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
  public Nette\Database\Explorer $conn;

  public function __construct(
    PostModel $model,
    FormFactory $formFactory,
    TagsFactory $tagsFactory,
    Nette\Database\Explorer $conn
  ) {
    parent::__construct($model, $formFactory, $tagsFactory, $conn);
    $this->model = $model;
    $this->formFactory = $formFactory;
    $this->tagsFactory = $tagsFactory;
    $this->conn = $conn;
  }

  protected function startup()
  {
    parent::startup();
    if (!$this->user->isLoggedIn()) {
      $this->redirect('Sign:in');
    }
  }

  public function createComponentDataGrid(): DataGrid
  {
    $grid = new DataGrid();
    $datasource = new NetteDatabaseDataSource($this->conn, 'SELECT * from pages');
    $grid->setDataSource($datasource);

    $grid->setItemsPerPageList([5, 10, 100]);

    $grid->addColumnNumber('id', 'ID')->setSortable();
    $grid->addColumnText('title', 'Název');
    $grid->addColumnText('content', 'Perex');
    $grid->addColumnText('createdAt', 'Vytvořeno')->setSortable();
    $grid->addColumnText('updatedAt', 'Aktualizace')->setSortable();

    $grid
      ->addAction('delete', '', 'deletePage!')
      ->setIcon('trash')
      ->setClass('btn btn-xs btn-danger');
    $grid
      ->addAction('add', '', 'add!')
      ->setIcon('star')
      ->setClass('btn btn-xs btn-default btn-warning');
    $grid
      ->addAction('edit', '', 'editPage!')
      ->setIcon('pencil pencil-alt')
      ->setClass('btn btn-xs btn-default btn-secondary');
    $grid
      ->addAction('show', '', 'showPage!')
      ->setIcon('eye')
      ->setClass('btn btn-xs btn-default btn-primary');

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
    $this->getComponent('pageForm')->onSuccess[] = function (Form $form) {
      $this->flashMessage('Úspěšně jste vytvořil stránku');
      $this->redirect('Page:overview');
    };
  }

  public function actionEditPage(int $pageId): void
  {
    $this->page = $this->model->getPages()->get($pageId);
    $this->related = [];
    $tags = [];
    $postTags = $this->page->related('pages_tags');
    foreach ($postTags as $postTag) {
      $this->related[] = $postTag;
      $tags[] = $postTag->tag_id;
    }

    $this->getComponent('pageForm')->setDefaults([
      'title' => $this->page->title,
      'tags' => $tags,
      'content' => $this->page->content,
    ])->onSuccess[] = function (Form $form) {
      $this->flashMessage('Stránka byla aktualizovana.');
      $this->redirect('Homepage:showPage', $this->page->id);
    };
  }

  public function renderEditPage(): void
  {
    $this->template->page = $this->page;
    $this->template->tagsActive = $this->related;
  }

  public function handleShowPage($id)
  {
    $page = $this->model->getPages()->get($id);
    $this->redirect('Homepage:showPage', $page->id);
  }

  public function handleEditPage($id)
  {
    $page = $this->model->getPages()->get($id);
    $this->redirect('Page:editPage', $page->id);
  }

  public function handleAdd($id)
  {
    $page = $this->model->getPages()->get($id);
    if ($page->inMenu == 0) {
      $page->update([
        'inMenu' => 1,
      ]);
      $this->flashMessage('Stránka byla přidána do menu');
      $this->redirect('this');
    } else {
      $page->update([
        'inMenu' => 0,
      ]);
      $this->flashMessage('Stránka byla odebrána z menu');
      $this->redirect('this');
    }
  }

  public function handleDeletePage($id)
  {
    $page = $this->model->getPages()->get($id);
    $page->related('pages_tags')->delete();
    $page->delete();
    $this->flashMessage('Stránka s ID: ' . $id . ' byla úspěšně smazána');
    $this->redirect('this');
  }
}