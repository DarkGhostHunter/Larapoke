<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait ScaffoldAuth
{
    /**
     * Scaffold the default authentication logic for testing.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function scaffoldAuth($app)
    {
	$this->cleanScaffold();

        mkdir($app->basePath('routes'), 0777, true);
        mkdir($app->resourcePath('sass'), 0777, true);
        mkdir($app->resourcePath('js'), 0777, true);
        mkdir($app->path('Http/Controllers'), 0777, true);

        $app[Kernel::class]->call('ui bootstrap --auth');
    }

    /**
     * Clear the auth scaffold files
     *
     * @return void
     */
    protected function cleanScaffold()
    {
        $this->cleanDir(base_path('routes'));
        $this->cleanDir(app_path('Http'));
        $this->cleanDir(resource_path('js'));
        $this->cleanDir(resource_path('sass'));
        $this->cleanDir(resource_path('views'));
    }

    /**
     * Recursive directory cleaning.
     *
     * @param $dir
     * @return bool
     */
    protected function cleanDir($dir)
    {
	if (! is_dir($dir)) {
            return true;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileinfo) {
            $fileinfo->isDir()
                ? rmdir($fileinfo->getRealPath())
                : unlink($fileinfo->getRealPath());
        }

        return rmdir($dir);
    }
}
