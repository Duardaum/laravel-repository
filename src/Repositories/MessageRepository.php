<?php

namespace Duardaum\LaravelRepository\Repositories;

use Duardaum\LaravelRepository\Contracts\Repositories\MessageRepositoryInterface;
use Duardaum\LaravelRepository\Models\Message;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{

    protected string|\Illuminate\Database\Eloquent\Model $_model = Message::class;

}