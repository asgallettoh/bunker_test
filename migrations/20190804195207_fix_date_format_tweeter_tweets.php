<?php

use Phinx\Migration\AbstractMigration;

class FixDateFormatTweeterTweets extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $count = $this->execute("UPDATE twitter_tweets SET twitter_created_at = DATE_FORMAT(STR_TO_DATE(twitter_created_at, '%a %b %e %H:%i:%s +0000 %Y'),'%Y-%m-%d %T') where STR_TO_DATE(twitter_created_at, '%a %b %e %H:%i:%s +0000 %Y') IS NOT NULL");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}