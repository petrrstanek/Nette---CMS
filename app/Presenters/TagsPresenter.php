<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use App\Model\PostModel;
use Nette\Application\UI\Form;
use App\Presenters\BasePresenter;

class TagsPresenter extends BasePresenter
{
	private postModel $model;

	public function __construct(PostModel $model)
	{
		parent::__construct($model);
		$this->model = $model;
	}

	protected function createComponentControlTagForm(): Form
	{
		$formTag = new Form();

		$formTag->addText('name', 'Název Kategorie:')->setRequired();

		$formTag->addSubmit('send', 'Aktualizovat')->setHtmlAttribute('class', 'button_submit');

		$formTag->onSuccess[] = [$this, 'controlTagFormProcess'];

		return $formTag;
	}

	public function controlTagFormProcess(\stdClass $values): void
	{
		$tagId = $this->getParameter('tagId');
		$tags = $this->model->getTags()->select('name');
		$exist = false;

		//__UPDATE - MODE__//
		if ($tagId) {
			$tag = $this->model->getTags()->get($tagId);
			$tag->update([
				'name' => $values->name,
			]);
			$this->flashMessage('Kategorie byla aktualizována.');
			$this->redirect('Tags:ManagTags');
		} else {
			//__INSERT - MODE__//
			foreach ($tags as $tag) {
				if ($tag->name == $values->name) {
					$this->flashMessage('Kategorie: ' . "$values->name" . ' již existuje');
					$this->redirect('Tags:ManagTags');
					$exist = true;
					break;
				}
			}

			if ($exist === false) {
				$this->model->getTags()->insert([
					'name' => $values->name,
				]);
				$this->flashMessage('Úspěšně jste přidal kategorii: ' . "$values->name" . ' ', 'success');
				$this->redirect('Tags:ManagTags');
			}
		}
	}

	function handleDelete($tagId)
	{
		try {
			$tagId = $this->getParameter('tagId');
			$tagToDelete = $this->model
				->getTags()
				->get($tagId)
				->delete();
			$this->flashMessage('Kategorie byla smazána.');
			$this->redirect('Tags:ManagTags');
		} catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
			$this->flashMessage('Nelze odstranit');
			$this->redirect('this');
		}
	}

	public function renderManagTags(): void
	{
		$tags = $this->model->getTags();
		$this->template->tags = $this->model->getTags();
	}

	public function renderEditTag(int $tagId): void
	{
		$tag = $this->model->getTags()->get($tagId);

		$this->getComponent('controlTagForm')->setDefaults($tag->toArray());
	}
}
