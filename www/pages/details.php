<?php

use app\models\Settings;
use newzflash\AniDB;
use newzflash\Books;
use newzflash\Console;
use newzflash\Games;
use newzflash\Movie;
use newzflash\Music;
use newzflash\PreDb;
use newzflash\ReleaseComments;
use newzflash\ReleaseExtra;
use newzflash\ReleaseFiles;
use newzflash\Releases;
use newzflash\Videos;
use newzflash\XXX;
use newzflash\DnzbFailures;

if (!$page->users->isLoggedIn()) {
	$page->show403();
}

if (isset($_GET['id'])) {
	$releases = new Releases(['Settings' => $page->settings]);
	$data     = $releases->getByGuid($_GET['id']);

	if (!$data) {
		$page->show404();
	}

	$rc = new ReleaseComments($page->settings);
	$fail = new DnzbFailures(['Settings' => $page->settings]);
	if ($page->isPostBack()) {
		$rc->addComment($data['id'], $_POST['txtAddComment'], $page->users->currentUserId(), $_SERVER['REMOTE_ADDR']);
	}

	$mov = $xxx = $showInfo = '';
	if ($data['videos_id'] > 0) {
		$showInfo = (new Videos(['Settings' => $page->settings]))->getByVideoID($data['videos_id']);
	}

	if ($data['imdbid'] != '' && $data['imdbid'] != 0000000) {
		$movie = new Movie(['Settings' => $page->settings]);
		$mov   = $movie->getMovieInfo($data['imdbid']);
		if ($mov && isset($mov['title'])) {
			$mov['title']    = str_replace(['/', '\\'], '', $mov['title']);
			$mov['actors']   = $movie->makeFieldLinks($mov, 'actors');
			$mov['genre']    = $movie->makeFieldLinks($mov, 'genre');
			$mov['director'] = $movie->makeFieldLinks($mov, 'director');
			if (Settings::value('site.trailers.trailers_display')) {
				$trailer = (!isset($mov['trailer']) || empty($mov['trailer']) || $mov['trailer'] == '' ? $movie->getTrailer($data['imdbid']) : $mov['trailer']);
				if ($trailer) {
					$mov['trailer'] = sprintf(
						"<iframe width=\"%d\" height=\"%d\" src=\"%s\"></iframe>",
						Settings::value('site.trailers.trailers_size_x'),
						Settings::value('site.trailers.trailers_size_y'),
						$trailer
					);
				}
			}
		}
	}

	if ($data['xxxinfo_id'] != '' && $data['xxxinfo_id'] != 0) {
		$XXX = new XXX(['Settings' => $page->settings]);
		$xxx = $XXX->getXXXInfo($data['xxxinfo_id']);
		if ($xxx && isset($xxx['title'])) {
			$xxx['title']    = str_replace(['/', '\\'], '', $xxx['title']);
			$xxx['actors']   = $XXX->makeFieldLinks($xxx, 'actors');
			$xxx['genre']    = $XXX->makeFieldLinks($xxx, 'genre');
			$xxx['director'] = $XXX->makeFieldLinks($xxx, 'director');
			if (isset($xxx['trailers'])) {
				$xxx['trailers'] = $XXX->insertSwf($xxx['classused'], $xxx['trailers']);
			}
		} else {
			$xxx = false;
		}
	}

	$user = $page->users->getById($page->users->currentUserId());
	$re = new ReleaseExtra($page->settings);

	$page->smarty->assign([
		'anidb'   => ($data['anidbid'] > 0 ? (new AniDB(['Settings' => $page->settings]))->getAnimeInfo($data['anidbid']) : ''),
		'boo'   => ($data['bookinfo_id'] != '' ? (new Books(['Settings' => $page->settings]))->getBookInfo($data['bookinfo_id']) : ''),
		'con'   => ($data['consoleinfo_id'] != '' ? (new Console(['Settings' => $page->settings]))->getConsoleInfo($data['consoleinfo_id']) : ''),
		'game'  => ($data['gamesinfo_id'] != '' ? (new Games(['Settings' => $page->settings]))->getgamesInfo($data['gamesinfo_id']) : ''),
		'movie'   => $mov,
		'music' => ($data['musicinfo_id'] != '' ? (new Music(['Settings' => $page->settings]))->getMusicInfo($data['musicinfo_id']) : ''),
		'pre'   => (new PreDb(['Settings' => $page->settings]))->getForRelease($data['predb_id']),
		'show'  => $showInfo,
		'xxx'   => $xxx,
		'comments' => $rc->getComments($data['id']),
		'cpapi'    => $user['cp_api'],
		'cpurl'    => $user['cp_url'],
		'nfo'      => $releases->getReleaseNfo($data['id'], false),
		'release'  => $data,
		'reAudio'  => $re->getAudio($data['id']),
		'reSubs'   => $re->getSubs($data['id']),
		'reVideo'  => $re->getVideo($data['id']),
		'similars' => $releases->searchSimilar($data['id'], $data['searchname'], 6, $page->userdata['categoryexclusions']),
		'privateprofiles' => (Settings::value('..privateprofiles') == 1 ? true : false),
		'releasefiles'    => (new ReleaseFiles($page->settings))->get($data['id']),
		'searchname'      => $releases->getSimilarName($data['searchname']),
		'failed'          => $fail->getFailedCount($data['id']),
	]);

	$page->meta_title       = 'View NZB';
	$page->meta_keywords    = 'view,nzb,description,details';
	$page->meta_description = 'View NZB for' . $data['searchname'];

	$page->content = $page->smarty->fetch('viewnzb.tpl');
	$page->render();
}
