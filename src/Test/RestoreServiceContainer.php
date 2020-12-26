<?php

namespace Happyr\ServiceMocking\Test;

use Happyr\ServiceMocking\ServiceMock;

/**
 * After each test, make sure we restore the default behavior of all
 * services.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait RestoreServiceContainer
{
    /**
     * @internal
     * @after
     */
    public static function _restoreContainer(): void
    {
        ServiceMock::resetAll();
    }
}
