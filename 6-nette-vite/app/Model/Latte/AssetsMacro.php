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

final class AssetsMacro extends MacroSet
{

	private string $assetsDir;

	private string $assetsPath;

	/** @var mixed[]|null */
	private ?array $manifest = null;

	/** @var mixed[]|null */
	private ?array $devserver = null;

	private function __construct(Compiler $compiler, string $assetsDir, string $assetsPath)
	{
		parent::__construct($compiler);

		$this->assetsDir = $assetsDir;
		$this->assetsPath = $assetsPath;
	}

	public function macroAssets(MacroNode $node, PhpWriter $writer): string
	{
		if (!$node->args) throw new InvalidStateException('{assets-css} cannot be empty');

		$isServer = $this->getDevServer() !== null;
		$asset = trim($writer->write('%node.word'), ' \'"');
		$output = '';

		if ($isServer) {
			$scripts = $this->getDevServerAssets();
			foreach ($scripts as $script) {
				$output .= $writer->write('echo "<script type=\"module\" src=\"%raw\"></script>";', $script);
			}
		} else {
			$scripts = $this->getManifestAssets($asset, 'js');
			foreach ($scripts as $script) {
				$output .= $writer->write('echo "<script type=\"module\" src=\"".$basePath."%raw%raw\"></script>";', $this->assetsPath, $script);
			}

			$stylesheets = $this->getManifestAssets($asset, 'css');
			foreach ($stylesheets as $stylesheet) {
				$output .= $writer->write('echo "<link rel=\"stylesheet\" href=\"".$basePath."%raw%raw\">";', $this->assetsPath, $stylesheet);
			}
		}

		return $output;
	}

	public static function setup(string $manifest, string $base): callable
	{
		return function (Engine $engine) use ($manifest, $base): AssetsMacro {
			$compiler = $engine->getCompiler();

			$set = new AssetsMacro($compiler, $manifest, $base);
			$set->addMacro('assets', [$set, 'macroAssets']);

			return $set;
		};
	}

	/**
	 * @return string[]
	 */
	private function getManifestAssets(string $asset, string $type): array
	{
		$manifest = $this->getManifest();

		if (!isset($manifest[$asset])) {
			throw new LogicException(sprintf('Asset "%s" not found in "manifest.json"', $asset));
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
	private function getManifest(): array
	{
		if ($this->manifest === null) {
			$this->manifest = Json::decode(FileSystem::read($this->assetsDir . '/manifest.json'), JSON_OBJECT_AS_ARRAY);
		}

		return $this->manifest;
	}

	/**
	 * @return string[]
	 */
	private function getDevServerAssets(): array
	{
		$assets = $this->getDevServer();

		if (empty($assets)) {
			throw new LogicException('There are no assets in "devserver.json"');
		}

		return $assets;
	}

	/**
	 * @return mixed[]
	 */
	private function getDevServer(): array
	{
		if ($this->devserver === null) {
			$this->devserver = Json::decode(FileSystem::read($this->assetsDir . '/devserver.json'), JSON_OBJECT_AS_ARRAY);
		}

		return $this->devserver;
	}

}
