<?php

namespace Bundles\GIT;
use Bundles\Manage\Tile;
use Exception;
use e;

/**
 * Evolution GIT Manage
 * @author Kelly Becker
 */
class Manage {
	
	public $title = 'GIT';
	
	public function update() {
		echo '<h1 style="border-bottom:solid 1px #000;">Pulling The Following Repos</h1>';
		return $this->index(true);
	}

	public function index($pull = false) {
		return e::$git->check_status($pull);
	}
	
	public function page($path) {
		$all = array();
		
		echo '<style>' . file_get_contents(__DIR__ . '/manage/git-style.css') . '</style>';
		echo '<div class="controls">
				<span class="state-init"><em>GIT</em> | <a href="/@manage/git/update">Pull</a></span>
			</div>';
		
		echo '<div class="wrapper">';

		if(array_shift($path) == 'update')
			echo $this->update();
		else echo $this->index();

		echo '</div>';
	}
	
	public function tile() {
	    $tile = new Tile('git');
	    if(!function_exists('shell_exec'))
	    	$tile->alert = 'Shell Exec not available!';
    	$tile->body .= '<h2>Fetch and upgrade your sites repos.</h2>';
    	return $tile;
    }
}