<?php declare(strict_types = 1);

namespace App\Model\Latte;

use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use LogicException;
use Nette\InvalidStateException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

final class ManifestMacro extends MacroSet
{

	private string $manifest;

	/** @var mixed[]|null */
	private ?array $content = null;

	private function __construct(Compiler $compiler, string $manifest)
	{
		parent::__construct($compiler);

		$this->manifest = $manifest;
	}

	public function macroAssetsJs(MacroNode $node, PhpWriter $writer): string
	{
		if (!$node->args) throw new InvalidStateException('{assets-js} cannot be empty');

		$asset = trim($writer->write('%node.word'), ' \'"');
		$scripts = $this->getAsset($asset, 'js');

		$output = '';
		foreach ($scripts as $script) {
			$output .= $writer->write('echo "<script type=\"module\" src=\"$basePath%raw\"></script>";', $script);
		}

		return $output;
	}

	public function macroAssetsCss(MacroNode $node, PhpWriter $writer): string
	{
		if (!$node->args) throw new InvalidStateException('{assets-css} cannot be empty');

		$asset = trim($writer->write('%node.word'), ' \'"');
		$stylesheets = $this->getAsset($asset, 'css');

		$output = '';
		foreach ($stylesheets as $stylesheet) {
			$output .= $writer->write('echo "<link rel=\"stylesheet\" href=\"$basePath%raw\">";', $stylesheet);
		}

		return $output;
	}

	public static function setup(string $manifest): callable
	{
		return function (Engine $engine) use ($manifest): AssetsMacro {
			$compiler = $engine->getCompiler();

			$set = new AssetsMacro($compiler, $manifest);
			$set->addMacro('assets-css', [$set, 'macroAssetsCss']);
			$set->addMacro('assets-js', [$set, 'macroAssetsJs']);

			return $set;
		};
	}

	/**
	 * @return string[]
	 */
	private function getAsset(string $asset, string $type): array
	{
		$manifest = $this->load();

		if (!isset($manifest[$asset])) {
			throw new LogicException(sprintf('Asset "%s" not found', $asset));
		}

		if ($type === 'js') {
			return [$manifest[$asset]['file']];
		}

		if ($type === 'css') {
			return $manifest[$asset]['css'];
		}

		throw new LogicException(sprintf('Invalid type "%s" ', $type));
	}

	/**
	 * @return mixed[]
	 */
	private function load(): array
	{
		if ($this->content === null) {
			$this->content = Json::decode(FileSystem::read($this->manifest), JSON_OBJECT_AS_ARRAY);
		}

		return $this->content;
	}

}
