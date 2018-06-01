<?php
/**
 * Created by PhpStorm.
 * User: sean
 * Date: 22/06/17
 * Time: 8:13 AM
 */

namespace CSIROCMS\Application;


use PHPUnit\Framework\Assert;

class CommandHelpers
{
	public static function liveExecuteCommand($cmd)
	{
		while (@ ob_end_flush()) ; // end all output buffers if any
		$proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');
		$live_output = "";
		$complete_output = "";
		while (!feof($proc)) {
			$live_output = fread($proc, 4096);
			$live_output = str_replace('tput: No value for $TERM and no -T specified', "", $live_output);
			$live_output = preg_replace('/[^a-zA-Z]*.*CollectiveAccess [0-9]+\.[0-9]+\.[0-9]+([\w]|[^\w])*/', '', $live_output);
			$complete_output = $complete_output . $live_output;
			echo "$live_output";
			@ flush();
		}

		pclose($proc);

		// get exit status
		preg_match('/[0-9]+$/', $complete_output, $matches);

		// return exit status and intended output
		return [
			'exit_status' => intval($matches[0]),
			'output' => str_replace("Exit status : " . $matches[0], '', $complete_output),
		];
	}

	public static function assertEquals($expected, $actual, $message = null)
	{
		Assert::assertEquals($expected, $actual, $message);
	}

	public static function assert($condition, $message = null)
	{
		Assert::assertTrue($condition, $message);
	}

}
