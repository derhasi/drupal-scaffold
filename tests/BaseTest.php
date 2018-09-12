<?php

namespace DrupalComposer\DrupalScaffold\Tests;

use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * Base test Class for drupal-scaffold features.
 */
abstract class BaseTest extends TestCase {

  /**
   * @var \Composer\Util\Filesystem
   */
  protected $fs;

  /**
   * @var string
   */
  protected $tmpDir;

  /**
   * @var string
   */
  protected $rootDir;

  /**
   * @var string
   */
  protected $tmpReleaseTag;

  /**
   * SetUp test
   */
  public function setUp() {
    $this->rootDir = realpath(realpath(__DIR__ . '/..'));

    // Prepare temp directory.
    $this->fs = new Filesystem();
    $this->tmpDir = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR . 'drupal-scaffold';
    $this->ensureDirectoryExistsAndClear($this->tmpDir);

    $this->writeTestReleaseTag();
    $this->writeComposerJSON();

    chdir($this->tmpDir);
  }

  /**
   * tearDown
   *
   * @return void
   */
  public function tearDown() {
    $this->fs->removeDirectory($this->tmpDir);
    $this->git(sprintf('tag -d "%s"', $this->tmpReleaseTag));
  }

  /**
   * Writes the default composer json to the temp direcoty.
   */
  protected function writeComposerJSON() {
    $json = json_encode($this->composerJSONDefaults(), JSON_PRETTY_PRINT);
    // Write composer.json.
    file_put_contents($this->tmpDir . '/composer.json', $json);
  }

  /**
   * Writes a tag for the current commit, so we can reference it directly in the
   * composer.json.
   */
  protected function writeTestReleaseTag() {
    // Tag the current state.
    $this->tmpReleaseTag = '999.0.' . time();
    $this->git(sprintf('tag -a "%s" -m "%s"', $this->tmpReleaseTag, 'Tag for testing this exact commit'));
  }

  /**
   * Provides the default composer.json data.
   *
   * @return array
   */
  protected function composerJSONDefaults() {
    return array(
      'repositories' => array(
        array(
          'type' => 'vcs',
          'url' => $this->rootDir,
        ),
      ),
      'require' => array(
        'drupal-composer/drupal-scaffold' => $this->tmpReleaseTag,
        'composer/installers' => '^1.0.20',
        'drupal/core' => '8.0.0',
      ),
      'minimum-stability' => 'dev',
    );
  }

  /**
   * Wrapper for the composer command.
   *
   * @param string $command
   *   Composer command name, arguments and/or options
   */
  protected function composer($command) {
    chdir($this->tmpDir);
    passthru(escapeshellcmd($this->rootDir . '/vendor/bin/composer ' . $command), $exit_code);
    if ($exit_code !== 0) {
      throw new \Exception('Composer returned a non-zero exit code');
    }
  }

  /**
   * Wrapper for git command in the root directory.
   *
   * @param $command
   *   Git command name, arguments and/or options.
   */
  protected function git($command) {
    chdir($this->rootDir);
    passthru(escapeshellcmd('git ' . $command), $exit_code);
    if ($exit_code !== 0) {
      throw new \Exception('Git returned a non-zero exit code');
    }
  }

  /**
   * Makes sure the given directory exists and has no content.
   *
   * @param string $directory
   */
  protected function ensureDirectoryExistsAndClear($directory) {
    if (is_dir($directory)) {
      $this->fs->removeDirectory($directory);
    }
    mkdir($directory, 0777, TRUE);
  }

}
