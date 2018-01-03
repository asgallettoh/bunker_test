<?php

use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{ 
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        echo "This actually does nothing, but I want it to be there in order for candidates to know where the configuration and migrations folder is" . PHP_EOL;
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
