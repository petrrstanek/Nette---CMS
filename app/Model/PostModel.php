<?php

namespace App\Model;

use Nette;

final class PostModel
{
	use Nette\SmartObject;

	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function fetchTags(): array
	{
		return $this->database->table('tags')->fetchPairs('id', 'name');
	}

	public function fetchActiveTags(): array
	{
		return $this->database->table('pages_tags')->fetchPairs('id', 'name');
	}

	public function getRelatedTags()
	{
		return $this->database->table('pages_tags');
	}

	public function getPages()
	{
		return $this->database->table('pages');
	}

	public function getTags()
	{
		return $this->database->table('tags');
	}

	public function getCreatedPages(): Nette\Database\Table\Selection
	{
		return $this->database->table('pages')->order('updatedAt DESC');
	}
}
