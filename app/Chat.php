<?php

namespace App;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface {


    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {

        $token = $conn->httpRequest->getUri()->getQuery();

        // find user by token
        $user = User::query()->where(['remember_token'=>$token])->first();

        // check user status
        if (!$user || $user->state === User::STATUS_BANNED){
            $conn->close();
        }

        // save current connection to user property
        $conn->user = $user;

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->sendUserList();

        echo "New connection! {$user->name} ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        /*
         *
         * get text message from client
         *
         * {
         *  "type": "message",
         *  "text": "...."
         * }
         *
         *
         * ban user by id
         *
         * {
         *  "type": "ban",
         *  "id": "10"
         * }
         *
         * unban user by id
         *
         * {
         *  "type": "unban",
         *  "id": "10"
         * }
         *
         */


        $data = json_decode($msg, true);
        echo 'Message: ' . $msg . PHP_EOL;

        if (!$data || empty($data['type'])){
            return;
        }


        switch($data['type']){
            case 'message':
                // save message to db
                // check for 15 sec timeout

                if ($from->user->mute === User::MUTE_ON || empty($data['text'])){
                    return false;
                }

                $this->sendToAll([
                    'type'=>'message',
                    'text' => $data['text'],
                    'user'=> $from->user->name
                ]);

                break;

            case 'ban':

                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                User::query()->where(['id'=> $data['id']])->update(['state'=>User::STATUS_BANNED]);

                $this->sendToAll([
                    'type'=>'ban',
                    'user'=> $from->user->name
                ]);

                break;

            case 'unban':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                User::query()->where(['id'=> $data['id']])->update(['state'=>User::STATUS_ACTIVE]);

                $this->sendToAll([
                    'type'=>'unban',
                    'user'=> $from->user->name
                ]);
                break;

            case 'mute':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                User::query()->where(['id'=> $data['id']])->update(['mute'=>User::MUTE_ON]);

                $this->sendToAll([
                    'type'=>'mute',
                    'user'=> $from->user->name
                ]);

                break;

            case 'unmute':
                if ($from->user->isAdmin !== true || empty($data['id'])){
                    return false;
                }

                User::query()->where(['id'=> $data['id']])->update(['mute'=>User::MUTE_OFF]);

                $this->sendToAll([
                    'type'=>'unmute',
                    'user'=> $from->user->name
                ]);

                break;
        }


    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $this->sendUserList();

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function sendUserList(){

        $list = [];
        foreach ($this->clients as $client) {
            $list[]=$client->user->name;
        }

        $this->sendToAll([
            'type'=>'online',
            'list'=> $list
        ]);
    }

    public function sendToAll($data){
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }
}
