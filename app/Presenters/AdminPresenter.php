<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;

final class AdminPresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	public function renderDefault(int $page = 1): void
	{
		$pages = $this->model->getCreatedPages();
		$endPage = 0;
		
		$this->template->layPages = $pages->page($page, 5, $endPage);
		$this->template->endPage = $endPage;//zustava
		$this->template->page = $page;
	}

	public function handleDelete(int $pageId)
	{
		parent::startup();
		if($this->getUser()->isLoggedIn())
		{
			$this->redirect('Sign:in');
		} else{
			$page = $this->model->getPages()->get($pageId);
			$page->related('pages_tags')->delete();
			$page->delete();
		}
	}

	public function handleAdd(int $pageId)
	{
		parent::startup();
		if($this->getUser->isLoggedIn())
		{
			$this->redirect('Sign:in');
		}else{
			$page = $this->model->getPages()->get($pageId);
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
}
