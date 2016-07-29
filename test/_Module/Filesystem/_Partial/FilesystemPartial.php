<?php

namespace Kraken\_Module\Filesystem\_Partial;

use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiAppendPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiCopyDirPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiCopyFilePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiCreateDirPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiCreateFilePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiEraseDirPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiEraseFilePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiExistsPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetContentsPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetDirectoriesPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetFilesPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetMimetypePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetSizePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetTimestampPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetTypePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiGetVisibilityPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiIsDirPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiIsFilePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiIsPrivatePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiIsPublicPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiMovePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiPrependPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiReadPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiRemoveDirPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiRemoveFilePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiReqPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiSetPrivatePartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiSetPublicPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiSetVisibilityPartial;
use Kraken\_Module\Filesystem\_Partial\Filesystem\FsApiWritePartial;

trait FilesystemPartial
{
    use FsApiExistsPartial;
    use FsApiMovePartial;
    use FsApiIsFilePartial;
    use FsApiIsDirPartial;
    use FsApiGetContentsPartial;
    use FsApiGetFilesPartial;
    use FsApiGetDirectoriesPartial;
    use FsApiGetVisibilityPartial;
    use FsApiIsPublicPartial;
    use FsApiIsPrivatePartial;
    use FsApiSetVisibilityPartial;
    use FsApiSetPublicPartial;
    use FsApiSetPrivatePartial;
    use FsApiWritePartial;
    use FsApiAppendPartial;
    use FsApiPrependPartial;
    use FsApiReadPartial;
    use FsApiReqPartial;
    use FsApiGetSizePartial;
    use FsApiGetTypePartial;
    use FsApiGetMimetypePartial;
    use FsApiGetTimestampPartial;
    use FsApiCreateFilePartial;
    use FsApiCopyFilePartial;
    use FsApiRemoveFilePartial;
    use FsApiEraseFilePartial;
    use FsApiCreateDirPartial;
    use FsApiCopyDirPartial;
    use FsApiRemoveDirPartial;
    use FsApiEraseDirPartial;
}
