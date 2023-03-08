<?php

use Phinx\Migration\AbstractMigration;

class CreateMessagesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('messages');
        $table->addColumn('chat_id', 'string', ['limit' => 30])
          ->addColumn('message_text', 'text')
          ->addColumn('message_date', 'datetime')
          ->create();
    }
}