<?php

namespace App\Entity;

use App\Repository\SearchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchRepository::class)]
class Search
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type:'string')]
    private string $query;

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $value): void
    {
        $this->query = $value;
    }
}