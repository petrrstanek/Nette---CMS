<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;
use Nette\Utils\DateTime;

final class PagePresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	protected function createComponentPageForm(): Form
	{
		if(!in_array($this->getAction(), ['createPage', 'editPage'])){
			$this->error();
		}

		$pageForm = new Form();
		$pageId = $this->getParameter('pageId');
		$pageForm->addText('title', 'Titulek:')->setRequired();

		if (!$pageId) {
			$fetchedTags = $this->model->fetchTags();
			$pageForm->addSelect('tags', 'Kategorie: ', $fetchedTags);
		} 

		$pageForm
				->addTextArea('content', 'Obsah:')
				->setHtmlAttribute('id', 'editor')
				->setRequired();

		$pageForm
				->addSubmit('send', 'Aktualizovat')
				->setHtmlAttribute('class', 'button__submit');

		return $pageForm;
	}

	public function actionCreatePage()
	{
			$pageForm = $this->getComponent('pageForm');
			$pageForm->onSuccess[] = [$this, 'addPageFormProcess'];
	}

	public function actionEditPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);
		$pageForm = $this->getComponent('pageForm');
		$pageForm->setDefaults($page->toArray());
		$pageForm->onSuccess[] = [$this,'editPageFormProcess'];
	}

	public function editPageFormProcess(\stdClass $values)
	{
		$pageId = $this->getParameter('pageId');
		$page = $this->model->getPages()->get($pageId);
		$page->update([
			'updatedAt' => new DateTime(),
			'title' => $values->title,
			'content' => $values->content,
		]);
		$this->flashMessage('Příspěvek byl aktualizován');
		$this->redirect('this');
	}

	public function addPageFormProcess(\stdClass $values): void
	{
			$pageId = $this->getParameter('pageId');
			$page = $this->model->getPages()->insert([
				'title' => $values->title,
				'content' => $values->content,
				'createdAt' => new DateTime(),
			]);
			$this->model->getRelatedTags()->insert([
				'tag_id' => $values->tags,
			]);
			$this->flashMessage('Aktualizace proběhla úspěšně.', 'success');
			$this->redirect('Page:showPage', $page->id);
	}

	public function actionShowPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);
		$fkPageIds = $this->model->getRelatedTags()->select('page_id');

		foreach ($fkPageIds as $fkPageId) {
			if ($fkPageId->page_id == null) {
				$this->model
					->getRelatedTags()
					->where('page_id', null)
					->update([
						'page_id' => $page->id,
					]);
			}
		}
	}

	public function renderShowPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);
		$this->template->page = $page;
		if (!$page) {
			$this->error('Stránka nebyla nalezena.');
		}
		
		$tags = $this->model->getTags();
		$pageId = $this->getParameter('pageId');
		
		$allTags = [];
		foreach ($tags as $tag) {
			$postTags = $tag->related('pages_tags')->where('page_id', $pageId);
			foreach ($postTags as $postTag) {
				$allTags[] = $postTag;
			}

			$this->template->tagsActive = $allTags;
		}
	}

	public function renderEditPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);
		$pageId = $this->getParameter('pageId');
		$tags = $this->model->getTags();

		$allTags = [];
		foreach ($tags as $tag) {
			$postTags = $tag->related('pages_tags')->where('page_id', $pageId);
			foreach ($postTags as $postTag) {
				$allTags[] = $postTag;
			}
			
			$this->template->tagsActive = $allTags;
		}

		$this->template->page = $page;
	}

	protected function createComponentAddTagForm(): Form
	{
		$form = new Form();
		$pageId = $this->getParameter('pageId');

		/* $activeId = $this->model->getPages()->get($pageId);

		$tags = $activeId->related('pages_tags')->fetchPairs('id', 'id');

		$tags = $this->model->getTags(); */

		/* $allTags = [];
		foreach ($tags as $tag) {
			$postTags = $tag->related('pages_tags');

			foreach ($postTags as $postTag) {
				$allTags[] = $postTag->tag->name;
			}
		} */

		$form
			->addSelect('tags', 'Přidat Kategori:', $this->model->fetchTags())
			->setHtmlAttribute('id', 'mar')
			->setRequired();

		/* $form->addRadioList('rTags', 'Odebrat Kategorii:', $allTags)->setHtmlAttribute('class', 'selectButtons'); */

		$form
			->addSubmit('send', 'Přidat Kategorii')
			->setHtmlAttribute('class', 'button__submit');
		return $form;
	}

	public function actionAddTagPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);
		$form = $this->getComponent('addTagForm')->setDefaults($page->toArray());
		$form->onSuccess[] = [$this, 'addTagFormSucceeded'];
	}

	public function addTagFormSucceeded(\stdClass $values): void
	{
		try {
			$pageId = $this->getParameter('pageId');
			$page = $this->model->getPages()->get($pageId);
			$this->model->getRelatedTags()->insert([
				'page_id' => $pageId,
				'tag_id' => $values->tags,
			]);
		} 
		catch (Nette\Database\UniqueConstraintViolationException $e) 
		{
			$this->flashMessage('Nelze přidat, protože příspěvěk již obsahuje tuto kategorii');
			$this->redirect('this');
		}

		$this->flashMessage('Kategorie byla úspěšně přidána.', 'success');
		$this->redirect('Page:editPage', $page->id);
	}

	public function renderAddTagPage(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);
		$this->template->page = $page;
	}

	public function handleDelete(int $tagId)
	{
	$pageId = $this->getParameter('pageId');
	$tags = $this->model->getRelatedTags()->where('page_id', $pageId);
	$size = $tags->count('*');
	foreach($tags as $tag){
		if($tag->tag_id == $tagId){
			if($size > 1){
				$tag->delete();
				$this->flashMessage('Kategorie byla odstraněna');
				$this->redirect('this');
			}else{
				$this->flashMessage('Nelze odstranit, příspěvek musí obsahovat min. 1. kategorii');
				$this->redirect('this');
			}
		} 
	}
	}
}
