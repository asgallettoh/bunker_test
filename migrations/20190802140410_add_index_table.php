<?php

use Phinx\Migration\AbstractMigration;

class AddIndexTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $twitterTweetEntities = $this->table("twitter_tweet_entities");
        $twitterTweetEntities->addIndex(['tag'], ['name' => 'idx_tag'])
            ->addIndex(['type'], ['name' => 'idx_type'])
            ->save();

        $twitterActors = $this->table("twitter_actors");
        $twitterActors->addIndex(['username'], ['name' => 'idx_username'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}