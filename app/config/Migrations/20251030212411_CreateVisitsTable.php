<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateVisitsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        // Create the visits table

        $table = $this->table("visits");

        $table->addColumn('date', 'date', [
            'null' => false
        ]);

        $table->addColumn('completed', 'integer', [
            'default' => 0,
            'null' => false,
            'limit' => 1,
        ]);

        $table->addColumn('forms', 'integer', [
            'null' => false,
            'limit' => 11,
        ]);

        $table->addColumn('products', 'integer', [
            'null' => false,
            'limit' => 11,
        ]);

        $table->addColumn('duration', 'integer', [
            'default' => 0,
            'null' => false,
            'limit' => 11,
        ]);

        $table->addIndex(['date'], ['name' => 'date']);

        $table->create();
    }
}
