<?php

namespace App\DataObject;

class SeachIndex
{
    private $page;
    private $length;
    private $orderBy;
    private $orderDir;
    private $search;

    function __construct(int $page, int $length, string $orderBy, string $orderDir)
    {
        $this->page = $page;
        $this->length = $length;
        $this->orderBy = $orderBy;
        $this->orderDir = $orderDir;
    }

    public function getOrderDir(): string
    {
        return $this->orderDir;
    }

    public function setOrderDir(string $orderDir): SeachIndex
    {
        $this->orderDir = $orderDir;

        return $this;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function setOrderBy(string $orderBy): SeachIndex
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): SeachIndex
    {
        $this->length = $length;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): SeachIndex
    {
        $this->page = $page;

        return $this;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function setSearch(string $search): SeachIndex
    {
        $this->search = $search;

        return $this;
    }
}
