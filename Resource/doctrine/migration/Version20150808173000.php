<?php
/*
 * This file is part of the Related Product plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Doctrine\ORM\EntityManager;
use Plugin\RelatedProduct\Utils\Version;

/**
 * Class Version20150808173000.
 */
class Version20150808173000 extends AbstractMigration
{
    /**
     * @var string table name
     */
    const NAME = 'plg_related_product';

    /**
     * @var array table eitity
     */
    protected $entities = array(
        'Plugin\RelatedProduct\Entity\RelatedProduct',
    );

    /**
     * Setup table.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (Version::isSupportGetInstanceFunction()) {
            $this->createRelatedProductTable($schema);
        } else {
            $this->createRelatedProductTableForOldVersion($schema);
        }
    }

    /**
     * remove table.
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //current version >= 3.0.9
        if (Version::isSupportGetInstanceFunction()) {
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);
            $tool = new SchemaTool($app['orm.em']);
            $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);
            // テーブル削除
            foreach ($schemaFromMetadata->getTables() as $table) {
                if ($schema->hasTable($table->getName())) {
                    $schema->dropTable($table->getName());
                }
            }
            // シーケンス削除
            foreach ($schemaFromMetadata->getSequences() as $sequence) {
                if ($schema->hasSequence($sequence->getName())) {
                    $schema->dropSequence($sequence->getName());
                }
            }
            //for delete sequence in postgresql
            if ($this->connection->getDatabasePlatform()->getName() == 'postgresql') {
                $schema->dropSequence('plg_related_product_id_seq');
            }
        } else {
            // this down() migration is auto-generated, please modify it to your needs
            $schema->dropTable(self::NAME);
            $schema->dropSequence('plg_related_product_id_seq');
        }
    }

    /**
     * create related product table for version < 3.0.9 .
     *
     * @param Schema $schema
     */
    protected function createRelatedProductTableForOldVersion(Schema $schema)
    {
        $table = $schema->createTable('plg_related_product');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('product_id', 'integer');
        $table->addColumn('child_product_id', 'integer');
        $table->addColumn('content', 'text', array(
                'notnull' => false,
        ));
        $table->setPrimaryKey(array('id'));
    }

    /**
     * create related product table for version > 3.0.9 .
     *
     * @param Schema $schema
     *
     * @return true
     */
    protected function createRelatedProductTable(Schema $schema)
    {
        if ($schema->hasTable(self::NAME)) {
            return true;
        }

        $app = Application::getInstance();
        $em = $app['orm.em'];
        $classes = array(
            $em->getClassMetadata('Plugin\RelatedProduct\Entity\RelatedProduct'),
        );
        $tool = new SchemaTool($em);
        $tool->createSchema($classes);

        return true;
    }

    /**
     * Get metadata.
     *
     * @param EntityManager $em
     *
     * @return array
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }
}
