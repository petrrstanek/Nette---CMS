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

	protected function createComponentAddPageForm(): Form
	{
		$pageForm = new Form();

		$pageId = $this->getParameter('pageId');

		$pageForm->addText('title', 'Titulek:')->setRequired();

		//__UPDATE__-__MODE__ //
		if ($pageId) {
			/* $pageForm
				->addSelect('tags', 'Kategorie: ', $this->model->fetchTags())
				->setHtmlAttribute('class', 'selectButtons'); */

			$pageForm
				->addTextArea('content', 'Obsah:')
				->setHtmlAttribute('id', 'editor')
				->setRequired();

			$pageForm
				->addSubmit('send', 'Aktualizovat')
				->setHtmlAttribute('class', 'button__submit')
				->setValidationScope([$pageForm['content'], $pageForm['title']])->onClick[] = [$this, 'pageFormSucceeded'];
		} else {
			//__INSERT__MODE__//
			$fetchedTags = $this->model->fetchTags();
			$pageForm->addSelect('tags', 'Kategorie: ', $fetchedTags);

			$pageForm
				->addTextArea('content', 'Obsah:')
				->setHtmlAttribute('id', 'editor')
				->setRequired();

			$pageForm->addSubmit('send', 'Uložit');
			$pageForm->onSuccess[] = [$this, 'pageFormSucceeded'];
		}
		return $pageForm;
	}

	public function pageFormSucceeded(\stdClass $values): void
	{
		$pageId = $this->getParameter('pageId');
		bdump($pageId);
		//__UPDATE__MODE__
		if ($pageId) {
			$page = $this->model->getPages()->get($pageId);
			$page->update([
				'updatedAt' => new DateTime(),
				'title' => $values->title,
				'content' => $values->content,
			]);
			$this->flashMessage('Příspěvek byl aktualizován');
			$this->redirect('this');
		} else {
			//__INSERT__MODE__

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
	}

	public function renderShowPage(int $pageId): void
	{
		$page = $this->model->getPages()->get($pageId);

		if (!$page) {
			$this->error('Stránka nebyla nalezena.');
		}
		$this->template->page = $page;

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
		$this->template->page = $page;
		$this->getComponent('addPageForm')->setDefaults($page->toArray());

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

	protected function createComponentAddTagForm(): Form
	{
		$form = new Form();

		$pageId = $this->getParameter('pageId');

		$activeId = $this->model->getPages()->get($pageId);

		$tags = $activeId->related('pages_tags')->fetchPairs('id', 'id');

		$tags = $this->model->getTags();

		$allTags = [];
		foreach ($tags as $tag) {
			$postTags = $tag->related('pages_tags');

			foreach ($postTags as $postTag) {
				$allTags[] = $postTag->tag->name;
			}
		}

		$form
			->addSelect('tags', 'Přidat Kategori:', $this->model->fetchTags())
			->setHtmlAttribute('id', 'mar')
			->setRequired();

		$form->addRadioList('rTags', 'Odebrat Kategorii:', $allTags)->setHtmlAttribute('class', 'selectButtons');

		$form
			->addSubmit('delete', 'Smazat')
			->setHtmlAttribute('class', 'button__delete')
			->setValidationScope([$form['rTags']])->onClick[] = [$this, 'relatedRemoveGo'];

		$form
			->addSubmit('send', 'Přidat Kategorii')
			->setHtmlAttribute('class', 'button__submit')
			->setValidationScope([$form['tags']])->onClick[] = [$this, 'addTagFormSucceeded'];

		return $form;
	}

	public function relatedRemoveGo(\stdClass $values): void
	{
		$pageId = $this->getParameter('pageId');
		$page = $this->model->getPages()->get($pageId);
		$relatedTags = $this->model
			->getRelatedTags()
			->select('page_id')
			->where('tag_id', $values->rTags);
		bdump($values->rTags);
		foreach ($relatedTags as $relatedTag) {
			/* if ($relatedTag->id == $values->rTags) {
				$this->model
					->getActiveTag()
					->where('name', $relatedTag->name)
					->delete();
				$this->model
					->getPosts()
					->where('tags', $values->rTags)
					->delete();
				$this->flashMessage('Kategorie byla odebrána');
				$this->redirect('Page:editPage', $page->id);
			} */
		}
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
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			$this->flashMessage('Nelze přidat, protože příspěvěk již obsahuje tuto kategorii');
			$this->redirect('this');
		}

		/* $activeTag = $this->model
			->getActiveTag()
			->select('name')
			->where('post_id', $pageId);
		$sizeActiveTag = $activeTag->count('*');
		$exist = false;

		for ($i = 0; $i < $sizeActiveTag; $i++) {
			bdump($activeTag[$i]);
			if ($activeTag[$i]->name == $data->tags) {
				$this->flashMessage('Tento příspěvek již má kategorii ' . $data->tags . '.');
				$this->redirect('Post:show', $page->id);
				$exist = true;
			}
		}
		if ($exist === false) {
			$this->model->getActiveTag()->insert([
				'post_id' => $pageId,
				'name' => $data->tags,
			]);
		} */
		$this->flashMessage('Kategorie byla úspěšně přidána.', 'success');
		$this->redirect('Page:editPage', $page->id);
	}

	public function renderAddTagPage(int $pageId)
	{
		$page = $this->model->getPages()->get($pageId);

		$this->getComponent('addTagForm')->setDefaults($page->toArray());

		$this->template->page = $page;
	}
}
