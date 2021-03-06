<?php

namespace GuzzleHttp\Tests\MessageIntegrity;

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\MessageIntegrity\PhpHash;
use GuzzleHttp\Subscriber\MessageIntegrity\HashingStream;

class HashingStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateRollingMd5()
    {
        $source = Stream::factory('foobar');
        $hash = new PhpHash('md5');
        (new HashingStream($source, $hash))->getContents();
        $this->assertEquals(md5('foobar'), bin2hex($hash->complete()));
    }

    public function testCallbackTriggeredWhenComplete()
    {
        $source = Stream::factory('foobar');
        $hash = new PhpHash('md5');
        $called = false;
        $stream = new HashingStream($source, $hash, function () use (&$called) {
            $called = true;
        });
        $stream->getContents();
        $this->assertTrue($called);
    }

    public function testCanOnlySeekToTheBeginning()
    {
        $source = Stream::factory('foobar');
        $hash = new PhpHash('md5');
        $stream = new HashingStream($source, $hash);

        // Reading works fine
        $bytes = $stream->read(3);
        $this->assertEquals('foo', $bytes);

        // Seeking to 0 is fine
        $stream->seek(0);
        $stream->getContents();
        $this->assertEquals(md5('foobar'), bin2hex($hash->complete()));

        // Seeking arbitrarily is not fine
        $stream->seek(3);
    }
}
