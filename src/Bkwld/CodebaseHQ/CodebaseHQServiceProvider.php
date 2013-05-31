<?php namespace Bkwld\CodebaseHQ;

use Airbrake;
use App;
use Illuminate\Support\ServiceProvider;
use Config;
use Exception;

class CodebaseHQServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(){}
	
	/**
	 * Boot it up
	 */
	public function boot() {
		$this->package('bkwld/codebasehq');
		
		// Don't initialize if it's been disabled for this environment
		if (!Config::get('codebasehq::enable')) return;
		
		// Settings
		$apiKey  = Config::get('codebasehq::api_key');
		$options = array(
			'apiEndPoint' => 'https://exceptions.codebasehq.com/notifier_api/v2/notices',
			'environmentName' => App::environment(),
			'timeout' => 10, // The default wasn't log enough in my tests
		);

		// Instantiate airbrake
		$config = new Airbrake\Configuration($apiKey, $options);
		$client = new Airbrake\Client($config);
		
		// Listen for exception events and pass them to Codebase HQ.
		App::error(function(Exception $exception) use ($client) {
			
			// Exceptions to ignore
			if (is_a($exception, 'Symfony\Component\HttpKernel\Exception\NotFoundHttpException')) return;
			
			// Tell Codebase
			$client->notifyOnException($exception);
		});
		
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() { return array(); }

}