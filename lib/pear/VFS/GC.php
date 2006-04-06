<?php
/**
 * Class for providing garbage collection for any VFS instance.
 *
 * $Horde: framework/VFS/VFS/GC.php,v 1.6 2005/03/14 02:12:13 slusarz Exp $
 *
 * Copyright 2003-2005 Michael Slusarz <slusarz@bigworm.colorado.edu>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Michael Slusarz <slusarz@bigworm.colorado.edu>
 * @version $Revision: 1.6 $
 * @since   Horde 3.0
 * @package VFS
 */
class VFS_GC {

    /**
     * Garbage collect files in the VFS storage system.
     *
     * @access public
     *
     * @param object VFS &$vfs        The VFS object to perform
     *                                garbage collection on.
     * @param string $path            The VFS path to clean.
     * @param optional integer $secs  The minimum amount of time (in seconds)
     *                                required before a file is removed.
     */
    function gc(&$vfs, $path, $secs = 345600)
    {
        /* A 1% chance we will run garbage collection during a call. */
        if (rand(0, 99) == 0) {
            /* Make sure cleaning is done recursively. */
            $files = $vfs->listFolder($path, null, true, false, true);
            if (!is_a($files, 'PEAR_Error') && is_array($files)) {
                $modtime = time() - $secs;
                foreach ($files as $val) {
                    if ($val['date'] < $modtime) {
                        $vfs->deleteFile($path, $val['name']);
                    }
                }
            }
        }
    }

}
