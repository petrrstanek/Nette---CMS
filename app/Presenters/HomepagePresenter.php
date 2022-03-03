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

	private $page;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	public function renderDefault(int $page = 1): void
	{
		$newPages = $this->model->getCreatedPages();
		$lastPage = 0;
		$this->template->userPages = $newPages->page($page, 3, $lastPage);
		$this->template->lastPage = $lastPage;
		$this->template->paginator = $page;
	}

	public function actionShowPage(int $pageId): void
	{
		$this->page = $this->model->getPages()->get($pageId);
		if (!$this->page) {
			$this->error('Stránka nebyla nalezena.');
		}
	}

	public function renderShowPage(int $pageId): void
	{
		$this->template->page = $this->page;
	}
}
