<?php namespace SellerCenter\SDK\Feed\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class FeedStatusEnum
 *
 * @package SellerCenter\SDK\Status\Enum
 * @author  Daniel Costa
 * @method static FeedStatusEnum QUEUED()
 * @method static FeedStatusEnum PROCESSING()
 * @method static FeedStatusEnum FINISHED()
 */
class FeedStatusEnum extends Enum
{
    const QUEUED = 'Queued';
    const PROCESSING = 'Processing';
    const FINISHED = 'Finished';
}
