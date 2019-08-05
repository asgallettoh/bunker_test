<?php

require_once('../vendor/autoload.php');
include("../controller/indexController.php");

header('Content-type: text/plain; charset=utf-8');
$app = new \Slim\Slim(array('mode' => 'production', 'debug' => true));
$app->configureMode('development', function () use ($app) {
	$app->config(array(
			'log.enable' => false,
			'displayErrorDetails' => true
	));
});
$loader = new Twig_Loader_Filesystem('../templates');
$twig = new Twig_Environment($loader, array());
$indexController = new indexController();

define('REPORT_START', '2014-08-22'); //for some reason let's consider the report is taken from this date onwards

//ROUTES
//GET route /
$app->get('/', function () use ($twig) {
	echo $twig->render('index.twig');
});

//GET route /chart
$app->get('/chart', function () use ($twig, $indexController) {
	//Conneccion
	$db = $indexController::connectToDB();
	//Query trackeo
	$tracking_query = "SELECT tracking_type, tracking_term FROM twitter_tracking";

	$stmt_twitter_tracking = indexController::getQueryResult($db, $tracking_query);
	$data = array('hashtags' => array(), 'mentions' => array(), 'user' => array());
	//creo las queries para completar los parametros abajo
	$hashtags_query = "
		SELECT twitter_created_at d, count(1) q 
		FROM twitter_tweets 
		JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id 
			and type = 'hashtag' 
			and tag IN (";

	$mentions_query = "
		SELECT twitter_created_at d, count(1) q 
			FROM twitter_tweets 
			JOIN twitter_tweet_entities on twitter_tweets.tweet_id = twitter_tweet_entities.tweet_id 
				and type = 'mentions' 
				and tag IN (";

	$users_query = "
	SELECT twitter_created_at d, count(1) q 
		FROM twitter_tweets 
		JOIN twitter_actors on twitter_actors.twitter_user_id = twitter_tweets.twitter_user_id 
		and username IN (";

	foreach ($stmt_twitter_tracking as $track) {
		//HASHTAGS
		if ($track['tracking_type'] == 'hashtag'){
			$hashtags_query = $hashtags_query."'".$track['tracking_term']."',";
		}//MENTIONS
		elseif ($track['tracking_type'] == 'mention'){
			$mentions_query = $mentions_query."'".$track['tracking_term']."',";
		}//USERS
		elseif ($track['tracking_type'] == 'user'){
			$users_query = $users_query."'".$track['tracking_term']."',";
		}
	}
	
	//HASHTAGS
	$hashtags_query = rtrim($hashtags_query,',');
	$hashtags_query = $hashtags_query.") WHERE twitter_created_at > ? group by twitter_created_at";
	$stmt_hashtags = indexController::getQueryResult($db, $hashtags_query, array(REPORT_START));

	while ($row = $stmt_hashtags->fetch(PDO::FETCH_ASSOC)) {
		$row['date'] = date('Y-m-d', strtotime($row['d']));
		if (!isset($data['hashtags'][$row['date']])) $data['hashtags'][$row['date']] = 0;
		$data['hashtags'][$row['date']] += $row['q'];	
	}
	//MENTIONS
	$mentions_query = rtrim($mentions_query,',');
	$mentions_query = $mentions_query.") WHERE twitter_created_at > ? group by twitter_created_at";
	$stmt_mention = indexController::getQueryResult($db, $mentions_query, array(REPORT_START));
	
	while ($row = $stmt_mention->fetch(PDO::FETCH_ASSOC)) {
		$row['d'] = date('Y-m-d', strtotime($row['d']));
		if (!isset($data['mentions'][$row['d']])) $data['mentions'][$row['d']] = 0;
		$data['mentions'][$row['d']] += $row['q'];
	}
	//USERS
	$users_query = rtrim($users_query,',');
	$users_query = $users_query.") WHERE twitter_created_at > ? group by twitter_created_at";
	$stmt_user = indexController::getQueryResult($db, $users_query, array(REPORT_START));
	
	while ($row = $stmt_user->fetch(PDO::FETCH_ASSOC)) {
		$row['d'] = date('Y-m-d', strtotime($row['d']));
		if (!isset($data['user'][$row['d']])) $data['user'][$row['d']] = 0;
		$data['user'][$row['d']] += $row['q'];
	}
	
	$data = indexController::fillDates($data, REPORT_START);
	echo $twig->render('chart.twig', array('data' => $data));
});

$app->run();

?>