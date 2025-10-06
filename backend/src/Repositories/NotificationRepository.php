<?php
namespace App\Repositories;

class NotificationRepository extends BaseRepository
{
    public function __construct($supabase)
    {
        parent::__construct($supabase, 'notifications');
    }
}
