<?php declare(strict_types = 1);

namespace App\Model\Latte;

use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\InvalidStateException;

final class AssetsMacro extends MacroSet
{

	private string $wwwDir;

	private function __construct(Compiler $compiler, string $wwwDir)
	{
		parent::__construct($compiler);
		$this->wwwDir = $wwwDir;
	}

	public function macroAssets(MacroNode $node, PhpWriter $writer): string
	{
		if (!$node->args) throw new InvalidStateException('{assets} cannot be empty');

		$asset = trim($writer->write('%node.word'), ' \'"');
		$file = $this->wwwDir . '/' . ltrim($asset, '/');

		return $writer->write('echo "%raw";', hash_file('md5', $file));
	}

	public static function setup(string $manifest): callable
	{
		return function (Engine $engine) use ($manifest): AssetsMacro {
			$compiler = $engine->getCompiler();

			$set = new AssetsMacro($compiler, $manifest);
			$set->addMacro('assets', [$set, 'macroAssets']);

			return $set;
		};
	}

}
