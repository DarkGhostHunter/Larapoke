<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

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
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->{__FUNCTION__}("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}
