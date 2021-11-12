<?php declare(strict_types = 1);

namespace App\Modules\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Tracy\Debugger;
use Tracy\ILogger;

final class ErrorPresenter implements IPresenter
{

	public function run(Request $request): Response
	{
		$e = $request->getParameter('exception');

		if ($e instanceof BadRequestException) {
			// Log attempt
			Debugger::log(sprintf('HTTP code %s: %s in %s:%s', $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()), 'access');
			[$module, , $sep] = Helpers::splitName($request->getPresenterName());

			return new ForwardResponse($request->setPresenterName($module . $sep . 'ErrorPage'));
		}

		// Log exception
		Debugger::log($e, ILogger::EXCEPTION);

		return new CallbackResponse(function ($httpRequest, $httpResponse): void {
			if (preg_match('#^application/json(?:;|$)#', $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/templates/Error/500.json';
			} elseif (preg_match('#^text/html(?:;|$)#', $httpResponse->getHeader('Content-Type'))) {
				require __DIR__ . '/templates/Error/500.phtml';
			}
		});
	}

}
