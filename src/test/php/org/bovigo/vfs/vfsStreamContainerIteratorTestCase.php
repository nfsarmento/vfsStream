<?php
/**
 * This file is part of vfsStream.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  org\bovigo\vfs
 */
namespace org\bovigo\vfs;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
/**
 * Test for org\bovigo\vfs\vfsStreamContainerIterator.
 */
class vfsStreamContainerIteratorTestCase extends TestCase
{
    /**
     * instance to test
     *
     * @type  vfsStreamDirectory
     */
    private $dir;
    /**
     * child one
     *
     * @type  vfsStreamContent
     */
    private $child1;
    /**
     * child two
     *
     * @type  vfsStreamContent
     */
    private $child2;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->dir = new vfsStreamDirectory('foo');
        $this->child1 = NewInstance::of(vfsStreamContent::class)->returns([
           'getName' => 'bar'
        ]);

        $this->dir->addChild($this->child1);
        $this->child2 = NewInstance::of(vfsStreamContent::class)->returns([
           'getName' => 'baz'
        ]);
        $this->dir->addChild($this->child2);
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        vfsStream::enableDotfiles();
    }

    public function provideSwitchWithExpectations(): array
    {
        return [
            [[vfsStream::class, 'disableDotfiles'], []],
            [[vfsStream::class, 'enableDotfiles'], ['.', '..']]
        ];
    }

    private function getDirName($dir): string
    {
        if (is_string($dir)) {
            return $dir;
        }

        return $dir->getName();
    }

    /**
     * @param  callable  $switchDotFiles
     * @param  array     $dirNames
     * @test
     * @dataProvider  provideSwitchWithExpectations
     */
    public function iteration(callable $switchDotFiles, array $dirs)
    {
        $dirs[] = $this->child1;
        $dirs[] = $this->child2;
        $switchDotFiles();
        $dirIterator = $this->dir->getIterator();
        foreach ($dirs as $dir) {
            $this->assertEquals($this->getDirName($dir), $dirIterator->key());
            $this->assertTrue($dirIterator->valid());
            if (!is_string($dir)) {
                $this->assertSame($dir, $dirIterator->current());
            }

            $dirIterator->next();
        }

        $this->assertFalse($dirIterator->valid());
        $this->assertNull($dirIterator->key());
        $this->assertNull($dirIterator->current());
    }
}
