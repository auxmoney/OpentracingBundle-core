<?php

namespace Constants;

const EMIT_BATCH_OVER_HEAD = 12314;
const UDP_PACKET_MAX_LENGTH = 84934;

class Foo
{
    public static $maxSpanBytes;

    const MAC_UDP_MAX_SIZE = 9216;

    public function __construct($maxPacketSize = '')
    {
        if ($maxPacketSize == 0) {
            $maxPacketSize = false ? self::MAC_UDP_MAX_SIZE : UDP_PACKET_MAX_LENGTH;
        }

        var_dump($maxPacketSize);
        var_dump(EMIT_BATCH_OVER_HEAD);

        self::$maxSpanBytes = $maxPacketSize - EMIT_BATCH_OVER_HEAD;
    }
}

$f = new Foo();
var_dump(Foo::$maxSpanBytes);

$g = new Foo('bad');
