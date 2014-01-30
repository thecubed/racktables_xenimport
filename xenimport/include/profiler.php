<?php
/**
 * cfProfiler - CodeFire PHP profiler library
 *
 * Allows for easy profiling of applications written in PHP
 *
 * This class requires php-process, for the unix named pipe utilities
 *
 * @author Tyler Montgomery <tylerfixer@thecubed.com>
 * @license GPLv3
 *
 */

class cfProfiler {

	protected $pipe = "";

	protected $timer = 0;
	protected $initTime = 0;
	protected $lastTime = 0;

	/** Construct the profiler.
		@param	pipefile	Path and filename of named pipe to create
		@param	modeid		Numeric permission indicator for the new pipe

	*/
	function __construct($pipefile = "", $modeid = 0) {
		if (!empty($pipefile) && $modeid != 0){
			// init the pipe, and block
			$this->_pipeInit($pipefile, $modeid);
	
			// the rest of the init is blocked until the pipe is connected to (within the php execution timeout period though)
			$this->time = microtime(true);
			$this->initTime = $this->time;
			$this->lastTime = $this->time;
		} else {
			throw new Exception("cfProfiler expects pipe file to not be blank, and mode to be nonzero");
		}
	}

	/** Output text to the profiler 
		@param text	The text to output to the pipe, along with timing information
	*/
	function marker($text = '') {
		// calc the time since the last call
		$curTime = microtime(true);
		$secSinceLastCall = $curTime - $this->lastTime;

		// output to pipe
		$this->_pipeOut("[ ". $curTime ." | ". sprintf('%0.9f',$secSinceLastCall)  ." ] : ". $text . "\n");

		// update last call marker
		$this->lastTime = $curTime;
	}

	/** Initialize the named pipe
		@param pipefile	Name of the new named pipe to create
		@param mode	Numeric permission indicator for the new pipe
	*/
	function _pipeInit($pipefile,$mode) {
		if(!file_exists($pipefile)) {
    			// create the pipe
			umask(0);
			posix_mkfifo($pipefile,$mode);
		}

		// save the pipe to instance var
		$this->pipe = fopen($pipefile, "r+");

		// turn off blocking
		stream_set_blocking($this->pipe, 0);

		//fwrite($this->pipe, "cfDebug Init\n\n");

	} 

	/** Output text to the new pipe
		@param text	Text to output through the named pipe
	*/
	function _pipeOut($text = '') {
		// write the data out
		fwrite($this->pipe, $text);
	}

}

?>
