<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use Nette\Http\IResponse;

final class AdminPresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	public function renderDashboard(int $page = 1): void
	{
		$pages = $this->model->getCreatedPages();
		$endPage = 0;

		$this->template->layPages = $pages->page($page, 5, $endPage);
		$this->template->endPage = $endPage;
		$this->template->page = $page;
	}

	public function handleDelete(int $pageId)
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		} else {
			$page = $this->model->getPages()->get($pageId);
			$page->related('pages_tags')->delete();
			$page->delete();
		}
	}

	public function handleAdd(int $pageId)
	{
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} 
			$page = $this->model->getPages()->get($pageId);
			if ($page->inMenu == 0) {
				$page->update([
					'inMenu' => 1
				]);
			} else {
				$page->update([
					'inMenu' => 0
				]);
			}
			$this->redirect('this');
	}
}
