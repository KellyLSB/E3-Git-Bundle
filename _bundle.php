<?php

namespace Bundles\GIT;
use Exception;
use stack;
use e;

class Bundle {

	private $repos = array();
	private $developer = false;

	public function _on_framework_loaded() {
		/**
		 * Get a list of Bundle Dirs
		 */
		$dirs = stack::$dirs;

		/**
		 * Unshift the site directory onto the array
		 */
		array_unshift($dirs, e\site);
		array_unshift($dirs, EvolutionSDK);

		/**
		 * Loop through and check if is a git repository
		 */
		foreach($dirs as $dir) {
			if(!is_dir($dir.'/.git'))
				continue;

			$this->repos[] = $dir;
		}

		/**
		 * Get Development Mode
		 */
		$this->developer = e::$environment->requireVar('Development.Master', 'yes | no');

		//$this->check_status();

		//e\Complete();
	}

	public function check_status($pull = false) {
		$final = '';
		foreach($this->repos as $repo) {
			$return = '<h1>'.ucwords(basename($repo)).'</h1><br />';
			
			$output = shell_exec("cd $repo && git status");
			$output = explode("\n", $output);

			if(array_search('# On branch dev', $output) !== FALSE
				&& $this->developer !== true)
				$return .= '<span style="color:red;">Warning: on development branch!</span><br />';
			if(array_search('nothing to commit (working directory clean)', $output) === FALSE
				&& $this->developer !== true)
				$return .= '<span style="color:red;">Warning: you should not be editing live source code!</span><br />';

			$output = shell_exec("cd $repo && git branch");
			$output = explode("\n", $output);

			$branch = e\array_find('* ', $output);
			$branch = substr($output[$branch], 2);

			$output = shell_exec("cd $repo && git log --name-only ..origin/$branch");
			$commits = array_filter(explode('commit ', $output));

			if(!empty($commits)) $return .= "<h2>".($pull ? 'Pulled' : 'Remote (UnPulled)')." Commits on \"$branch\" brach</h2>";
			foreach($commits as &$commit) {
				$return .= '<div class="commit" style="margin:5px;padding:10px;border:solid 1px #000;">';
				$commit = array_filter(explode("\n", $commit));
				$return .= '<span style="font-weight:bold;">Hash: </span>'.array_shift($commit).'<br />';
				$return .= '<span style="font-weight:bold;">Author: </span>'.substr(array_shift($commit), 8).'<br />';
				$return .= '<span style="font-weight:bold;">Date: </span>'.date('Y-m-d H:i:s', strtotime(substr(array_shift($commit), 6))).'<br /><br />';
				$return .= '<span style="font-weight:bold;">Message: </span><p>'.array_shift($commit).'</p>';

				$return .= '<span style="font-weight:bold;">Modified Files:</span> <br />';
				foreach($commit as $file)
					$return .= $file.'<br />';

				$return .= '</div>';
			}

			if(!empty($commits) && $pull) {
				$output = shell_exec("cd $repo && git pull -u origin $branch");
				$output = array_filter(explode("\n", $output));

				$return .= '<p style="color:green;"><span style="font-weight:bold;">Pulled: </span>'.array_pop($output).'</p>';
			}

			$result = trim($return);
			if($result === '<h1>'.ucwords(basename($repo)).'</h1><br />')
				$return .= "<span style=\"color:green;\">Everything up to date on branch \"$branch\"</span>";

			$final .= $return;

			//$return .= nl2br($output);
			
		}

		echo $final;
	}

}