<?php

namespace hypeJunction\Groups;

use Elgg\Cli\Command;
use Symfony\Component\Console\Input\InputOption;

class TranslateCommand extends Command {

	/**
	 * {@inheritdoc}
	 */
	protected function configure() {
		$this->setName('groups:translate')
			->setDescription('Generate language keys for custom group subtypes')
			->addOption('path', 'p', InputOption::VALUE_REQUIRED,
				'Specify output path'
			);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function command() {
		_elgg_services()->translator->setCurrentLanguage('en');
		$all_translations = _elgg_services()->translator->getLoadedTranslations();

		$conf = GroupsService::instance()->all();

		foreach ($all_translations as $language => $translations) {
			$original_str = $translations["groups:group"];
			$original_str_plural = $translations["groups"];

			foreach ($conf as $subtype => $options) {
				$identifier = $options->identifier;

				if ($identifier === 'groups') {
					continue;
				}

				$subtype_str = $options->labels['en']['item'];
				$subtype_str_plural = $options->labels['en']['collection'];

				foreach ($translations as $key => $translation) {
					$identifier_key = preg_replace("/^(groups)/", $identifier, $key);

					if ($identifier_key == $key) {
						continue;
					}

					if (!empty($translations[$identifier_key])) {
						continue;
					}

					$translation = str_replace(strtolower($original_str), strtolower($subtype_str), $translation);
					$translation = str_replace(ucfirst($original_str), ucfirst($subtype_str), $translation);

					$translation = str_replace(strtolower($original_str_plural), strtolower($subtype_str_plural), $translation);
					$translation = str_replace(ucfirst($original_str_plural), ucfirst($subtype_str_plural), $translation);

					$to_translate[$identifier_key] = $translation;
				}
			}

			$path = $this->option('path') ? : elgg_get_cache_path();
			$dir = rtrim($path, '/') . '/subgroups/languages/';

			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}

			$file = fopen("{$dir}{$language}.php", 'w');
			$contents = var_export($to_translate, true);
			fwrite($file, "<?php\r\n\r\nreturn $contents;\r\n");
			fclose($file);

			$this->write("Translations generated in {$dir}{$language}.php");
		}
	}

}