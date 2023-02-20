<?php

declare(strict_types=1);

namespace App\Helpers;

class StoreMessageHelper
{
    public const STORE_NOT_FOUND = 'Store not found';
    public const STORE_CREATED = 'Store created successfully';
    public const STORE_UPDATED = 'Store updated successfully';
    public const STORE_DELETED = 'Store deleted successfully';
    public const STORE_NOT_CREATED = 'Store not created successfully';
    public const STORE_NOT_UPDATED = 'An error occurred while updating the store';
    public const STORE_NOT_DELETED = 'An error occurred while deleting the store';
}
