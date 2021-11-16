<?php declare(strict_types = 1);

namespace App\Model\Latte;

use Latte\Compiler;
use Latte\Engine;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\InvalidStateException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;

final class AssetsMacro extends MacroSet
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
		$script = $this->getAsset($asset);

		if ($script) {
			return $writer->write('echo "<script type=\"module\" src=\"$basePath%raw\"></script>";', $script);
		}

		return $writer->write('echo "<!-- No asset %raw -->";', $asset);
	}

	public function macroAssetsCss(MacroNode $node, PhpWriter $writer): string
	{
		if (!$node->args) throw new InvalidStateException('{assets-css} cannot be empty');

		$asset = trim($writer->write('%node.word'), ' \'"');
		$stylesheet = $this->getAsset($asset);

		if ($stylesheet) {
			return $writer->write('echo "<link rel=\"stylesheet\" href=\"$basePath%raw\">";', $stylesheet);
		}

		return $writer->write('echo "<!-- No asset %raw -->";', $asset);
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

	private function getAsset(string $asset): ?string
	{
		$manifest = $this->load();

		return $manifest[$asset] ?? null;
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
