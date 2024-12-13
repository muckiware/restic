<?php

namespace MuckiRestic\Core;

enum Commands: string
{
    case INIT = 'Init';
    case BACKUP = 'Backup';
    case RESTORE = 'Restore';
    case FORGET = 'Forget';
    case PRUNE = 'Prune';
    case LIST = 'List';
    case SNAPSHOTS = 'Snapshots';
    case CHECK = 'Check';
    case VERSION = 'Version';
    case HELP = 'Help';
    case TEST_COMMAND = 'TestCommand';
    case UNLOCK = 'Unlock';
}
