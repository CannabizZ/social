<?php

require_once __DIR__.'/../vendor/autoload.php';

$exampleStorage = new class() extends \App\Storage\AbstractStorage {

    public const TABLE = 'example';


    public function createTable()
    {
        $this->query(
            'CREATE TABLE IF NOT EXISTS social.' . self::TABLE . '
                    (
                        firstValue       int unsigned not null,
                        secondValue int unsigned not null,
                        primary key (firstValue, secondValue)
                    );'
        );
        $this->query('TRUNCATE TABLE social.' . self::TABLE . ';');
    }

    public function addValue(int $firstValue, int $secondValue)
    {
        $this->query(
            'INSERT INTO social.' . self::TABLE . '
                    (firstValue,secondValue) 
                    VALUES(' . $firstValue . ',' . $secondValue . ');'
        );
    }
};

$exampleStorage->createTable();


$i = 0;
while (true) {
    ++$i;
    $exampleStorage->addValue($i, 1);
    echo $i . PHP_EOL;
}