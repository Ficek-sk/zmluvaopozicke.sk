<?php

use Nette\Database\Connection;
use Nette\DI\Container;

class ContractReviewManager
{
    private $siteId;
    public const TABLE_CONTRACT = 'contract';
    public const TABLE_CONTRACT_WEBSITE = 'contract_website';
    public const TABLE_CONTRACT_DOCUMENT = 'contract_document';

    /**
     * @var Connection
     */
    private $connection;
    /**
     * ContractReviewManager constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $parameters = $container->getParameters()['contractReview'];
        $this->connection = new Connection('mysql:host=' . $parameters['host'] . ';dbname=' . $parameters['dbname'], $parameters['user'], $parameters['password']);
        $this->siteId = $parameters['websiteId'];
    }

    public function getPhoto()
    {
        $photoSql = 'select website_photo from user where id = 2';
        $photoName = $this->connection->query($photoSql)->fetchField();
        return 'https://admin.ficek.sk/www/websiteUserPhoto/' . $photoName;
    }
}