<?php

require_once '../vendor/autoload.php';

$app = new \Slim\Slim(array('mode' => 'production', 'debug' => false ));
$loader = new Twig_Loader_Filesystem('../templates');
$twig = new Twig_Environment($loader, array());
$db = new PDO('mysql:host=localhost;port=3306;dbname=bunker', 'root', 'bunker', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET SESSION query_cache_type = OFF;"));
define('REPORT_START', '2014-08-22');

$app->get('/', function () use ($twig) {
	echo $twig->render('index.twig');
});

$app->get('/chart', function () use ($twig, $db) {
    $stmt = $db->prepare("SELECT tracking_type, tracking_term FROM twitter_tracking");
    $stmt->execute();
    $data = array('hashtag' => array(), 'mentions' => array());
    foreach ($stmt->fetchAll() as $track) {
        if ($track['tracking_type'] == 'hashtag')
        {
            $stmt = $db->prepare("SELECT twitter_created_at d, count(1) q FROM twitter_tweets JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id and type = 'hashtag' and tag = ? WHERE twitter_created_at > ? group by twitter_created_at");
            $stmt->execute(array($track['tracking_term'], REPORT_START));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['d'] = date('Y-m-d', strtotime($row['d']));
                if (!isset($data['hashtags'][$row['d']])) $data['hashtags'][$row['d']] = 0;
                $data['hashtags'][$row['d']] += $row['q'];
            }
        }
        elseif ($track['tracking_type'] == 'mention')
        {
            $stmt = $db->prepare("SELECT twitter_created_at d, count(1) q FROM twitter_tweets JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id and tag = ? and type = 'mentions' WHERE twitter_created_at > ? group by twitter_created_at");
            $stmt->execute(array($track['tracking_term'], REPORT_START));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['d'] = date('Y-m-d', strtotime($row['d']));
                if (!isset($data['mentions'][$row['d']])) $data['mentions'][$row['d']] = 0;
                $data['mentions'][$row['d']] += $row['q'];
            }
        } elseif ($track['tracking_type'] == 'user') {
            $stmt = $db->prepare("SELECT twitter_created_at d, count(1) q FROM twitter_tweets JOIN twitter_actors on twitter_actors.twitter_user_id = twitter_tweets.twitter_user_id and username = ? WHERE twitter_created_at > ? group by twitter_created_at");
            $stmt->execute(array($track['tracking_term'], REPORT_START));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['d'] = date('Y-m-d', strtotime($row['d']));
                if (!isset($data['user'][$row['d']])) $data['user'][$row['d']] = 0;
                $data['user'][$row['d']] += $row['q'];
            }
        }
    }
    $data = fillDates($data, REPORT_START);

    echo $twig->render('chart.twig', array('data' => $data));
});

$app->run();


function fillDates($dates, $min)
{
    ksort($dates['mentions']);
    ksort($dates['hashtags']);
    ksort($dates['user']);

    $max = array_keys($dates['mentions'])[count($dates['mentions']) - 1];
    if ($max < array_keys($dates['hashtags'])[count($dates['hashtags']) - 1]) {
        $max = array_keys($dates['hashtags'])[count($dates['hashtags']) - 1];
    }
    if ($max < array_keys($dates['user'])[count($dates['hashtags']) - 1]) {
        $max = array_keys($dates['user'])[count($dates['hashtags']) - 1];
    }

    while ($min <= $max) {

        if (isset($dates['mentions'][$min])) {
            $return['mentions'][$min] = $dates['mentions'][$min];
        } else {
            $return['mentions'][$min] = 0;
        }

        if (isset($dates['hashtags'][$min])) {
            $return['hashtags'][$min] = $dates['hashtags'][$min];
        } else {
            $return['hashtags'][$min] = 0;
        }

        if (isset($dates['user'][$min])) {
            $return['user'][$min] = $dates['user'][$min];
        } else {
            $return['user'][$min] = 0;
        }
        $return['dates'][$min] = $min;
        $min = date('Y-m-d', strtotime($min . ' + 1 day'));
    }

    return $return;
}

