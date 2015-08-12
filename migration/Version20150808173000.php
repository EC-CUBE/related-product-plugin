<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150808173000 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $this->createDtbCategoyContentPlugin($schema);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('plg_related_product');
    }

    protected function createDtbCategoryContentPlugin(Schema $schema)
    {
        $table = $schema->createTable("plg_related_product");
        $table
            ->addColumn('product_id', 'integer')
            ->addColumn('child_product_id', 'integer')
            ->addColumn('explain', 'integer', array(
                'notnull' => true,
            ))
        ;
    }
}