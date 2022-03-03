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
	private $page;
	private $related;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	protected function startup(){
		parent::startup();
		if(!$this->user->isLoggedIn()){
			$this->redirect('Sign:in');
		}
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
		$pageForm = new Form();
		$pageForm->addText('title', 'Titulek:')->setRequired();
		if (!$this->page) {
			$pageForm->addMultiSelect('tags', 'Kategorie: ', $this->model->fetchTags());
		}

		$pageForm->addTextArea('content', 'Obsah:')
			->setHtmlAttribute('id', 'editor')
			->setRequired();

		$pageForm->addSubmit('send', 'Aktualizovat')
			->setHtmlAttribute('class', 'button__submit');

		return $pageForm;
	}

	public function actionCreatePage()
	{
		$this->getComponent('pageForm')->onSuccess[] = [$this, 'addPageFormProcess'];
	}

	public function actionEditPage(int $pageId): void
	{
		$this->page = $this->model->getPages()->get($pageId);
		if (!$this->page) {
			$this->error('Stránka nebyla nalezena.');
		}

		$this->related = [];
		$postTags = $this->page->related('pages_tags');
		foreach ($postTags as $postTag) {
			$this->related[] = $postTag;
		}
		$this->getComponent('pageForm')->setDefaults($this->page->toArray())
		->onSuccess[] = [$this, 'editPageFormProcess'];
	}

	public function addPageFormProcess(\stdClass $values): void
	{
		$page = $this->model->getPages()->insert([
			'title' => $values->title,
			'content' => $values->content,
			'updatedAt' => new DateTime(),
			'createdAt' => new DateTime(),
		]);

		foreach($values->tags as $tag){
			$this->model->getRelatedTags()->insert([
				'page_id' => $page->id,
				'tag_id' => $tag,
			]);
		}
		$this->flashMessage('Aktualizace proběhla úspěšně.', 'success');
		$this->redirect('Homepage:showPage', $page->id);
	}

	public function renderEditPage(): void
	{
		$this->template->page = $this->page;
		$this->template->tagsActive = $this->related;
	}

	public function editPageFormProcess(\stdClass $values)
	{
		$this->page->update([
			'updatedAt' => new DateTime(),
			'title' => $values->title,
			'content' => $values->content,
		]);
		$this->flashMessage('Příspěvek byl aktualizován');
		$this->redirect('Homepage:showPage', $this->page->id);
	}

	public function actionAddTagPage(int $pageId): void
	{
		$this->page = $this->model->getPages()->get($pageId);
		$this->getComponent('addTagForm')->setDefaults($this->page->toArray())
		->onSuccess[] = [$this, 'addTagFormSucceeded'];
	}

	public function renderAddTagPage()
	{
		$this->template->page = $this->page;
	}

	protected function createComponentAddTagForm(): Form
	{
		$form = new Form();
		$form
			->addMultiSelect('tags', 'Přidat Kategori:', $this->model->fetchTags())
			->setHtmlAttribute('id', 'mar')
			->setRequired();

		$form
			->addSubmit('send', 'Přidat Kategorii')
			->setHtmlAttribute('class', 'button__submit');
		return $form;
	}

	public function addTagFormSucceeded(\stdClass $values): void
	{
		try {
			foreach($values->tags as $tag){
				$this->model->getRelatedTags()->insert([
					'page_id' => $this->page->id,
					'tag_id' => $tag,
				]);
			}
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			$this->flashMessage('Nelze přidat, protože příspěvěk již obsahuje tuto kategorii');
			$this->redirect('this');
		}
		$this->flashMessage('Kategorie byla úspěšně přidána.', 'success');
		$this->redirect('Page:editPage', $this->page->id);
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
			} else {
				$page->update([
					'inMenu' => 0
				]);
			}
			$this->flashMessage('Stránka byla přidána do menu');
			$this->redirect('this');
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
