<?php
/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ytake\LaravelAop;

use Ytake\LaravelAop\Aspect\AspectKernel;
use Illuminate\Contracts\Container\Container as LaravelApplication;

/**
 * Class GoAspect
 *
 * @package Ytake\LaravelAop
 */
class GoAspect implements AspectDriverInterface
{
    /** @var array */
    protected $configure;

    /** @var LaravelApplication */
    protected $laravel;

    /**
     * @param LaravelApplication $laravel
     * @param array              $configure
     */
    public function __construct(LaravelApplication $laravel, array $configure)
    {
        $this->laravel = $laravel;
        $this->configure = $configure;
    }

    public function registerAspect()
    {
        // TODO: add user aspect
    }

    /**
     * initialize aspect kernel
     *
     * @return void
     */
    public function register()
    {
        $kernel = AspectKernel::getInstance();
        $kernel->setLaravel($this->laravel);
        $kernel->init($this->configure);
    }
}