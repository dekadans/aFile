<?php
namespace lib\Services;


class SortService
{
    const COLUMN_NAME = 'name';
    const COLUMN_SIZE = 'size';
    const COLUMN_DATE_UPLOAD = 'created';
    const COLUMN_DATE_EDIT = 'last_edit';

    const DIRECTION_ASCENDING = 'asc';
    const DIRECTION_DESCENDING = 'desc';

    const SESSION_COLUMN = 'aFile_Sort_Column';
    const SESSION_DIRECTION = 'aFile_Sort_Direction';

    private $sortBy;
    private $direction;

    private function __construct()
    {
        $this->sortBy = self::COLUMN_NAME;
        $this->direction = self::DIRECTION_ASCENDING;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     */
    public function setSortBy($sortBy)
    {
        if ($sortBy === $this->sortBy) {
            $this->flipDirection();
        }
        else if ($sortBy === self::COLUMN_NAME) {
            $this->setDirection(self::DIRECTION_ASCENDING);
        }
        else {
            $this->setDirection(self::DIRECTION_DESCENDING);
        }

        $_SESSION[self::SESSION_COLUMN] = $sortBy;
        $this->sortBy = $sortBy;
    }

    public function flipDirection()
    {
        if ($this->direction === self::DIRECTION_ASCENDING) {
            $this->setDirection(self::DIRECTION_DESCENDING);
        }
        else {
            $this->setDirection(self::DIRECTION_ASCENDING);
        }
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param mixed $direction
     */
    public function setDirection($direction)
    {
        $_SESSION[self::SESSION_DIRECTION] = $direction;
        $this->direction = $direction;
    }

    public function getSortString()
    {
        return $this->sortBy . ' ' . $this->direction;
    }

    /**
     * @return SortService
     */
    public static function loadFromSession()
    {
        if (isset($_SESSION[self::SESSION_COLUMN]) && isset($_SESSION[self::SESSION_DIRECTION])) {
            $column = $_SESSION[self::SESSION_COLUMN];
            $direction = $_SESSION[self::SESSION_DIRECTION];
        }
        else {
            $column = self::COLUMN_NAME;
            $direction = self::DIRECTION_ASCENDING;

            $_SESSION[self::SESSION_COLUMN] = $column;
            $_SESSION[self::SESSION_DIRECTION] = $direction;
        }

        $sort = new self();
        $sort->setSortBy($column);
        $sort->setDirection($direction);

        return $sort;
    }
}