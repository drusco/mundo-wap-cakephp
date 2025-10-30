<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateWorkdaysTable extends AbstractMigration
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

        // create the workdays table
        $table = $this->table("workdays");

        $table->addColumn('date', 'date', [
            'null' => false,
        ]);

        $table->addColumn('visits', 'integer', [
            'null' => false,
            'limit'=> 11,
            'default' => 0
        ]);

        $table->addColumn('completed', 'integer', [
            'null' => false,
            'limit'=> 1,
            'default' => 0
        ]);

        $table->addColumn('duration', 'integer', [
            'null' => false,
            'limit'=> 11,
            'default' => 0
        ]);

        $table->addIndex(['date'], ['name' => 'date']);

        $table->create();
    }
}
