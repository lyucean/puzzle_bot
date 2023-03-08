<?php

use Phinx\Migration\AbstractMigration;

class CreateImagesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('images');
        $table->addColumn('x', 'text')
          ->addColumn('y', 'text')
          ->addColumn('path', 'text')
          ->create();
    }
}