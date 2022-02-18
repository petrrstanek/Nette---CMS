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

	public function renderDefault(int $page = 1): void
	{
		$posts = $this->model->getOrderedPages();
		$lastPage = 0;
		$this->template->layPages = $posts->page($page, 3, $lastPage);
		$this->template->page = $page;
		$this->template->lastPage = $lastPage;
		$test = $this->model->getPages()->select('id');
		$size = $test->count('*');
		$this->template->relatedTags = $test;
	}
}
