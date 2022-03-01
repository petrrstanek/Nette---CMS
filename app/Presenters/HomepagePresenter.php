<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	public function renderDefault(int $page = 1): void
	{
		$newPages = $this->model->getCreatedPages();
		$lastPage = 0;//zustava
		
		$this->template->userPages = $newPages->page($page, 3, $lastPage);
		$this->template->lastPage = $lastPage;
		$this->template->paginator = $page;
	}
}
