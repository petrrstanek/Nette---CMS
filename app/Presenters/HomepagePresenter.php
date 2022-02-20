<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;

final class HomepagePresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	public function renderDefault(int $page = 1, int $newPage = 1): void
	{
		$pages = $this->model->getCreatedPages();
		$lastPage = 0;//zustava
	$newPages = $this->model->getCreatedPages();
		$endPage = 0;
		
		
		$this->template->layPages = $pages->page($page, 5, $lastPage);
		$this->template->lastPage = $lastPage;//zustava
		$this->template->page = $page;
		
	
		
		$this->template->userPages = $newPages->page($page, 3, $endPage);
		$this->template->endPage = $endPage;
		$this->template->paginator = $page;
	}

	public function handleDelete(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);
		$page->related('pages_tags')->delete();
		$page->delete();
	}

	public function handleAdd(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);
		bdump($page->inMenu);
		if($page->inMenu == 0){
			$page->update([
				'inMenu' => 1
			]);
		} else{
			$page->update([
				'inMenu' => 0
			]);
		}
	}
}
