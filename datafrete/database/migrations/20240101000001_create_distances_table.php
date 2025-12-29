<?php

use Phinx\Migration\AbstractMigration;

class CreateDistancesTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('distances', [
            'id' => 'id',
            'primary_key' => ['id']
        ]);

        $table->addColumn('cep_origem', 'string', ['limit' => 8, 'null' => false])
              ->addColumn('cep_destino', 'string', ['limit' => 8, 'null' => false])
              ->addColumn('distancia', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
              ->addColumn('created_at', 'datetime', ['null' => false])
              ->addColumn('updated_at', 'datetime', ['null' => false])
              ->addIndex(['cep_origem', 'cep_destino'], ['unique' => true, 'name' => 'idx_cep_pair'])
              ->create();
    }
}

