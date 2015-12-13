<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;
use CMDL\ContentTypeDefinition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ContentArchiveReadOnlyConnection extends RecordFilesReadOnlyConnection implements ReadOnlyConnection
{


}