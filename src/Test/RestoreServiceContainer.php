<?php

namespace Happyr\ServiceMocking\Test;

/**
 * After each tests, make sure we restore the default behavior of all
 * services.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait RestoreServiceContainer
{
    /**
     * @internal
     * @before
     */
    public static function _restoreContainer(): void
    {
        ServiceMock::resetAll();
    }
}
