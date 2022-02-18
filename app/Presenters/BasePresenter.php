<?php

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Form;

abstract class BasePresenter extends Presenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct();
		$this->model = $model;
	}

	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->layNavPosts = $this->model->getOrderedPages()->limit(3);
	}
}
