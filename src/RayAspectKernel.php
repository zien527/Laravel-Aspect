<?php

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 * Copyright (c) 2015 Yuuki Takezawa
 *
 *
 * CodeGenMethod Class, CodeGen Class is:
 * Copyright (c) 2012-2015, The Ray Project for PHP
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace Ytake\LaravelAspect;

use Ray\Aop\Bind;
use PhpParser\Lexer;
use Ray\Aop\Compiler;
use Illuminate\Contracts\Container\Container;
use Ytake\LaravelAspect\Exception\ClassNotFoundException;

/**
 * Class RayAspectKernel
 */
class RayAspectKernel implements AspectDriverInterface
{
    /** @var Container */
    protected $app;

    /** @var array */
    protected $configure;

    /** @var Compiler */
    protected $compiler;

    /** @var bool */
    protected $cacheable = false;

    /** @var \Ytake\LaravelAspect\Modules\AspectModule */
    protected $aspectResolver;

    /**
     * @param Container $app
     * @param array $configure
     */
    public function __construct(Container $app, array $configure)
    {
        $this->app = $app;
        $this->configure = $configure;
        $this->makeCompileDir();
        $this->makeCacheableDir();
        $this->compiler = $this->getCompiler();
    }

    /**
     * @param null $module
     *
     * @throws ClassNotFoundException
     */
    public function register($module = null)
    {
        if (!class_exists($module)) {
            throw new ClassNotFoundException($module);
        }
        $this->aspectResolver = (new $module($this->app));
        $this->aspectResolver->attach();
    }

    /**
     * boot aspect kernel
     */
    public function boot()
    {
        foreach ($this->aspectResolver->getResolver() as $class => $pointcuts) {

            $bind = (new AspectBind($this->cacheable, $this->configure['cache_dir']))
                ->bind($class, $pointcuts);
            $compiledClass = $this->compiler->compile($class, $bind);
            $this->app->bind($class, function ($app) use ($bind, $compiledClass) {
                $instance = $app->make($compiledClass);
                $instance->bindings = $bind->getBindings();
                return $instance;
            });
        }
    }

    /**
     * @return Compiler
     */
    protected function getCompiler()
    {
        return new Compiler($this->configure['compile_dir']);
    }

    /**
     * make source compile file directory
     *
     * @return void
     */
    protected function makeCompileDir()
    {
        /** @var \Illuminate\Filesystem\Filesystem $file */
        $file = $this->app['files'];
        if (!$file->exists($this->configure['compile_dir'])) {
            $file->makeDirectory($this->configure['compile_dir'], 0777);
        }
    }

    /**
     * make aspect cache directory
     *
     * @return void
     */
    protected function makeCacheableDir()
    {
        if ($this->configure['cache']) {
            /** @var \Illuminate\Filesystem\Filesystem $file */
            $file = $this->app['files'];
            if (!$file->exists($this->configure['cache_dir'])) {
                $file->makeDirectory($this->configure['cache_dir'], 0777);
            }
            $this->cacheable = true;
        }
    }
}
