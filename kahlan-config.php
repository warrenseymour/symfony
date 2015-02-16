<?php

use filter\Filter;
use kahlan\reporter\Coverage;
use kahlan\reporter\coverage\driver\Xdebug;

Filter::register('symfony.disable', function($chain) {
	return false;
});

// Disable Kahlan's JIT interceptor; we won't be using it
Filter::apply($this, 'interceptor', 'symfony.disable');

// We don't have any Kahlan specs, so don't try to load them
Filter::apply($this, 'load', 'symfony.disable');

// Use PHPUnit's runner instead of Kahlan's
Filter::register('symfony.run', function($chain) {
	$coverage = $this->reporters()->get('coverage');
	$coverage->before();
	PHPUnit_TextUI_Command::main(false);
	$coverage->after();
	$coverage->stop();
});

Filter::apply($this, 'run', 'symfony.run');

// Configure Kahlan's coverage reporter
Filter::register('symfony.coverage', function($chain) {
	$coverage = new Coverage([
		'verbosity' => 1,
		'driver' => new Xdebug(),
		'path' => [
			'src'
		],
		'exclude' => ['*/Tests/*']
	]);

	$reporters = $this->reporters();
	$reporters->add('coverage', $coverage);
	return $reporters;
});

Filter::apply($this, 'coverage', 'symfony.coverage');
