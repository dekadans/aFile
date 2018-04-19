<?php

namespace lib;

/**
 * MySQL
 * CAST(FROM_BASE64(location) as CHAR)
 */
class SearchEngine
{
    /** @var \PDO */
    private $pdo;
    /** @var Sort */
    private $sort;

    private $originalInput;
    private $searchString;

    private $advancedParameters = [];
    private $sqlWhereClauses = [];
    private $sqlWhereParameters = [];

    public function __construct(\PDO $pdo, Sort $sort)
    {
        $this->pdo = $pdo;
        $this->sort = $sort;
    }

    public function search(string $searchString, int $userId)
    {
        $this->originalInput = $this->searchString = trim($searchString);

        if (!empty($this->searchString)) {
            $this->extractAdvancedParameters();
            $this->setFreeTextSearch();

            $this->setTypeSearch();

            $this->addWhere(" user_id = :user ", [':user' => $userId]);
            $this->addWhere(" type = 'FILE' ", []);

            $result = $this->prepareAndRun();
        }
        else {
            $result = [];
        }

        return $result;
    }

    private function extractAdvancedParameters()
    {
        foreach (['type'] as $parameter) {
            if (preg_match('/'. $parameter .':([A-Za-z]*)/', $this->searchString, $matches)) {
                $this->advancedParameters[$parameter] = $matches[1];

                $this->searchString = trim(preg_replace('/'. $parameter .':[A-Za-z]*/', '', $this->searchString));
            }
        }
    }

    // Bygg ut detta så att varje ord måste finnas men inte i följd? LIKE XXX AND LIKE YYY
    private function setFreeTextSearch()
    {
        if (!empty($this->searchString)) {
            $this->addWhere(" name LIKE :freeText ", [':freeText' => '%'. $this->searchString .'%']);
        }
    }

    private function setTypeSearch()
    {
        if (isset($this->advancedParameters['type'])) {
            $type = $this->advancedParameters['type'];

            if (isset($this->searchTypes[$type])) {
                $mimes = $this->searchTypes[$type];
                $whereStatement = [];
                $whereParameters = [];

                for ($i = 0; $i < count($mimes); $i++) {
                    $whereStatement[] = " mime = :typeMime{$i} ";
                    $whereParameters[":typeMime{$i}"] = $mimes[$i];
                }

                $whereStatement = " (". implode(" OR ", $whereStatement) .") ";

                $this->addWhere($whereStatement, $whereParameters);
            }
        }
    }

    private function addWhere(string $preparedString, array $parameters)
    {
        $this->sqlWhereClauses[] = $preparedString;
        $this->sqlWhereParameters += $parameters;
    }

    // Add sorting
    private function getQuery()
    {
        $compositeWhere = implode(" AND ", $this->sqlWhereClauses);

        $sql = "SELECT * FROM files WHERE
                {$compositeWhere}
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'SPECIAL' THEN 2
                    ELSE 4
                END), " . $this->sort->getSortBy() . ' ' . $this->sort->getDirection();

        return $sql;
    }

    private function prepareAndRun()
    {
        $query = $this->getQuery();

        $statement = $this->pdo->prepare($query);

        foreach ($this->sqlWhereParameters as $placeholder => $value) {
            $statement->bindValue($placeholder, $value);
        }

        $statement->execute();
        return $statement->fetchAll();
    }

    private $searchTypes = [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/tiff'
        ],
        'document' => [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/vnd.oasis.opendocument.text',
            'application/rtf'
        ]
    ];
}